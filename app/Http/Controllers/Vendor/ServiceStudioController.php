<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductGigPackage;
use App\Models\ProductImage;
use App\Models\ServiceOrder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ServiceStudioController extends Controller
{
    /**
     * Get the active vendor for the authenticated user.
     */
    protected function getVendor(Request $request)
    {
        return $request->attributes->get('current_vendor') ?? auth()->user()?->vendor;
    }

    /**
     * List vendor's freelance services.
     */
    public function index(Request $request): View
    {
        $vendor = $this->getVendor($request);
        if (!$vendor) {
            abort(403, 'Vendor store required.');
        }

        $services = Product::where('vendor_id', $vendor->id)
            ->where('vertical', 'services')
            ->with(['images', 'category', 'gigPackages'])
            ->withCount(['serviceOrders'])
            ->latest()
            ->paginate(15);

        return view('vendor.services.index', compact('vendor', 'services'));
    }

    /**
     * Show creation studio for a new service gig.
     */
    public function create(Request $request): View
    {
        $vendor = $this->getVendor($request);
        if (!$vendor) {
            abort(403, 'Vendor store required.');
        }

        $categories = Category::where('vertical', 'services')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('vendor.services.create', compact('vendor', 'categories'));
    }

    /**
     * Store new service with 3-tier packages, FAQs, and add-ons.
     */
    public function store(Request $request): RedirectResponse
    {
        $vendor = $this->getVendor($request);
        if (!$vendor) {
            abort(403, 'Vendor store required.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:150',
            'category_id' => 'required|exists:categories,id',
            'description' => 'required|string|min:20',
            'requirements_prompt' => 'nullable|string|max:1000',

            // Packages (Basic is required)
            'basic_title' => 'required|string|max:80',
            'basic_description' => 'required|string|max:300',
            'basic_price' => 'required|numeric|min:50',
            'basic_delivery_days' => 'required|integer|min:1|max:90',
            'basic_revisions' => 'required|integer|min:0|max:20',
            'basic_features' => 'nullable|string',

            'standard_title' => 'nullable|string|max:80',
            'standard_description' => 'nullable|string|max:300',
            'standard_price' => 'nullable|numeric|min:50',
            'standard_delivery_days' => 'nullable|integer|min:1|max:90',
            'standard_revisions' => 'nullable|integer|min:0|max:20',
            'standard_features' => 'nullable|string',

            'premium_title' => 'nullable|string|max:80',
            'premium_description' => 'nullable|string|max:300',
            'premium_price' => 'nullable|numeric|min:50',
            'premium_delivery_days' => 'nullable|integer|min:1|max:90',
            'premium_revisions' => 'nullable|integer|min:0|max:20',
            'premium_features' => 'nullable|string',

            'images.*' => 'nullable|image|max:5120',
        ]);

        $baseSlug = Str::slug($validated['title']);
        $slug = $baseSlug;
        $counter = 1;
        while (Product::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter++;
        }

        // Store Product
        $product = Product::create([
            'vendor_id' => $vendor->id,
            'category_id' => $validated['category_id'],
            'title' => $validated['title'],
            'slug' => $slug,
            'description' => $validated['description'],
            'vertical' => 'services',
            'listing_type' => 'gig',
            'base_price' => (int) round($validated['basic_price'] * 100),
            'gst_rate' => 18.00,
            'status' => 'active',
            'requirements_prompt' => $validated['requirements_prompt'] ?? 'Please describe your project specifications and attach any reference files.',
        ]);

        // Create Basic Package
        $basicFeatures = array_filter(array_map('trim', explode("\n", $validated['basic_features'] ?? '')));
        ProductGigPackage::create([
            'product_id' => $product->id,
            'tier' => 'basic',
            'title' => $validated['basic_title'],
            'description' => $validated['basic_description'],
            'price' => $validated['basic_price'],
            'delivery_days' => $validated['basic_delivery_days'],
            'revisions' => $validated['basic_revisions'],
            'features_json' => $basicFeatures,
        ]);

        // Standard Package
        if (!empty($validated['standard_title']) && !empty($validated['standard_price'])) {
            $standardFeatures = array_filter(array_map('trim', explode("\n", $validated['standard_features'] ?? '')));
            ProductGigPackage::create([
                'product_id' => $product->id,
                'tier' => 'standard',
                'title' => $validated['standard_title'],
                'description' => $validated['standard_description'] ?? '',
                'price' => $validated['standard_price'],
                'delivery_days' => $validated['standard_delivery_days'] ?? 5,
                'revisions' => $validated['standard_revisions'] ?? 2,
                'features_json' => $standardFeatures,
            ]);
        }

        // Premium Package
        if (!empty($validated['premium_title']) && !empty($validated['premium_price'])) {
            $premiumFeatures = array_filter(array_map('trim', explode("\n", $validated['premium_features'] ?? '')));
            ProductGigPackage::create([
                'product_id' => $product->id,
                'tier' => 'premium',
                'title' => $validated['premium_title'],
                'description' => $validated['premium_description'] ?? '',
                'price' => $validated['premium_price'],
                'delivery_days' => $validated['premium_delivery_days'] ?? 7,
                'revisions' => $validated['premium_revisions'] ?? 5,
                'features_json' => $premiumFeatures,
            ]);
        }

        // Upload Images
        if ($request->hasFile('images')) {
            $sortOrder = 0;
            foreach ($request->file('images') as $img) {
                $path = $img->store('products', 'public');
                ProductImage::create([
                    'product_id' => $product->id,
                    'path' => $path,
                    'alt_text' => $product->title,
                    'sort_order' => $sortOrder++,
                ]);
            }
        }

        return redirect()->route('vendor.services.index')
            ->with('success', 'Your freelance service gig has been created and published successfully!');
    }

    /**
     * Delete service gig.
     */
    public function destroy(Request $request, Product $product): RedirectResponse
    {
        $vendor = $this->getVendor($request);
        if ($product->vendor_id !== $vendor->id) {
            abort(403, 'Unauthorized.');
        }

        $product->delete();

        return back()->with('success', 'Service deleted successfully.');
    }

    /**
     * List orders for this vendor's services.
     */
    public function orders(Request $request): View
    {
        $vendor = $this->getVendor($request);
        if (!$vendor) {
            abort(403, 'Vendor store required.');
        }

        $status = $request->query('status');

        $query = ServiceOrder::where('vendor_id', $vendor->id)
            ->with(['buyer', 'product.images', 'package']);

        if ($status && in_array($status, ['in_progress', 'delivered', 'revision_requested', 'completed', 'cancelled', 'disputed'])) {
            $query->where('status', $status);
        }

        $orders = $query->latest()->paginate(15)->withQueryString();

        return view('vendor.services.orders', compact('vendor', 'orders', 'status'));
    }
}
