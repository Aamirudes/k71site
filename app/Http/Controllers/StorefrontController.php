<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use App\Models\Product;
use Illuminate\Http\Request;

class StorefrontController extends Controller
{
    /**
     * Display a directory of all verified vendor storefronts.
     */
    public function index(Request $request)
    {
        $searchQuery = trim($request->input('q', $request->input('search', '')));
        $selectedType = $request->input('type', $request->input('category', 'all'));
        $selectedCity = $request->input('city', 'all');
        $activeSort = $request->input('sort', 'popular');

        $visibility = app(\App\Services\Monetization\VisibilityService::class);
        $query = Vendor::query();
        $visibility->scopeVisibleVendors($query);
        $query->withCount(['products' => function($q) {
                $q->where('status', 'active');
            }])
            ->with(['products' => function($q) {
                $q->where('status', 'active')
                    ->with('images')
                    ->latest()
                    ->take(4);
            }]);

        // Search Filter
        if (!empty($searchQuery)) {
            $query->where(function($q) use ($searchQuery) {
                $q->where('shop_name', 'like', "%{$searchQuery}%")
                  ->orWhere('description', 'like', "%{$searchQuery}%")
                  ->orWhere('city', 'like', "%{$searchQuery}%")
                  ->orWhere('state', 'like', "%{$searchQuery}%");
            });
        }

        // Category / Sector Filter
        if ($selectedType && $selectedType !== 'all') {
            if ($selectedType === 'fashion') {
                $query->whereIn('vendor_type', ['clothing', 'footwear', 'apparel', 'fashion']);
            } elseif ($selectedType === 'services') {
                $query->whereIn('vendor_type', ['services', 'digital_services', 'local_services']);
            } elseif ($selectedType === 'b2b') {
                $query->whereIn('vendor_type', ['b2b', 'machinery']);
            } else {
                $query->where('vendor_type', $selectedType);
            }
        }

        // City Filter
        if ($selectedCity && $selectedCity !== 'all') {
            $query->where('city', $selectedCity);
        }

        // Sorting
        if ($activeSort === 'newest') {
            $query->latest('id');
        } elseif ($activeSort === 'alpha') {
            $query->orderBy('shop_name', 'asc');
        } else {
            // Default: Most products first, then highest ID
            $query->orderByDesc('products_count')->latest('id');
        }

        $vendors = $query->paginate(12)->withQueryString();

        // Dynamic categories & verticals list
        $typesList = [
            'all' => ['label' => 'All Stores', 'icon' => '🏪'],
            'food' => ['label' => 'Food & Dining', 'icon' => '🍽️'],
            'fashion' => ['label' => 'Fashion & Footwear', 'icon' => '👗'],
            'electronics' => ['label' => 'Electronics & Tech', 'icon' => '📱'],
            'automotive' => ['label' => 'Auto Spares & Parts', 'icon' => '🚗'],
            'b2b' => ['label' => 'Machinery & B2B', 'icon' => '⚙️'],
            'services' => ['label' => 'Services & Digital', 'icon' => '🛠️'],
            'used' => ['label' => 'Pre-Loved & Vintage', 'icon' => '⏳'],
            'real_estate' => ['label' => 'Real Estate & Properties', 'icon' => '🏡'],
            'marketplace' => ['label' => 'General & Lifestyle', 'icon' => '✨'],
        ];

        $cities = Vendor::where('status', 'approved')
            ->whereNotNull('city')
            ->where('city', '!=', '')
            ->distinct()
            ->orderBy('city')
            ->pluck('city');

        $totalStoresCount = $visibility->scopeVisibleVendors(Vendor::query())->count();

        return view('stores.index', compact(
            'vendors',
            'typesList',
            'cities',
            'searchQuery',
            'selectedType',
            'selectedCity',
            'activeSort',
            'totalStoresCount'
        ));
    }

    public function show(Request $request, $slug)
    {
        $cleanSlug = strtolower(trim($slug));

        // Seamless dispatch if accessed via /s/{vertical} proxy routing
        if ($cleanSlug === 'food') {
            return app(\App\Http\Controllers\FoodController::class)->index($request);
        }
        if ($cleanSlug === 'services') {
            return app(\App\Http\Controllers\ServicesController::class)->index($request);
        }
        if ($cleanSlug === 'used') {
            return app(\App\Http\Controllers\UsedController::class)->index($request);
        }
        if ($cleanSlug === 'enquiry') {
            return app(\App\Http\Controllers\EnquiryController::class)->index($request);
        }
        if ($cleanSlug === 'shorts') {
            return app(\App\Http\Controllers\ShortsController::class)->index($request);
        }
        if (in_array($cleanSlug, ['vendor', 'vendors', 'seller', 'sellers'])) {
            return redirect()->route('vendor.register');
        }
        if ($cleanSlug === 'admin') {
            return redirect()->route('admin.login');
        }

        $subdomainManager = app(\App\Services\Services\SubdomainManagerService::class);
        $vendor = Vendor::with(['settings', 'storefrontConfig'])->where('slug', $cleanSlug)->first();

        if (!$vendor) {
            $redirectVendor = $subdomainManager->resolveRedirectVendor($cleanSlug);
            if ($redirectVendor) {
                $targetUrl = $subdomainManager->buildRedirectTarget($request, $redirectVendor);
                return redirect()->away($targetUrl, 301);
            }
            abort(404);
        }

        // Check if viewing draft preview (permitted if owner is logged in or admin)
        $isPreview = $request->boolean('preview') && auth()->check() && (auth()->user()->vendor?->id === $vendor->id || auth()->user()->hasRole('admin'));
        $isServiceWebsite = $vendor->isServices();

        if ($isServiceWebsite && $vendor->website_status === 'draft' && !in_array($vendor->status, ['suspended', 'closed'], true) && $vendor->verification_status !== 'rejected' && !$isPreview) {
            return response()
                ->view('errors.website-unpublished', ['vendor' => $vendor], 404)
                ->header('X-Robots-Tag', 'noindex, nofollow')
                ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        }

        if (!$isPreview && !in_array($vendor->status, ['approved', 'active'], true)) {
            return response()->view('errors.store-suspended', ['vendor' => $vendor], 403);
        }

        app()->instance('current_vendor', $vendor);

        $visibility = app(\App\Services\Monetization\VisibilityService::class);
        if (!$isPreview && !$visibility->isVendorVisibleInAnySector($vendor)) {
            return response()->view('errors.store-suspended', ['vendor' => $vendor], 403);
        }

        if ($isServiceWebsite && !$vendor->isWebsitePublished() && !$isPreview) {
            return response()
                ->view('errors.website-unpublished', ['vendor' => $vendor], 404)
                ->header('X-Robots-Tag', 'noindex, nofollow')
                ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        }

        // Never 302-redirect /s/{slug}: Cloudflare Origin/Worker routes proxy https://{slug}.domain/
        // to https://domain/s/{slug} on Host=domain, so redirecting /s/{slug} causes an infinite loop.
        // All UI/navigation links and <link rel="canonical"> point directly to $vendor->storefront_url.

        
        $query = Product::with(['images', 'category', 'variants'])
            ->where('vendor_id', $vendor->id)
            ->where('status', 'active');

        if ($vendor->vendor_type !== 'used') {
            $query->excludeUsed();
        }

        // In-store Search
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('material', 'like', "%{$search}%");
            });
        }

        // Category Filter
        if ($request->filled('category')) {
            $catSlug = $request->input('category');
            $cat = \App\Models\Category::where('slug', $catSlug)->first();
            if ($cat) {
                $catIds = \App\Models\Category::where('id', $cat->id)
                    ->orWhere('parent_id', $cat->id)
                    ->pluck('id');
                $query->whereIn('category_id', $catIds);
            } else {
                $query->whereHas('category', function($q) use ($catSlug) {
                    $q->where('slug', $catSlug)
                      ->orWhereHas('parent', fn($pq) => $pq->where('slug', $catSlug));
                });
            }
        }

        // Sort Options
        $sort = $request->input('sort', 'newest');
        if ($sort === 'price_low') {
            $query->orderBy('base_price', 'asc');
        } elseif ($sort === 'price_high') {
            $query->orderBy('base_price', 'desc');
        } else {
            $query->latest();
        }

        $products = $query->paginate(16)->withQueryString();

        // Vendor's unique categories with item count and sample image for Story Bubbles
        $vendorCategories = \App\Models\Category::whereHas('products', function($q) use ($vendor) {
            $q->where('vendor_id', $vendor->id)->where('status', 'active');
            if ($vendor->vendor_type !== 'used') {
                $q->excludeUsed();
            }
        })
        ->withCount(['products' => function($q) use ($vendor) {
            $q->where('vendor_id', $vendor->id)->where('status', 'active');
            if ($vendor->vendor_type !== 'used') {
                $q->excludeUsed();
            }
        }])
        ->get();

        foreach ($vendorCategories as $cat) {
            $sampleQuery = Product::where('vendor_id', $vendor->id)
                ->where('category_id', $cat->id)
                ->where('status', 'active');
            if ($vendor->vendor_type !== 'used') {
                $sampleQuery->excludeUsed();
            }
            $sampleProduct = $sampleQuery->with('images')->latest()->first();
            $cat->sample_image = $sampleProduct?->images->first()?->path ?? null;
        }

        // Featured products for boutique spotlight (up to 6 for horizontal rail)
        $featuredQuery = Product::with(['images', 'variants'])
            ->where('vendor_id', $vendor->id)
            ->where('status', 'active')
            ->where('is_featured', true);
        if ($vendor->vendor_type !== 'used') {
            $featuredQuery->excludeUsed();
        }
        $featuredProducts = $featuredQuery->take(6)->get();

        // If no featured products explicitly flagged, use newest products for Spotlight
        if ($featuredProducts->isEmpty()) {
            $latestQuery = Product::with(['images', 'variants'])
                ->where('vendor_id', $vendor->id)
                ->where('status', 'active');
            if ($vendor->vendor_type !== 'used') {
                $latestQuery->excludeUsed();
            }
            $featuredProducts = $latestQuery->latest()->take(6)->get();
        }

        // Vendor's published shorts for in-store video commerce tab
        $vendorShorts = $vendor->shorts()
            ->where('status', 'published')
            ->orderByDesc('is_boosted')
            ->orderByDesc('id')
            ->take(8)
            ->get();

        // Vendor's genuine customer reviews (all approved authentic product reviews)
        $vendorReviews = \App\Models\Review::whereIn('product_id', function($q) use ($vendor) {
                $q->select('id')->from('products')->where('vendor_id', $vendor->id);
            })
            ->where('is_approved', true)
            ->with(['user', 'product.images'])
            ->latest()
            ->take(24)
            ->get();

        $themeRegistry = app(\App\Services\Storefront\ThemeMarketplaceRegistry::class);
        $themeService = app(\App\Services\Storefront\ThemeMarketplaceService::class);

        $queryTheme = strtolower(trim((string) $request->input('theme', '')));
        if ($queryTheme !== '' && $themeRegistry->isValidThemeId($queryTheme)) {
            $resolvedThemeId = $queryTheme;
        } elseif ($isPreview) {
            $resolvedThemeId = $vendor->storefrontConfig?->draft_theme_id ?: $themeService->resolvePublicThemeId($vendor);
        } else {
            $resolvedThemeId = $themeService->resolvePublicThemeId($vendor);
        }

        if ($isPreview && $themeRegistry->isNewTheme($resolvedThemeId) && !$themeService->isVendorEntitled($vendor, $resolvedThemeId)) {
            $resolvedThemeId = $themeService->resolvePublicThemeId($vendor);
        }

        // Dispatch to the 50-Theme Engine if vendor's active theme is a new marketplace theme (non-service retail stores)
        if (!$isServiceWebsite && $themeRegistry->isNewTheme($resolvedThemeId)) {
            $manifest = $themeRegistry->getManifest($resolvedThemeId);
            $themeConfig = ($isPreview || $queryTheme !== '')
                ? $themeService->getDraftConfigForTheme($vendor, $resolvedThemeId)
                : $themeService->getPublishedConfigForTheme($vendor, $resolvedThemeId);

            $response = response()
                ->view('storefront.themes.home', [
                    'vendor' => $vendor,
                    'manifest' => $manifest,
                    'themeConfig' => $themeConfig,
                    'products' => $products,
                    'vendorCategories' => $vendorCategories,
                    'featuredProducts' => $featuredProducts,
                    'vendorReviews' => $vendorReviews,
                    'canonicalUrl' => $vendor->storefront_url,
                    'isPreview' => $isPreview,
                ])
                ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
                ->header('Pragma', 'no-cache');

            if ($isPreview) {
                $response->header('X-Robots-Tag', 'noindex, nofollow');
            }

            return $response;
        }

        $config = $vendor->getStorefrontConfig($isPreview);
        $vendorScrollSequence = $vendor->hasKineticScrollAccess()
            ? \App\Services\ScrollSequenceService::resolveVendorSequence($vendor, $config)
            : ['frames' => [], 'count' => 0, 'video_url' => null];
        $providerSite = null;

        if ($isServiceWebsite) {
            $providerSite = app(\App\Services\Services\ServiceWebsitePresenter::class)->build($vendor, $isPreview, $config);

            if (!$isPreview) {
                $this->recordWebsiteEvent($request, $vendor, 'website_view');
            }
        }

        $response = response()
            ->view('storefront.show', compact(
                'vendor',
                'products',
                'vendorCategories',
                'featuredProducts',
                'vendorShorts',
                'vendorReviews',
                'config',
                'vendorScrollSequence',
                'isPreview',
                'isServiceWebsite',
                'providerSite'
            ))
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('X-LiteSpeed-Purge', '*')
            ->header('X-LiteSpeed-Cache-Control', 'no-cache');

        if ($isPreview) {
            $response->header('X-Robots-Tag', 'noindex, nofollow');
        }

        return $response;
    }

    /**
     * Store-themed Collection / Category / Search Listing Page.
     */
    public function collection(Request $request, string $slug, ?string $categorySlug = null)
    {
        $cleanSlug = strtolower(trim($slug));
        $vendor = Vendor::with(['settings', 'storefrontConfig'])->where('slug', $cleanSlug)->firstOrFail();

        $isPreview = $request->boolean('preview') && auth()->check() && (auth()->user()->vendor?->id === $vendor->id || auth()->user()->hasRole('admin'));
        if (!$isPreview && !in_array($vendor->status, ['approved', 'active'], true)) {
            return response()->view('errors.store-suspended', ['vendor' => $vendor], 403);
        }

        $visibility = app(\App\Services\Monetization\VisibilityService::class);
        if (!$isPreview && !$visibility->isVendorVisibleInAnySector($vendor)) {
            return response()->view('errors.store-suspended', ['vendor' => $vendor], 403);
        }

        app()->instance('current_vendor', $vendor);

        $themeRegistry = app(\App\Services\Storefront\ThemeMarketplaceRegistry::class);
        $themeService = app(\App\Services\Storefront\ThemeMarketplaceService::class);

        $queryTheme = strtolower(trim((string) $request->input('theme', '')));
        if ($queryTheme !== '' && $themeRegistry->isValidThemeId($queryTheme)) {
            $resolvedThemeId = $queryTheme;
        } elseif ($isPreview && $vendor->storefrontConfig?->draft_theme_id && $themeService->isVendorEntitled($vendor, $vendor->storefrontConfig->draft_theme_id)) {
            $resolvedThemeId = $vendor->storefrontConfig->draft_theme_id;
        } else {
            $resolvedThemeId = $themeService->resolvePublicThemeId($vendor);
        }

        if (!$themeRegistry->isNewTheme($resolvedThemeId)) {
            $params = array_filter([
                'category' => $categorySlug ?: $request->input('category'),
                'search' => $request->input('search'),
                'sort' => $request->input('sort'),
                'preview' => $isPreview ? 1 : null,
            ]);
            return redirect()->to($vendor->storefrontUrl($params, 'catalog'));
        }

        $activeCategorySlug = $categorySlug ?: trim((string) $request->input('category', ''));

        $query = Product::with(['images', 'category', 'variants'])
            ->withCount(['reviews' => fn ($q) => $q->where('is_approved', true)])
            ->where('vendor_id', $vendor->id)
            ->where('status', 'active');

        if ($vendor->vendor_type !== 'used') {
            $query->excludeUsed();
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('material', 'like', "%{$search}%");
            });
        }

        if ($activeCategorySlug !== '') {
            $cat = \App\Models\Category::where('slug', $activeCategorySlug)->first();
            if ($cat) {
                $catIds = \App\Models\Category::where('id', $cat->id)->orWhere('parent_id', $cat->id)->pluck('id');
                $query->whereIn('category_id', $catIds);
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        $sort = (string) $request->input('sort', 'newest');
        if ($sort === 'price_low') {
            $query->orderBy('base_price', 'asc');
        } elseif ($sort === 'price_high') {
            $query->orderBy('base_price', 'desc');
        } else {
            $query->latest();
        }

        $products = $query->paginate(16)->withQueryString();

        $vendorCategories = \App\Models\Category::whereHas('products', function ($q) use ($vendor) {
            $q->where('vendor_id', $vendor->id)->where('status', 'active');
            if ($vendor->vendor_type !== 'used') {
                $q->excludeUsed();
            }
        })->withCount(['products' => function ($q) use ($vendor) {
            $q->where('vendor_id', $vendor->id)->where('status', 'active');
            if ($vendor->vendor_type !== 'used') {
                $q->excludeUsed();
            }
        }])->get();

        $manifest = $themeRegistry->getManifest($resolvedThemeId);
        $themeConfig = $isPreview
            ? $themeService->getDraftConfigForTheme($vendor, $resolvedThemeId)
            : $themeService->getPublishedConfigForTheme($vendor, $resolvedThemeId);

        $response = response()->view('storefront.themes.collection', [
            'vendor' => $vendor,
            'manifest' => $manifest,
            'themeConfig' => $themeConfig,
            'products' => $products,
            'vendorCategories' => $vendorCategories,
            'activeCategorySlug' => $activeCategorySlug,
            'pageTitle' => $activeCategorySlug ? ucwords(str_replace('-', ' ', $activeCategorySlug)) : 'Collection',
            'isPreview' => $isPreview,
        ]);

        if ($isPreview) {
            $response->header('X-Robots-Tag', 'noindex, nofollow');
        }

        return $response;
    }

    public function subdomainCollection(Request $request, ?string $categorySlug = null)
    {
        $vendor = $request->attributes->get('current_vendor');
        if (!$vendor) {
            abort(404);
        }
        return $this->collection($request, $vendor->slug, $categorySlug);
    }

    /**
     * Store-themed Product Detail Page (uses real K71 product data, variants, reviews & cart endpoints).
     */
    public function productDetail(Request $request, string $slug, string $productSlug)
    {
        $cleanSlug = strtolower(trim($slug));
        $vendor = Vendor::with(['settings', 'storefrontConfig'])->where('slug', $cleanSlug)->firstOrFail();

        $isPreview = $request->boolean('preview') && auth()->check() && (auth()->user()->vendor?->id === $vendor->id || auth()->user()->hasRole('admin'));
        if (!$isPreview && !in_array($vendor->status, ['approved', 'active'], true)) {
            return response()->view('errors.store-suspended', ['vendor' => $vendor], 403);
        }

        $product = Product::with(['images', 'variants', 'category', 'vendor.settings'])
            ->where('vendor_id', $vendor->id)
            ->where('slug', $productSlug)
            ->where('status', 'active')
            ->firstOrFail();

        $themeRegistry = app(\App\Services\Storefront\ThemeMarketplaceRegistry::class);
        $themeService = app(\App\Services\Storefront\ThemeMarketplaceService::class);

        $queryTheme = strtolower(trim((string) $request->input('theme', '')));
        if ($queryTheme !== '' && $themeRegistry->isValidThemeId($queryTheme)) {
            $resolvedThemeId = $queryTheme;
        } elseif ($isPreview && $vendor->storefrontConfig?->draft_theme_id && $themeService->isVendorEntitled($vendor, $vendor->storefrontConfig->draft_theme_id)) {
            $resolvedThemeId = $vendor->storefrontConfig->draft_theme_id;
        } else {
            $resolvedThemeId = $themeService->resolvePublicThemeId($vendor);
        }

        if (!$themeRegistry->isNewTheme($resolvedThemeId)) {
            return redirect()->route('product.show', $product->slug);
        }

        app()->instance('current_vendor', $vendor);

        $vendorCategories = \App\Models\Category::whereHas('products', fn ($q) => $q->where('vendor_id', $vendor->id)->where('status', 'active'))->get();
        $productReviews = \App\Models\Review::where('product_id', $product->id)
            ->where('is_approved', true)
            ->with('user')
            ->latest()
            ->take(20)
            ->get();

        $relatedProducts = Product::with(['images', 'variants', 'category'])
            ->where('vendor_id', $vendor->id)
            ->where('id', '!=', $product->id)
            ->where('status', 'active')
            ->latest()
            ->take(4)
            ->get();

        $manifest = $themeRegistry->getManifest($resolvedThemeId);
        $themeConfig = $isPreview
            ? $themeService->getDraftConfigForTheme($vendor, $resolvedThemeId)
            : $themeService->getPublishedConfigForTheme($vendor, $resolvedThemeId);

        $response = response()->view('storefront.themes.product', [
            'vendor' => $vendor,
            'manifest' => $manifest,
            'themeConfig' => $themeConfig,
            'product' => $product,
            'productReviews' => $productReviews,
            'relatedProducts' => $relatedProducts,
            'vendorCategories' => $vendorCategories,
            'pageTitle' => $product->title,
            'metaDescription' => $product->seo_description ?: $product->short_description ?: $product->title,
            'canonicalUrl' => route('storefront.product', ['slug' => $vendor->slug, 'productSlug' => $product->slug]),
            'isPreview' => $isPreview,
        ]);

        if ($isPreview) {
            $response->header('X-Robots-Tag', 'noindex, nofollow');
        }

        return $response;
    }

    public function subdomainProductDetail(Request $request, string $productSlug)
    {
        $vendor = $request->attributes->get('current_vendor');
        if (!$vendor) {
            abort(404);
        }
        return $this->productDetail($request, $vendor->slug, $productSlug);
    }

    public function subdomainPortfolioDetail(Request $request, string $projectSlug)
    {
        $vendor = $request->attributes->get('current_vendor');
        if (!$vendor) {
            abort(404);
        }

        return $this->portfolioDetail($request, $vendor->slug, $projectSlug);
    }

    public function portfolioDetail(Request $request, string $slug, string $projectSlug)
    {
        $cleanSlug = strtolower(trim($slug));
        $subdomainManager = app(\App\Services\Services\SubdomainManagerService::class);
        $vendor = Vendor::with(['settings', 'storefrontConfig'])->where('slug', $cleanSlug)->first();

        if (!$vendor) {
            $redirectVendor = $subdomainManager->resolveRedirectVendor($cleanSlug);
            if ($redirectVendor) {
                return redirect()->away($subdomainManager->buildRedirectTarget($request, $redirectVendor, 'portfolio/' . $projectSlug), 301);
            }
            abort(404);
        }

        $isPreview = $request->boolean('preview') && auth()->check() && (auth()->user()->vendor?->id === $vendor->id || auth()->user()->hasRole('admin'));

        if ($vendor->isServices() && !$vendor->isWebsitePublished() && !$isPreview && !in_array($vendor->status, ['suspended', 'closed'], true) && $vendor->verification_status !== 'rejected') {
            return response()
                ->view('errors.website-unpublished', ['vendor' => $vendor], 404)
                ->header('X-Robots-Tag', 'noindex, nofollow');
        }

        if (!$isPreview && !in_array($vendor->status, ['approved', 'active'], true)) {
            return response()->view('errors.store-suspended', ['vendor' => $vendor], 403);
        }

        $project = \App\Models\PortfolioProject::with(['category', 'serviceProduct'])
            ->where('vendor_id', $vendor->id)
            ->where('slug', $projectSlug)
            ->when(!$isPreview, fn ($q) => $q->where('status', 'published'))
            ->firstOrFail();

        $config = $vendor->getStorefrontConfig($isPreview);
        $providerSite = app(\App\Services\Services\ServiceWebsitePresenter::class)->build($vendor, $isPreview, $config);

        if (!$isPreview) {
            $this->recordWebsiteEvent($request, $vendor, 'portfolio_view', 'portfolio_project', $project->id);
        }

        $moreProjects = \App\Models\PortfolioProject::where('vendor_id', $vendor->id)
            ->where('id', '!=', $project->id)
            ->where('status', 'published')
            ->latest('id')
            ->take(3)
            ->get();

        $response = response()->view('storefront.portfolio_detail', compact(
            'vendor',
            'project',
            'moreProjects',
            'config',
            'providerSite',
            'isPreview'
        ));

        if ($isPreview) {
            $response->header('X-Robots-Tag', 'noindex, nofollow');
        }

        return $response;
    }

    public function subdomainEnquire(Request $request)
    {
        $vendor = $request->attributes->get('current_vendor');
        if (!$vendor) {
            abort(404);
        }

        return $this->submitEnquiry($request, $vendor->slug);
    }

    public function submitEnquiry(Request $request, string $slug)
    {
        $cleanSlug = strtolower(trim($slug));
        $vendor = Vendor::where('slug', $cleanSlug)->firstOrFail();

        $contactPrefs = is_array($vendor->public_contact_preferences) ? $vendor->public_contact_preferences : [];
        if (array_key_exists('allow_direct_enquiry', $contactPrefs) && !$contactPrefs['allow_direct_enquiry']) {
            return back()->with('error', 'Direct enquiries are currently paused by this provider.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:120',
            'email' => 'required|email|max:150',
            'phone' => 'nullable|string|max:20',
            'subject' => 'required|string|max:200',
            'message' => 'required|string|min:10|max:3000',
            'budget' => 'nullable|numeric|min:0',
            'service_product_id' => 'nullable|integer',
        ]);

        $serviceProductId = null;
        if (!empty($validated['service_product_id'])) {
            $serviceProductId = Product::withoutGlobalScopes()
                ->where('vendor_id', $vendor->id)
                ->where('id', $validated['service_product_id'])
                ->value('id');
        }

        $enquiry = \App\Models\Enquiry::create([
            'enquiry_number' => 'ENQ-' . strtoupper(\Illuminate\Support\Str::random(8)),
            'user_id' => auth()->id(),
            'vendor_id' => $vendor->id,
            'product_id' => $serviceProductId,
            'title' => $validated['subject'],
            'description' => $validated['message'],
            'quantity' => '1 Project',
            'target_budget' => !empty($validated['budget']) ? (int) round(((float) $validated['budget']) * 100) : null,
            'city' => $vendor->city ?: 'Remote',
            'urgency' => 'within_1_week',
            'buyer_name' => $validated['name'],
            'buyer_phone' => trim((string) (($validated['phone'] ?? null) ?: (auth()->user()?->phone ?: 'Not provided'))),
            'buyer_email' => $validated['email'],
            'status' => 'open',
        ]);

        if (auth()->check() && auth()->id() !== $vendor->user_id) {
            $conversation = \App\Models\Conversation::firstOrCreate(
                [
                    'vendor_id' => $vendor->id,
                    'user_id' => auth()->id(),
                    'product_id' => $serviceProductId,
                ],
                [
                    'subject' => $validated['subject'],
                    'last_message_at' => now(),
                ]
            );

            \App\Models\Message::create([
                'conversation_id' => $conversation->id,
                'sender_type' => 'user',
                'sender_id' => auth()->id(),
                'body' => "New Website Enquiry ({$enquiry->enquiry_number}): {$validated['subject']}\n\n{$validated['message']}",
                'is_read' => false,
            ]);

            $conversation->update(['last_message_at' => now()]);
        }

        $this->recordWebsiteEvent($request, $vendor, 'enquiry_submit', 'enquiry', $enquiry->id);

        return back()->with('success', 'Your project enquiry (' . $enquiry->enquiry_number . ') was sent directly to ' . $vendor->shop_name . '!');
    }

    protected function recordWebsiteEvent(Request $request, Vendor $vendor, string $eventType, ?string $entityType = null, ?int $entityId = null): void
    {
        try {
            $visitorHash = hash('sha256', ($request->ip() ?: 'anon') . '|' . substr((string) $request->userAgent(), 0, 120));
            $dedupKey = "wf_evt_{$vendor->id}_{$eventType}_" . ($entityId ?: 0) . "_{$visitorHash}";

            if ($eventType !== 'enquiry_submit' && \Illuminate\Support\Facades\Cache::has($dedupKey)) {
                return;
            }

            \App\Models\StorefrontAnalyticsEvent::create([
                'vendor_id' => $vendor->id,
                'event_type' => $eventType,
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'visitor_hash' => $visitorHash,
                'referrer' => \Illuminate\Support\Str::limit((string) $request->headers->get('referer', ''), 255) ?: null,
                'created_at' => now(),
            ]);

            if ($eventType !== 'enquiry_submit') {
                \Illuminate\Support\Facades\Cache::put($dedupKey, true, now()->addMinutes(30));
            }
        } catch (\Throwable $e) {
            // Never interrupt storefront rendering for telemetry writes
        }
    }
}
