<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Vendor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ServicesController extends Controller
{
    /**
     * Display the K71 Freelance & Professional Services Marketplace Hub.
     */
    public function index(Request $request): View
    {
        $search = $request->query('search');
        $selectedCategory = $request->query('cat');
        $city = $request->query('city');
        $minPrice = $request->query('min_price');
        $maxPrice = $request->query('max_price');
        $deliveryDays = $request->query('delivery');
        $workMode = $request->query('work_mode');
        $sort = $request->query('sort', 'newest');

        // Fetch active service categories with real service count
        $servicesCategories = Category::where('vertical', 'services')
            ->where('is_active', true)
            ->withCount(['products' => function ($q) {
                $q->where('vertical', 'services')->where('status', 'active');
            }])
            ->orderBy('sort_order')
            ->get();

        // Main Services Query
        $query = Product::with(['images', 'category', 'vendor.settings', 'gigPackages'])
            ->where('vertical', 'services')
            ->where('status', 'active');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('brand', 'like', "%{$search}%")
                  ->orWhereHas('vendor', function ($vq) use ($search) {
                      $vq->where('shop_name', 'like', "%{$search}%")
                         ->orWhere('tagline', 'like', "%{$search}%")
                         ->orWhere('city', 'like', "%{$search}%");
                  });
            });
        }

        if ($selectedCategory) {
            $query->whereHas('category', fn($q) => $q->where('slug', $selectedCategory));
        }

        if ($city) {
            $query->whereHas('vendor', fn($q) => $q->where('city', $city));
        }

        if ($minPrice !== null && is_numeric($minPrice)) {
            $query->where('base_price', '>=', (int) ($minPrice * 100));
        }

        if ($maxPrice !== null && is_numeric($maxPrice)) {
            $query->where('base_price', '<=', (int) ($maxPrice * 100));
        }

        if ($deliveryDays && is_numeric($deliveryDays)) {
            $query->whereHas('gigPackages', function ($gq) use ($deliveryDays) {
                $gq->where('delivery_days', '<=', (int) $deliveryDays);
            });
        }

        if ($workMode && in_array($workMode, ['remote', 'onsite', 'hybrid'], true)) {
            $query->whereHas('vendor', fn ($vq) => $vq->where('work_mode', $workMode));
        }

        // Sorting
        switch ($sort) {
            case 'price_asc':
                $query->orderBy('base_price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('base_price', 'desc');
                break;
            case 'rating':
                $query->orderByDesc('sales_count');
                break;
            case 'newest':
            default:
                $query->latest();
                break;
        }

        $services = $query->paginate(12)->withQueryString();

        // Featured Packages (Gigs with 3-tier packages)
        $featuredPackages = Product::with(['images', 'category', 'vendor', 'gigPackages'])
            ->where('vertical', 'services')
            ->where('status', 'active')
            ->has('gigPackages')
            ->take(6)
            ->get();

        // Recommended Professionals / Freelancers
        $topProviders = Vendor::where('status', 'approved')
            ->where(function ($q) {
                $q->whereIn('vendor_type', ['service', 'services', 'local_services', 'digital_services'])
                  ->orWhereHas('products', fn($pq) => $pq->where('vertical', 'services')->where('status', 'active'));
            })
            ->withCount(['products' => fn($q) => $q->where('vertical', 'services')->where('status', 'active')])
            ->orderByDesc('rating')
            ->take(8)
            ->get();

        // Budget Friendly (< ₹1,500)
        $budgetFriendly = Product::with(['images', 'vendor', 'category'])
            ->where('vertical', 'services')
            ->where('status', 'active')
            ->where('base_price', '<=', 150000)
            ->take(4)
            ->get();

        // Premium Pro Services (> ₹5,000)
        $premiumServices = Product::with(['images', 'vendor', 'category', 'gigPackages'])
            ->where('vertical', 'services')
            ->where('status', 'active')
            ->where('base_price', '>=', 500000)
            ->take(4)
            ->get();

        $servicesSettings = \App\Models\Setting::getGroup('services_page');

        return view('services.index', compact(
            'servicesCategories',
            'services',
            'featuredPackages',
            'topProviders',
            'budgetFriendly',
            'premiumServices',
            'search',
            'selectedCategory',
            'city',
            'minPrice',
            'maxPrice',
            'deliveryDays',
            'workMode',
            'sort',
            'servicesSettings'
        ));
    }

    /**
     * Category filtered view.
     */
    public function category(string $slug, Request $request): View
    {
        $category = Category::where('slug', $slug)
            ->where('vertical', 'services')
            ->firstOrFail();

        $request->merge(['cat' => $slug]);

        return $this->index($request);
    }

    /**
     * Display a single Freelance Service / Gig page (Fiverr/Upwork style).
     */
    public function show(string $slug): View
    {
        $service = Product::where('slug', $slug)
            ->where('vertical', 'services')
            ->with(['images', 'category', 'vendor.settings', 'gigPackages', 'reviews.user'])
            ->firstOrFail();

        // Other services by same provider
        $providerServices = Product::with(['images', 'category'])
            ->where('vendor_id', $service->vendor_id)
            ->where('vertical', 'services')
            ->where('id', '!=', $service->id)
            ->where('status', 'active')
            ->take(4)
            ->get();

        // Similar services in same category
        $similarServices = Product::with(['images', 'vendor', 'category'])
            ->where('category_id', $service->category_id)
            ->where('id', '!=', $service->id)
            ->where('vertical', 'services')
            ->where('status', 'active')
            ->take(4)
            ->get();

        return view('services.show', compact('service', 'providerServices', 'similarServices'));
    }

    /**
     * Display a dedicated Freelancer / Service Provider profile page.
     */
    public function provider(string $slug): View
    {
        $vendor = Vendor::where('slug', $slug)
            ->with(['settings', 'storefrontConfig'])
            ->firstOrFail();

        $services = Product::with(['images', 'category', 'gigPackages'])
            ->where('vendor_id', $vendor->id)
            ->where('vertical', 'services')
            ->where('status', 'active')
            ->latest()
            ->get();

        $portfolioProjects = \App\Models\PortfolioProject::where('vendor_id', $vendor->id)
            ->where('status', 'published')
            ->orderByDesc('is_featured')
            ->latest('id')
            ->get();

        $reviews = \App\Models\Review::with(['user', 'product'])
            ->where('is_approved', true)
            ->where(function ($q) use ($vendor, $services) {
                $q->where('vendor_id', $vendor->id);
                if ($services->isNotEmpty()) {
                    $q->orWhereIn('product_id', $services->pluck('id'));
                }
            })
            ->latest('id')
            ->take(12)
            ->get();

        return view('services.provider', compact('vendor', 'services', 'portfolioProjects', 'reviews'));
    }

    /**
     * Become a Seller guided 5-step website onboarding page.
     */
    public function becomeSeller(Request $request): View
    {
        return app(\App\Http\Controllers\ServiceOnboardingController::class)->show($request);
    }

    /**
     * Store Become a Seller application (supports both 5-step wizard and backward-compatible flat POST).
     */
    public function storeSeller(Request $request): RedirectResponse
    {
        if ($request->filled('step')) {
            return app(\App\Http\Controllers\ServiceOnboardingController::class)->saveStep($request);
        }

        if (!auth()->check()) {
            return redirect()->guest(route('login'))->with('error', 'Please log in or register before onboarding as a service provider.');
        }

        $user = auth()->user();

        $validated = $request->validate([
            'shop_name' => 'required|string|max:100',
            'tagline' => 'required|string|max:150',
            'description' => 'required|string|max:2000',
            'skills' => 'required|string|max:300',
            'languages' => 'nullable|string|max:200',
            'hourly_rate' => 'nullable|numeric|min:0',
            'city' => 'nullable|string|max:100',
            'contact_phone' => 'nullable|string|max:20',
        ]);

        $skillsArray = array_values(array_filter(array_map('trim', explode(',', $validated['skills']))));
        $languagesArray = !empty($validated['languages']) 
            ? array_values(array_filter(array_map('trim', explode(',', $validated['languages']))))
            : ['English', 'Hindi'];

        $vendor = $user->vendor;
        $cleanPhone = preg_replace('/\D+/', '', (string) ($validated['contact_phone'] ?? $user->phone ?? '')) ?: null;
        if ($cleanPhone && strlen($cleanPhone) > 15) {
            $cleanPhone = substr($cleanPhone, -10);
        }

        if (!$vendor) {
            $trashedVendor = Vendor::onlyTrashed()->where('user_id', $user->id)->first();
            if ($trashedVendor) {
                $trashedVendor->restore();
                $trashedVendor->update([
                    'shop_name' => $validated['shop_name'],
                    'vendor_type' => 'service',
                    'description' => $validated['description'],
                    'tagline' => $validated['tagline'],
                    'skills_json' => $skillsArray,
                    'languages_json' => $languagesArray,
                    'hourly_rate' => $validated['hourly_rate'] ?? null,
                    'city' => $validated['city'] ?? 'India',
                    'contact_phone' => $cleanPhone ?: $trashedVendor->contact_phone,
                    'email' => $user->email,
                    'status' => 'approved',
                    'approved_at' => $trashedVendor->approved_at ?: now(),
                    'vacation_mode' => false,
                ]);
                $vendor = $trashedVendor;
            } else {
                $reservedSlugs = array_unique(array_merge(
                    config('marketplace.reserved_subdomains', []) ?? [],
                    config('marketplace.reserved_slugs', []) ?? []
                ));
                $baseSlug = Str::limit(Str::slug($validated['shop_name']) ?: 'service-pro', 22, '');
                $baseSlug = trim($baseSlug, '-') ?: 'service-pro';
                $slug = $baseSlug;
                $counter = 1;
                while (in_array($slug, $reservedSlugs, true) || Vendor::withTrashed()->where('slug', $slug)->exists()) {
                    $suffix = '-' . $counter++;
                    $slug = Str::limit($baseSlug, max(1, 30 - strlen($suffix)), '') . $suffix;
                }

                $vendor = Vendor::create([
                    'user_id' => $user->id,
                    'shop_name' => $validated['shop_name'],
                    'slug' => $slug,
                    'vendor_type' => 'service',
                    'description' => $validated['description'],
                    'tagline' => $validated['tagline'],
                    'skills_json' => $skillsArray,
                    'languages_json' => $languagesArray,
                    'hourly_rate' => $validated['hourly_rate'] ?? null,
                    'city' => $validated['city'] ?? 'India',
                    'contact_phone' => $cleanPhone,
                    'email' => $user->email,
                    'status' => 'approved', // Instant onboarding for marketplace agility
                    'approved_at' => now(),
                    'rating' => 5.0,
                    'completed_projects' => 0,
                ]);
            }
        } else {
            $vendor->update([
                'shop_name' => $validated['shop_name'],
                'tagline' => $validated['tagline'],
                'description' => $validated['description'],
                'skills_json' => $skillsArray,
                'languages_json' => $languagesArray,
                'hourly_rate' => $validated['hourly_rate'] ?? $vendor->hourly_rate,
                'city' => $validated['city'] ?? $vendor->city,
                'contact_phone' => $cleanPhone ?? $vendor->contact_phone,
                'vendor_type' => 'service',
            ]);
        }

        // Ensure vendor settings & roles are consistently initialized
        \App\Models\VendorSetting::firstOrCreate(
            ['vendor_id' => $vendor->id],
            [
                'shipping_type' => 'flat',
                'shipping_flat_rate' => 0,
                'processing_days' => 1,
                'return_window_days' => 0,
                'upi_enabled' => true,
                'cod_enabled' => false,
                'service_radius_km' => 25,
                'service_experience_years' => 1,
                'service_working_hours' => '9:00 AM - 7:00 PM',
            ]
        );

        $vendorRole = \App\Models\Role::firstOrCreate(['name' => 'vendor'], ['guard_name' => 'web']);
        $customerRole = \App\Models\Role::firstOrCreate(['name' => 'customer'], ['guard_name' => 'web']);
        $user->roles()->syncWithoutDetaching([$customerRole->id, $vendorRole->id]);
        $user->unsetRelation('roles');
        $user->unsetRelation('vendor');

        return redirect()->route('account.listings', ['tab' => 'services'])
            ->with('success', 'Your Freelancer Service Profile is active! Manage your gigs below.');
    }

    /**
     * Edit Freelance Service Gig Form
     */
    public function editService(Product $product)
    {
        $user = auth()->user();
        if (!$user) abort(401);

        if ($product->vendor?->user_id !== $user->id && ($product->custom_fields['user_id'] ?? null) !== $user->id && !$user->hasRole('admin')) {
            abort(403, 'Unauthorized to edit this service.');
        }

        $categories = Category::where('vertical', 'services')->where('is_active', true)->get();
        $packages = $product->gigPackages()->get();

        return view('services.edit', compact('product', 'categories', 'packages'));
    }

    /**
     * Update Freelance Service Gig
     */
    public function updateService(Request $request, Product $product)
    {
        $user = auth()->user();
        if (!$user) abort(401);

        if ($product->vendor?->user_id !== $user->id && ($product->custom_fields['user_id'] ?? null) !== $user->id && !$user->hasRole('admin')) {
            abort(403, 'Unauthorized to update this service.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:150',
            'category_id' => 'required|exists:categories,id',
            'description' => 'required|string|max:5000',
            'base_price' => 'required|numeric|min:10',
            'packages' => 'nullable|array',
            'packages.*.name' => 'nullable|string|max:100',
            'packages.*.title' => 'nullable|string|max:100',
            'packages.*.price' => 'nullable|numeric|min:1',
            'packages.*.delivery_days' => 'nullable|integer|min:1|max:90',
            'packages.*.revisions' => 'nullable',
            'packages.*.description' => 'nullable|string|max:1000',
        ]);

        $product->update([
            'title' => $validated['title'],
            'category_id' => $validated['category_id'],
            'description' => $validated['description'],
            'short_description' => Str::limit($validated['description'], 120),
            'base_price' => (int) round(((float) $validated['base_price']) * 100),
        ]);

        if (!empty($validated['packages'])) {
            foreach ($validated['packages'] as $tier => $pkg) {
                if (!empty($pkg['price'])) {
                    $rawRev = (string) ($pkg['revisions'] ?? '');
                    $revCount = str_contains(strtolower($rawRev), 'unlim')
                        ? 99
                        : (int) (preg_replace('/\D+/', '', $rawRev) ?: 2);

                    $desc = trim((string) ($pkg['description'] ?? 'Standard tier delivery')) ?: 'Standard tier delivery';
                    $existingPkg = \App\Models\ProductGigPackage::where('product_id', $product->id)
                        ->where('tier', $tier)
                        ->first();

                    \App\Models\ProductGigPackage::updateOrCreate(
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

        $product->vendor?->bumpWebsiteCache();

        return redirect()->route('services.show', $product->slug)->with('success', 'Service Gig updated successfully!');
    }

    /**
     * Delete Service Gig
     */
    public function deleteService(Product $product)
    {
        $user = auth()->user();
        if (!$user) abort(401);

        if ($product->vendor?->user_id !== $user->id && ($product->custom_fields['user_id'] ?? null) !== $user->id && !$user->hasRole('admin')) {
            abort(403, 'Unauthorized to delete this service.');
        }

        $vendor = $product->vendor;
        $product->delete();
        $vendor?->bumpWebsiteCache();

        return redirect()->route('account.listings', ['tab' => 'services'])->with('success', 'Service gig removed.');
    }
}
