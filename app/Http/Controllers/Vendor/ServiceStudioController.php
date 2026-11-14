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
use Illuminate\Validation\Rule;
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
            'category_id' => ['required', Rule::exists('categories', 'id')->where('vertical', 'services')],
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

            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif,avif|max:15360',
        ]);

        $newImgCount = $request->hasFile('images') ? count(array_filter($request->file('images') ?? [], fn($f) => $f && $f->isValid())) : 0;
        $check = app(\App\Services\Monetization\EntitlementService::class)->validateListingWrite($vendor, 'services', true, $newImgCount);
        if (!$check['allowed']) {
            return back()->withInput()->with('error', $check['reason']);
        }

        $baseSlug = Str::slug($validated['title']) ?: 'service-gig';
        $slug = $baseSlug;
        $counter = 1;
        while (Product::withoutGlobalScopes()->where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter++;
        }

        $uploadedPaths = [];
        try {
            \Illuminate\Support\Facades\DB::transaction(function () use ($request, $vendor, $validated, $slug, &$uploadedPaths) {
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
                    'published_at' => now(),
                    'show_on_website' => true,
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
                        if (!$img || !$img->isValid()) {
                            continue;
                        }
                        $path = $img->store('products', 'public');
                        k71_sync_public_storage($path);
                        $uploadedPaths[] = $path;
                        ProductImage::create([
                            'product_id' => $product->id,
                            'path' => $path,
                            'alt_text' => $product->title,
                            'sort_order' => $sortOrder++,
                        ]);
                    }
                }
            });
        } catch (\Throwable $e) {
            foreach ($uploadedPaths as $path) {
                k71_delete_public_storage($path);
            }
            throw $e;
        }

        $vendor->bumpWebsiteCache();

        return redirect()->route('vendor.services.index')
            ->with('success', 'Your freelance service gig has been created and published successfully!');
    }

    protected function authorizeProduct(Request $request, Product $product): \App\Models\Vendor
    {
        $vendor = $this->getVendor($request);
        if (!$vendor || $product->vendor_id !== $vendor->id) {
            abort(403, 'Unauthorized.');
        }

        return $vendor;
    }

    /**
     * Show edit form for a service gig.
     */
    public function edit(Request $request, Product $product): View
    {
        $vendor = $this->authorizeProduct($request, $product);

        $categories = Category::where('vertical', 'services')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        $packages = $product->gigPackages()->get();
        $updateRoute = route('vendor.services.update', $product);
        $backRoute = route('vendor.services.index');

        return view('vendor.services.edit', compact('vendor', 'product', 'categories', 'packages', 'updateRoute', 'backRoute'));
    }

    /**
     * Update a vendor's service gig.
     */
    public function update(Request $request, Product $product): RedirectResponse
    {
        $vendor = $this->authorizeProduct($request, $product);

        $validated = $request->validate([
            'title' => 'required|string|max:150',
            'category_id' => ['required', Rule::exists('categories', 'id')->where('vertical', 'services')],
            'description' => 'required|string|max:5000',
            'base_price' => 'required|numeric|min:10',
            'status' => 'nullable|in:active,paused,draft',
            'show_on_website' => 'nullable|boolean',
            'is_website_featured' => 'nullable|boolean',
            'packages' => 'nullable|array',
            'packages.*.name' => 'nullable|string|max:100',
            'packages.*.title' => 'nullable|string|max:100',
            'packages.*.price' => 'nullable|numeric|min:1',
            'packages.*.delivery_days' => 'nullable|integer|min:1|max:90',
            'packages.*.revisions' => 'nullable',
            'packages.*.description' => 'nullable|string|max:1000',
        ]);

        $nextStatus = $validated['status'] ?? $product->status;
        $check = app(\App\Services\Monetization\EntitlementService::class)->validateListingWrite($vendor, 'services', $nextStatus === 'active', 0, 0, $product->id);
        if (!$check['allowed']) {
            return back()->withInput()->with('error', $check['reason']);
        }

        $product->update([
            'title' => $validated['title'],
            'category_id' => $validated['category_id'],
            'description' => $validated['description'],
            'short_description' => Str::limit($validated['description'], 120),
            'base_price' => (int) round(((float) $validated['base_price']) * 100),
            'status' => $validated['status'] ?? $product->status,
            'show_on_website' => $request->has('show_on_website') ? $request->boolean('show_on_website') : ($product->show_on_website ?? true),
            'is_website_featured' => $request->has('is_website_featured') ? $request->boolean('is_website_featured') : ($product->is_website_featured ?? false),
        ]);

        if (!empty($validated['packages'])) {
            foreach ($validated['packages'] as $tier => $pkg) {
                if (!empty($pkg['price'])) {
                    $rawRev = (string) ($pkg['revisions'] ?? '');
                    $revCount = str_contains(strtolower($rawRev), 'unlim')
                        ? 99
                        : (int) (preg_replace('/\D+/', '', $rawRev) ?: 2);

                    $desc = trim((string) ($pkg['description'] ?? 'Standard tier delivery')) ?: 'Standard tier delivery';
                    $existingPkg = ProductGigPackage::where('product_id', $product->id)
                        ->where('tier', $tier)
                        ->first();

                    ProductGigPackage::updateOrCreate(
                        ['product_id' => $product->id, 'tier' => $tier],
                        [
                            'title' => trim((string) ($pkg['title'] ?? $pkg['name'] ?? '')) ?: ucfirst((string) $tier),
                            'price' => round((float) $pkg['price'], 2),
                            'delivery_days' => max(1, (int) ($pkg['delivery_days'] ?? 3)),
                            'revisions' => max(0, $revCount),
                            'description' => $desc,
                            'features_json' => $existingPkg?->features_json ?? [$desc],
                        ]
                    );
                }
            }
        }

        $vendor->bumpWebsiteCache();

        return redirect()->route('vendor.services.index')
            ->with('success', 'Service updated and synced across marketplace & your website!');
    }

    public function toggleStatus(Request $request, Product $product): RedirectResponse
    {
        $vendor = $this->authorizeProduct($request, $product);
        $nextStatus = $product->status === 'active' ? 'paused' : 'active';
        $check = app(\App\Services\Monetization\EntitlementService::class)->validateListingWrite($vendor, 'services', $nextStatus === 'active', 0, 0, $product->id);
        if (!$check['allowed']) {
            return back()->with('error', $check['reason']);
        }

        $product->update(['status' => $nextStatus]);
        $vendor->bumpWebsiteCache();

        return back()->with('success', $nextStatus === 'active' ? 'Service resumed and live.' : 'Service paused.');
    }

    public function toggleWebsiteVisibility(Request $request, Product $product): RedirectResponse
    {
        $vendor = $this->authorizeProduct($request, $product);
        $check = app(\App\Services\Monetization\EntitlementService::class)->authorizeSectorWrite($vendor, 'services');
        if (!$check['allowed']) {
            return back()->with('error', $check['reason']);
        }

        $product->update([
            'show_on_website' => !($product->show_on_website ?? true),
        ]);
        $vendor->bumpWebsiteCache();

        return back()->with('success', $product->show_on_website ? 'Service shown on your personal website.' : 'Service hidden from personal website.');
    }

    public function duplicate(Request $request, Product $product): RedirectResponse
    {
        $vendor = $this->authorizeProduct($request, $product);
        $check = app(\App\Services\Monetization\EntitlementService::class)->authorizeSectorWrite($vendor, 'services');
        if (!$check['allowed']) {
            return back()->with('error', $check['reason']);
        }

        $copy = \Illuminate\Support\Facades\DB::transaction(function () use ($product) {
            $copy = $product->replicate();
            $copy->title = $product->title . ' (Copy)';
            $baseSlug = Str::slug($copy->title) ?: 'service-copy';
            $slug = $baseSlug;
            $counter = 1;
            while (Product::withoutGlobalScopes()->where('slug', $slug)->exists()) {
                $slug = $baseSlug . '-' . $counter++;
            }
            $copy->slug = $slug;
            $copy->status = 'paused';
            $copy->save();

            foreach ($product->gigPackages as $pkg) {
                $pkgCopy = $pkg->replicate();
                $pkgCopy->product_id = $copy->id;
                $pkgCopy->save();
            }

            foreach ($product->images as $img) {
                $imgCopy = $img->replicate();
                $imgCopy->product_id = $copy->id;
                $imgCopy->save();
            }

            return $copy;
        });

        $vendor->bumpWebsiteCache();

        return redirect()->route('vendor.services.edit', $copy)->with('success', 'Service duplicated as paused draft. Customize and publish when ready.');
    }

    /**
     * Delete service gig.
     */
    public function destroy(Request $request, Product $product): RedirectResponse
    {
        $vendor = $this->authorizeProduct($request, $product);

        $hasActiveOrders = ServiceOrder::where('product_id', $product->id)
            ->whereIn('status', ['in_progress', 'delivered', 'revision_requested', 'disputed'])
            ->exists();
        if ($hasActiveOrders) {
            return back()->with('error', 'Cannot delete a service with active client orders. Pause the service instead.');
        }

        $product->delete();
        $vendor->bumpWebsiteCache();
        \App\Http\Controllers\SitemapController::clearCache();

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
