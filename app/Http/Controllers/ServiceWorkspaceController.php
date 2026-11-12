<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\ServiceOrder;
use App\Models\ServiceOrderMessage;
use App\Services\MediaUploadService;
use App\Services\NotificationCenterService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ServiceWorkspaceController extends Controller
{
    public function __construct(
        protected MediaUploadService $mediaUploads,
        protected NotificationCenterService $notifications
    ) {}

    /**
     * Authorize user to access this project workspace.
     */
    protected function authorizeWorkspaceUser(ServiceOrder $order): bool
    {
        $userId = auth()->id();
        $isBuyer = (int) $order->buyer_id === (int) $userId;
        $isVendor = $order->vendor && (int) $order->vendor->user_id === (int) $userId;
        $isAdmin = auth()->user()?->isAdmin() ?? false;

        return $isBuyer || $isVendor || $isAdmin;
    }

    /**
     * Display the collaborative project workspace.
     */
    public function show(ServiceOrder $order): View
    {
        if (!$this->authorizeWorkspaceUser($order)) {
            abort(403, 'Unauthorized access to this project workspace.');
        }

        $order->load(['buyer', 'vendor.settings', 'product.images', 'messages.sender', 'package']);

        $isBuyer = (int) $order->buyer_id === (int) auth()->id();
        $isVendor = $order->vendor && (int) $order->vendor->user_id === (int) auth()->id();

        return view('services.workspace.show', compact('order', 'isBuyer', 'isVendor'));
    }

    /**
     * Send a message or upload project assets in the workspace chat.
     */
    public function sendMessage(Request $request, ServiceOrder $order): RedirectResponse
    {
        if (!$this->authorizeWorkspaceUser($order)) {
            abort(403, 'Unauthorized action.');
        }

        if (in_array($order->status, ['cancelled'], true)) {
            return back()->with('error', 'This project order has been cancelled and no longer accepts messages.');
        }

        $validated = $request->validate([
            'message' => 'nullable|string|max:3000',
            'attachments' => 'nullable|array|max:8',
            'attachments.*' => 'nullable|file|max:10240',
        ]);

        if (empty(trim((string) ($validated['message'] ?? ''))) && !$request->hasFile('attachments')) {
            return back()->with('error', 'Please enter a message or select a file to send.');
        }

        $uploadedAttachments = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $uploadedAttachments[] = $this->mediaUploads->storeSafeDeliverable(
                    $file,
                    'workspace_files/' . $order->order_number
                );
            }
        }

        $isBuyer = (int) $order->buyer_id === (int) auth()->id();

        ServiceOrderMessage::create([
            'service_order_id' => $order->id,
            'user_id' => auth()->id(),
            'sender_type' => $isBuyer ? 'buyer' : 'seller',
            'message' => trim((string) ($validated['message'] ?? '')) ?: 'Uploaded attachments',
            'attachments' => $uploadedAttachments,
        ]);

        $recipientUserId = $isBuyer ? $order->vendor?->user_id : $order->buyer_id;
        $this->notifications->notifyUser(
            $recipientUserId,
            '💬',
            "New Workspace Message — #{$order->order_number}",
            'You received a new update in your service project workspace.',
            route('services.workspace', $order),
            'service_workspace'
        );

        return back()->with('success', 'Message posted.');
    }

    /**
     * Freelancer delivers the final work / files.
     */
    public function submitDelivery(Request $request, ServiceOrder $order): RedirectResponse
    {
        $userId = auth()->id();
        if ((int) ($order->vendor?->user_id ?? 0) !== (int) $userId && !auth()->user()->isAdmin()) {
            abort(403, 'Only the assigned freelancer can submit project deliverables.');
        }

        if (!in_array($order->status, ['in_progress', 'revision_requested'], true)) {
            return back()->with('error', 'Deliverables can only be submitted while the order is in progress or awaiting a revision.');
        }

        $validated = $request->validate([
            'notes' => 'required|string|max:2000',
            'delivery_files' => 'required|array|min:1|max:10',
            'delivery_files.*' => 'required|file|max:20480',
        ]);

        $uploadedFiles = [];
        if ($request->hasFile('delivery_files')) {
            foreach ($request->file('delivery_files') as $file) {
                $uploadedFiles[] = $this->mediaUploads->storeSafeDeliverable(
                    $file,
                    'deliverables/' . $order->order_number,
                    'delivery_files'
                );
            }
        }

        DB::transaction(function () use ($order, $validated, $uploadedFiles, $userId) {
            $locked = ServiceOrder::whereKey($order->id)->lockForUpdate()->firstOrFail();
            if (!in_array($locked->status, ['in_progress', 'revision_requested'], true)) {
                throw new \RuntimeException('Deliverables can only be submitted on active or revision-requested orders.');
            }

            $currentDeliveries = is_array($locked->deliveries) ? $locked->deliveries : [];
            $currentDeliveries[] = [
                'notes' => $validated['notes'],
                'files' => $uploadedFiles,
                'submitted_at' => now()->toDateTimeString(),
            ];

            $locked->update([
                'deliveries' => $currentDeliveries,
                'status' => 'delivered',
                'delivered_at' => now(),
            ]);

            ServiceOrderMessage::create([
                'service_order_id' => $locked->id,
                'user_id' => $userId,
                'sender_type' => 'seller',
                'message' => "🎉 **Work Delivered!**\n\n" . $validated['notes'] . "\n\nPlease review the deliverables above. You can request revisions or approve to finalize the project.",
                'attachments' => $uploadedFiles,
            ]);
        });

        $this->notifications->notifyUser(
            $order->buyer_id,
            '📦',
            "Work Delivered — #{$order->order_number}",
            'Your service provider submitted deliverables ready for review.',
            route('services.workspace', $order),
            'service_workspace'
        );

        return back()->with('success', 'Work delivered successfully! The buyer has been notified for review.');
    }

    /**
     * Buyer requests a revision on delivered work.
     */
    public function requestRevision(Request $request, ServiceOrder $order): RedirectResponse
    {
        $userId = auth()->id();
        if ((int) $order->buyer_id !== (int) $userId && !auth()->user()->isAdmin()) {
            abort(403, 'Only the buyer can request revisions.');
        }

        if ($order->status !== 'delivered') {
            return back()->with('error', 'Revisions can only be requested after work is delivered.');
        }

        if ($order->revisions_used >= $order->revisions_allowed) {
            return back()->with('error', 'You have reached the maximum allowed revisions (' . $order->revisions_allowed . ') for this package.');
        }

        $validated = $request->validate([
            'revision_notes' => 'required|string|min:10|max:2000',
        ]);

        $revUsed = null;
        DB::transaction(function () use ($order, $validated, $userId, &$revUsed) {
            $locked = ServiceOrder::whereKey($order->id)->lockForUpdate()->firstOrFail();
            if ($locked->status !== 'delivered' || $locked->revisions_used >= $locked->revisions_allowed) {
                throw new \RuntimeException('Revision cannot be requested on the current order state.');
            }

            $revUsed = (int) $locked->revisions_used + 1;
            $locked->update([
                'revisions_used' => $revUsed,
                'status' => 'revision_requested',
            ]);

            ServiceOrderMessage::create([
                'service_order_id' => $locked->id,
                'user_id' => $userId,
                'sender_type' => 'buyer',
                'message' => "🔄 **Revision Requested (Revision #{$revUsed}/{$locked->revisions_allowed})**\n\n" . $validated['revision_notes'],
                'attachments' => [],
            ]);
        });

        $this->notifications->notifyUser(
            $order->vendor?->user_id,
            '🔄',
            "Revision Requested — #{$order->order_number}",
            "Buyer requested revision #{$revUsed} of {$order->revisions_allowed}.",
            route('services.workspace', $order),
            'service_workspace'
        );

        return back()->with('success', 'Revision request sent to the provider.');
    }

    /**
     * Buyer approves deliverables and marks the project complete.
     */
    public function approveOrder(Request $request, ServiceOrder $order): RedirectResponse
    {
        $userId = auth()->id();
        if ((int) $order->buyer_id !== (int) $userId && !auth()->user()->isAdmin()) {
            abort(403, 'Only the buyer can approve and finalize this project.');
        }

        if ($order->status === 'completed') {
            return back()->with('info', 'This project is already approved and completed.');
        }

        if ($order->status !== 'delivered') {
            return back()->with('error', 'Deliverables must be submitted before approving completion.');
        }

        $validated = $request->validate([
            'rating' => 'nullable|integer|min:1|max:5',
            'review_text' => 'nullable|string|max:1500',
        ]);

        DB::transaction(function () use ($order, $validated, $userId) {
            $locked = ServiceOrder::whereKey($order->id)->lockForUpdate()->firstOrFail();
            if ($locked->status !== 'delivered') {
                throw new \RuntimeException('Only delivered service orders can be approved.');
            }

            $updatePayload = [
                'status' => 'completed',
                'completed_at' => now(),
            ];

            if (!empty($validated['rating'])) {
                $rating = (int) $validated['rating'];
                $reviewBody = trim((string) ($validated['review_text'] ?? 'Great service and smooth communication!'));

                $updatePayload['buyer_rating'] = $rating;
                $updatePayload['buyer_review'] = $reviewBody;

                Review::updateOrCreate(
                    [
                        'product_id' => $locked->product_id,
                        'user_id' => $userId,
                    ],
                    [
                        'vendor_id' => $locked->vendor_id,
                        'service_order_id' => $locked->id,
                        'rating' => $rating,
                        'body' => $reviewBody,
                        'is_verified_purchase' => true,
                        'is_approved' => true,
                    ]
                );
            }

            $locked->update($updatePayload);

            ServiceOrderMessage::create([
                'service_order_id' => $locked->id,
                'user_id' => $userId,
                'sender_type' => 'buyer',
                'message' => "✅ **Project Approved & Completed!**\nThank you for great collaboration.",
                'attachments' => [],
            ]);
        });

        $order->vendor?->recomputeServiceMetrics();

        $this->notifications->notifyUser(
            $order->vendor?->user_id,
            '✅',
            "Project Completed — #{$order->order_number}",
            'Buyer approved your delivery and finalized the service order.',
            route('services.workspace', $order),
            'service_workspace'
        );

        return back()->with('success', 'Project completed and approved! Funds released to provider.');
    }

    /**
     * Raise a dispute on an active project.
     */
    public function raiseDispute(Request $request, ServiceOrder $order): RedirectResponse
    {
        if (!$this->authorizeWorkspaceUser($order)) {
            abort(403, 'Unauthorized.');
        }

        if (in_array($order->status, ['completed', 'cancelled', 'disputed'], true)) {
            return back()->with('error', 'Disputes cannot be opened on completed, cancelled, or already disputed orders.');
        }

        $validated = $request->validate([
            'dispute_reason' => 'required|string|min:15|max:1500',
        ]);

        DB::transaction(function () use ($order, $validated) {
            $locked = ServiceOrder::whereKey($order->id)->lockForUpdate()->firstOrFail();
            if (in_array($locked->status, ['completed', 'cancelled', 'disputed'], true)) {
                throw new \RuntimeException('Dispute already recorded or order finalized.');
            }

            $locked->update([
                'status' => 'disputed',
                'dispute_reason' => $validated['dispute_reason'],
            ]);

            ServiceOrderMessage::create([
                'service_order_id' => $locked->id,
                'user_id' => auth()->id(),
                'sender_type' => (int) $locked->buyer_id === (int) auth()->id() ? 'buyer' : 'seller',
                'message' => "⚠️ **Dispute Raised:**\n\n" . $validated['dispute_reason'] . "\n\nK71 Marketplace Support has been notified for mediation.",
                'attachments' => [],
            ]);
        });

        $counterpartId = (int) $order->buyer_id === (int) auth()->id() ? $order->vendor?->user_id : $order->buyer_id;
        $this->notifications->notifyUser(
            $counterpartId,
            '⚠️',
            "Dispute Opened — #{$order->order_number}",
            'A workspace dispute was logged and flagged for K71 support review.',
            route('services.workspace', $order),
            'service_workspace'
        );

        return back()->with('info', 'Dispute ticket registered. A K71 mediation specialist will review the conversation and deliverables.');
    }
}
