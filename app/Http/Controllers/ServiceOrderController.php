<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductGigPackage;
use App\Models\ServiceOrder;
use App\Models\ServiceOrderMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ServiceOrderController extends Controller
{
    /**
     * Display service package checkout & project requirements form.
     */
    public function checkout(Product $product, Request $request): View
    {
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
            return redirect()->route('login')->with('info', 'Please sign in or create an account to start this freelance project.');
        }

        $validated = $request->validate([
            'tier' => 'required|in:basic,standard,premium',
            'requirements' => 'required|string|min:10|max:3000',
            'reference_files.*' => 'nullable|file|max:10240|mimes:pdf,zip,png,jpg,jpeg,doc,docx,txt,fig,psd,ai',
            'selected_addons' => 'nullable|array',
        ]);

        $package = $product->gigPackages->firstWhere('tier', $validated['tier']);
        $basePrice = $package ? (float) $package->price : ($product->base_price / 100);
        $deliveryDays = $package ? (int) $package->delivery_days : 3;
        $revisionsAllowed = $package ? (int) $package->revisions : 1;

        // Process add-ons if selected
        $addonsTotal = 0;
        $selectedAddonsList = [];
        if (!empty($validated['selected_addons']) && is_array($product->addons_json)) {
            foreach ($product->addons_json as $addon) {
                if (in_array($addon['name'] ?? '', $validated['selected_addons'])) {
                    $addonsTotal += (float) ($addon['price'] ?? 0);
                    $selectedAddonsList[] = $addon;
                }
            }
        }

        $totalPrice = $basePrice + $addonsTotal;

        // Handle file uploads
        $uploadedFiles = [];
        if ($request->hasFile('reference_files')) {
            foreach ($request->file('reference_files') as $file) {
                $path = $file->store('service_requirements', 'public');
                $uploadedFiles[] = [
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'size' => $file->getSize(),
                ];
            }
        }

        $orderNumber = 'SO-' . strtoupper(Str::random(10));

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

        // Post project kickoff notification message to workspace
        ServiceOrderMessage::create([
            'service_order_id' => $order->id,
            'user_id' => auth()->id(),
            'sender_type' => 'buyer',
            'message' => "Project started! Here are the submitted requirements:\n\n" . $validated['requirements'],
            'attachments' => $uploadedFiles,
        ]);

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

        if ($status && in_array($status, ['in_progress', 'delivered', 'revision_requested', 'completed', 'cancelled', 'disputed'])) {
            $query->where('status', $status);
        }

        $orders = $query->latest()->paginate(10)->withQueryString();

        return view('services.orders.index', compact('orders', 'status'));
    }
}
