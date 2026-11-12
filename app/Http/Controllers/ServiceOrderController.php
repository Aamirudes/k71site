<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ServiceOrder;
use App\Models\ServiceOrderMessage;
use App\Services\MediaUploadService;
use App\Services\Monetization\VisibilityService;
use App\Services\NotificationCenterService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ServiceOrderController extends Controller
{
    public function __construct(
        protected MediaUploadService $mediaUploads,
        protected VisibilityService $visibility,
        protected NotificationCenterService $notifications
    ) {}

    /**
     * Display service package checkout & project requirements form.
     */
    public function checkout(Product $product, Request $request): View
    {
        if ($product->vertical !== 'services') {
            abort(404);
        }

        $isOwnerOrAdmin = auth()->check() && (auth()->user()->vendor?->id === $product->vendor_id || auth()->user()->hasRole('admin'));
        if (!$isOwnerOrAdmin && !$this->visibility->isProductPubliclyVisible($product)) {
            abort(404);
        }

        $product->load(['images', 'vendor.settings', 'gigPackages', 'category']);

        $tier = $request->query('tier', 'basic');
        $package = $product->gigPackages->firstWhere('tier', $tier)
            ?? $product->gigPackages->first();

        $selectedPrice = $package ? (float) $package->price : ($product->base_price / 100);
        $deliveryDays = $package ? $package->delivery_days : 3;

        return view('services.order.checkout', compact('product', 'package', 'tier', 'selectedPrice', 'deliveryDays'));
    }

    /**
     * Submit and initialize the freelance project order.
     */
    public function placeOrder(Request $request, Product $product): RedirectResponse
    {
        if (!auth()->check()) {
            return redirect()->guest(route('login'))->with('info', 'Please sign in or create an account to start this freelance project.');
        }

        if ($product->vertical !== 'services' || !$this->visibility->isProductPubliclyVisible($product)) {
            return back()->with('error', 'This service is currently unavailable or paused.');
        }

        if ($product->vendor && (int) $product->vendor->user_id === (int) auth()->id()) {
            return back()->with('error', 'You cannot place a service order on your own listing.');
        }

        $validated = $request->validate([
            'tier' => 'required|in:basic,standard,premium',
            'requirements' => 'required|string|min:10|max:3000',
            'reference_files' => 'nullable|array|max:8',
            'reference_files.*' => 'nullable|file|max:10240',
            'selected_addons' => 'nullable|array',
        ]);

        $package = $product->gigPackages->firstWhere('tier', $validated['tier']);
        $basePrice = $package ? (float) $package->price : ($product->base_price / 100);
        $deliveryDays = $package ? (int) $package->delivery_days : 3;
        $revisionsAllowed = $package ? (int) $package->revisions : 1;

        // Process add-ons if selected
        $addonsTotal = 0;
        if (!empty($validated['selected_addons']) && is_array($product->addons_json)) {
            foreach ($product->addons_json as $addon) {
                if (in_array($addon['name'] ?? '', $validated['selected_addons'], true)) {
                    $addonsTotal += max(0, (float) ($addon['price'] ?? 0));
                }
            }
        }

        $totalPrice = $basePrice + $addonsTotal;

        $orderNumber = 'SO-' . strtoupper(Str::random(10));

        // Handle file uploads safely via shared MediaUploadService
        $uploadedFiles = [];
        if ($request->hasFile('reference_files')) {
            foreach ($request->file('reference_files') as $file) {
                $meta = $this->mediaUploads->storeSafeDeliverable(
                    $file,
                    'service_requirements/' . $orderNumber
                );
                if (!empty($meta['path']) && function_exists('k71_sync_public_storage')) {
                    k71_sync_public_storage($meta['path']);
                }
                $uploadedFiles[] = $meta;
            }
        }

        $order = DB::transaction(function () use ($orderNumber, $product, $package, $validated, $totalPrice, $deliveryDays, $revisionsAllowed, $uploadedFiles) {
            $order = ServiceOrder::create([
                'order_number' => $orderNumber,
                'buyer_id' => auth()->id(),
                'vendor_id' => $product->vendor_id,
                'product_id' => $product->id,
                'gig_package_id' => $package?->id,
                'package_tier' => $validated['tier'],
                'package_title' => $package?->title ?? $product->title,
                'amount' => (int) round($totalPrice * 100),
                'delivery_days' => $deliveryDays,
                'due_at' => now()->addDays($deliveryDays),
                'revisions_allowed' => $revisionsAllowed,
                'revisions_used' => 0,
                'status' => 'in_progress',
                'requirements' => $validated['requirements'],
                'requirements_files' => $uploadedFiles,
            ]);

            ServiceOrderMessage::create([
                'service_order_id' => $order->id,
                'user_id' => auth()->id(),
                'sender_type' => 'buyer',
                'message' => "Project started! Here are the submitted requirements:\n\n" . $validated['requirements'],
                'attachments' => $uploadedFiles,
            ]);

            return $order;
        });

        $this->notifications->notifyUser(
            $product->vendor?->user_id,
            '🚀',
            "New Service Order #{$order->order_number}",
            "A buyer started a '{$order->package_tier}' order for {$product->title}.",
            route('services.workspace', $order),
            'service_order'
        );

        return redirect()->route('services.workspace', $order)
            ->with('success', 'Project order placed successfully! You are now in the collaborative project workspace.');
    }

    /**
     * List user's service orders (Customer Dashboard).
     */
    public function myOrders(Request $request): View
    {
        $status = $request->query('status');

        $query = ServiceOrder::with(['vendor', 'product.images', 'package'])
            ->where('buyer_id', auth()->id());

        if ($status && in_array($status, ['in_progress', 'delivered', 'revision_requested', 'completed', 'cancelled', 'disputed'], true)) {
            $query->where('status', $status);
        }

        $orders = $query->latest()->paginate(10)->withQueryString();

        return view('services.orders.index', compact('orders', 'status'));
    }
}
