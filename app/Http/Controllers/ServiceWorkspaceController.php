<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\ServiceOrder;
use App\Models\ServiceOrderMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ServiceWorkspaceController extends Controller
{
    /**
     * Authorize user to access this project workspace.
     */
    protected function authorizeWorkspaceUser(ServiceOrder $order): bool
    {
        $userId = auth()->id();
        $isBuyer = $order->buyer_id === $userId;
        $isVendor = $order->vendor && $order->vendor->user_id === $userId;
        $isAdmin = auth()->user()?->isAdmin() ?? false;

        return $isBuyer || $isVendor || $isAdmin;
    }

    /**
     * Display the Upwork/Fiverr style collaborative project workspace.
     */
    public function show(ServiceOrder $order): View
    {
        if (!$this->authorizeWorkspaceUser($order)) {
            abort(403, 'Unauthorized access to this project workspace.');
        }

        $order->load(['buyer', 'vendor.settings', 'product.images', 'messages.sender', 'package']);

        $isBuyer = $order->buyer_id === auth()->id();
        $isVendor = $order->vendor && $order->vendor->user_id === auth()->id();

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

        $validated = $request->validate([
            'message' => 'nullable|string|max:3000',
            'attachments.*' => 'nullable|file|max:10240|mimes:pdf,zip,png,jpg,jpeg,doc,docx,txt,fig,psd,ai',
        ]);

        if (empty($validated['message']) && !$request->hasFile('attachments')) {
            return back()->with('error', 'Please enter a message or select a file to send.');
        }

        $uploadedAttachments = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('workspace_files/' . $order->order_number, 'public');
                $uploadedAttachments[] = [
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'size' => $file->getSize(),
                ];
            }
        }

        ServiceOrderMessage::create([
            'service_order_id' => $order->id,
            'user_id' => auth()->id(),
            'sender_type' => $order->buyer_id === auth()->id() ? 'buyer' : 'seller',
            'message' => $validated['message'] ?? 'Uploaded attachments',
            'attachments' => $uploadedAttachments,
        ]);

        return back()->with('success', 'Message posted.');
    }

    /**
     * Freelancer delivers the final work / files.
     */
    public function submitDelivery(Request $request, ServiceOrder $order): RedirectResponse
    {
        $userId = auth()->id();
        if ($order->vendor->user_id !== $userId && !auth()->user()->isAdmin()) {
            abort(403, 'Only the assigned freelancer can submit project deliverables.');
        }

        $validated = $request->validate([
            'notes' => 'required|string|max:2000',
            'delivery_files.*' => 'required|file|max:20480',
        ]);

        $uploadedFiles = [];
        if ($request->hasFile('delivery_files')) {
            foreach ($request->file('delivery_files') as $file) {
                $path = $file->store('deliverables/' . $order->order_number, 'public');
                $uploadedFiles[] = [
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'size' => $file->getSize(),
                ];
            }
        }

        $currentDeliveries = is_array($order->deliveries) ? $order->deliveries : [];
        $currentDeliveries[] = [
            'notes' => $validated['notes'],
            'files' => $uploadedFiles,
            'submitted_at' => now()->toDateTimeString(),
        ];

        $order->update([
            'deliveries' => $currentDeliveries,
            'status' => 'delivered',
            'delivered_at' => now(),
        ]);

        // Post delivery announcement in conversation
        ServiceOrderMessage::create([
            'service_order_id' => $order->id,
            'user_id' => $userId,
            'sender_type' => 'seller',
            'message' => "🎉 **Work Delivered!**\n\n" . $validated['notes'] . "\n\nPlease review the deliverables above. You can request revisions or approve to finalize the project.",
            'attachments' => $uploadedFiles,
        ]);

        return back()->with('success', 'Work delivered successfully! The buyer has been notified for review.');
    }

    /**
     * Buyer requests a revision on delivered work.
     */
    public function requestRevision(Request $request, ServiceOrder $order): RedirectResponse
    {
        $userId = auth()->id();
        if ($order->buyer_id !== $userId && !auth()->user()->isAdmin()) {
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

        $order->increment('revisions_used');
        $order->update([
            'status' => 'revision_requested',
        ]);

        ServiceOrderMessage::create([
            'service_order_id' => $order->id,
            'user_id' => $userId,
            'sender_type' => 'buyer',
            'message' => "🔄 **Revision Requested (Revision #{$order->revisions_used}/{$order->revisions_allowed})**\n\n" . $validated['revision_notes'],
            'attachments' => [],
        ]);

        return back()->with('success', 'Revision request sent to the provider.');
    }

    /**
     * Buyer approves deliverables and marks the project complete.
     */
    public function approveOrder(Request $request, ServiceOrder $order): RedirectResponse
    {
        $userId = auth()->id();
        if ($order->buyer_id !== $userId && !auth()->user()->isAdmin()) {
            abort(403, 'Only the buyer can approve and finalize this project.');
        }

        $order->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        // Increment provider completed projects counter
        $order->vendor?->increment('completed_projects');

        // Optional Rating and Review
        if ($request->filled('rating')) {
            $rating = (int) $request->rating;
            $order->update([
                'buyer_rating' => $rating,
                'buyer_review' => $request->review_text,
            ]);

            Review::create([
                'user_id' => $userId,
                'product_id' => $order->product_id,
                'rating' => $rating,
                'comment' => $request->review_text ?? 'Great service and communication!',
                'status' => 'approved',
                'is_verified_purchase' => true,
            ]);

            // Recompute vendor rating
            $avg = ServiceOrder::where('vendor_id', $order->vendor_id)
                ->whereNotNull('buyer_rating')
                ->avg('buyer_rating');
            if ($avg) {
                $order->vendor->update(['rating' => round($avg, 1)]);
            }
        }

        ServiceOrderMessage::create([
            'service_order_id' => $order->id,
            'user_id' => $userId,
            'sender_type' => 'buyer',
            'message' => "✅ **Project Approved & Completed!**\nThank you for great collaboration.",
            'attachments' => [],
        ]);

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

        $validated = $request->validate([
            'dispute_reason' => 'required|string|min:15|max:1500',
        ]);

        $order->update([
            'status' => 'disputed',
            'dispute_reason' => $validated['dispute_reason'],
        ]);

        ServiceOrderMessage::create([
            'service_order_id' => $order->id,
            'user_id' => auth()->id(),
            'sender_type' => $order->buyer_id === auth()->id() ? 'buyer' : 'seller',
            'message' => "⚠️ **Dispute Raised:**\n\n" . $validated['dispute_reason'] . "\n\nK71 Marketplace Support has been notified for mediation.",
            'attachments' => [],
        ]);

        return back()->with('info', 'Dispute ticket registered. A K71 mediation specialist will review the conversation and deliverables.');
    }
}
