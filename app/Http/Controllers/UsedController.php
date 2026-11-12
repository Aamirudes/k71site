<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Offer;
use App\Models\Product;
use App\Models\Review;
use App\Models\UsedSavedSearch;
use App\Models\UsedThread;
use App\Models\Vendor;
use App\Models\Wishlist;
use App\Services\Used\UsedMarketplaceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class UsedController extends Controller
{
    public function __construct(private UsedMarketplaceService $usedService)
    {
    }

    /**
     * Discovery-first Used Marketplace Homepage (/used, /s/used, used.{domain}).
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $selectedCategory = trim((string) $request->query('cat', ''));
        $condition = trim((string) $request->query('condition', ''));
        $city = trim((string) $request->query('city', ''));
        $minPrice = $request->query('min_price');
        $maxPrice = $request->query('max_price');
        $negotiableOnly = $request->boolean('negotiable');
        $pricingMode = trim((string) $request->query('pricing', ''));
        $fulfillment = trim((string) $request->query('fulfillment', ''));
        $posted = trim((string) $request->query('posted', ''));
        $refurbished = $request->query('refurbished');
        $showSold = $request->boolean('include_sold');
        $openAdSlug = $request->query('ad');
        $sort = trim((string) $request->query('sort', 'newest'));
        $specFilters = array_filter((array) $request->query('spec', []), fn($v) => $v !== null && $v !== '');

        // Configurable parent categories with genuine active listing counts (rolled up from children)
        $parentCategories = Category::where('vertical', 'used')
            ->where('is_active', true)
            ->whereNull('parent_id')
            ->with(['children' => fn($q) => $q->where('is_active', true)->orderBy('sort_order')])
            ->orderBy('sort_order')
            ->get();

        $allUsedCategories = Category::where('vertical', 'used')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $visibility = app(\App\Services\Monetization\VisibilityService::class);
        $escapeLike = static fn (string $v): string => addcslashes($v, '%_\\');

        $activeCountsQuery = Product::withoutGlobalScopes()
            ->select('category_id', DB::raw('COUNT(*) as aggregate'))
            ->where('vertical', 'used')
            ->where('status', 'active')
            ->where('is_sold', false)
            ->where(function ($q) {
                $q->whereNull('used_status')->orWhere('used_status', 'active');
            });
        $visibility->scopeVisibleProducts($activeCountsQuery, 'used', true);
        $activeCountsByCat = $activeCountsQuery
            ->groupBy('category_id')
            ->pluck('aggregate', 'category_id');

        foreach ($parentCategories as $cat) {
            $childSum = $cat->children->sum(fn($child) => (int) ($activeCountsByCat[$child->id] ?? 0));
            $directCount = (int) ($activeCountsByCat[$cat->id] ?? 0);
            $cat->active_items_count = $directCount + $childSum;
            $cat->products_count = $cat->active_items_count;
            foreach ($cat->children as $child) {
                $child->active_items_count = (int) ($activeCountsByCat[$child->id] ?? 0);
            }
        }

        // Preserve $usedCategories alias for view/tests while including active counts
        $usedCategories = $parentCategories;

        // Genuine popular categories ordered strictly by live active listing activity (>0)
        $popularCategories = $parentCategories
            ->filter(fn($c) => ($c->active_items_count ?? 0) > 0)
            ->sortByDesc('active_items_count')
            ->take(6)
            ->values();

        // Real city list from active listings + standard regional centers
        $defaultCities = collect(config('used_marketplace.default_cities', []));
        $productCitiesQuery = Product::withoutGlobalScopes()
            ->where('vertical', 'used')
            ->where('status', 'active')
            ->where('is_sold', false)
            ->where(function ($q) {
                $q->whereNull('used_status')->orWhere('used_status', 'active');
            })
            ->whereNotNull('city');
        $visibility->scopeVisibleProducts($productCitiesQuery, 'used', true);
        $productCities = $productCitiesQuery->pluck('city')->unique();

        $vendorCitiesQuery = Vendor::whereHas('products', fn($q) => $q->where('vertical', 'used')->where('status', 'active'))
            ->whereNotNull('city');
        $visibility->scopeVisibleVendors($vendorCitiesQuery, 'used');
        $vendorCities = $vendorCitiesQuery->pluck('city')->unique();

        $availableCities = $defaultCities
            ->merge($productCities)
            ->merge($vendorCities)
            ->map(fn($c) => trim((string) $c))
            ->filter()
            ->unique()
            ->values();

        // Main discovery query
        $query = Product::withoutGlobalScopes()
            ->with(['images', 'category.parent', 'vendor', 'sellerUser.usedSellerProfile'])
            ->where('vertical', 'used')
            ->where('status', 'active');
        $visibility->scopeVisibleProducts($query, 'used', true);

        if ($showSold) {
            $query->where(function ($q) {
                $q->whereNull('used_status')
                  ->orWhereIn('used_status', ['active', 'reserved', 'sold']);
            });
        } else {
            $query->where('is_sold', false)
                ->where(function ($q) {
                    $q->whereNull('used_status')
                      ->orWhereIn('used_status', ['active', 'reserved']);
                });
        }

        if ($search !== '') {
            $searchPattern = '%' . $escapeLike($search) . '%';
            $query->where(function ($q) use ($searchPattern) {
                $q->where('title', 'like', $searchPattern)
                  ->orWhere('description', 'like', $searchPattern)
                  ->orWhere('brand', 'like', $searchPattern)
                  ->orWhere('model_name', 'like', $searchPattern)
                  ->orWhere('city', 'like', $searchPattern)
                  ->orWhere('locality', 'like', $searchPattern)
                  ->orWhere('tags', 'like', $searchPattern)
                  ->orWhereHas('category', fn($cq) => $cq->where('name', 'like', $searchPattern)->orWhere('slug', 'like', $searchPattern))
                  ->orWhereHas('vendor', fn($vq) => $vq->where('shop_name', 'like', $searchPattern)->orWhere('city', 'like', $searchPattern))
                  ->orWhereHas('sellerUser.usedSellerProfile', fn($sq) => $sq->where('display_name', 'like', $searchPattern)->orWhere('handle', 'like', $searchPattern));
            });
        }

        $activeCategoryObj = null;
        if ($selectedCategory !== '' && $selectedCategory !== 'all') {
            $catPattern = '%' . $escapeLike($selectedCategory) . '%';
            $activeCategoryObj = Category::where('vertical', 'used')
                ->where(function ($q) use ($selectedCategory, $catPattern) {
                    $q->where('slug', $selectedCategory);
                    if (is_numeric($selectedCategory)) {
                        $q->orWhere('id', (int) $selectedCategory);
                    }
                    $q->orWhere('name', 'like', $catPattern);
                })
                ->with('children')
                ->first();

            if ($activeCategoryObj) {
                $catIds = $activeCategoryObj->children->pluck('id')->push($activeCategoryObj->id)->all();
                $query->whereIn('category_id', $catIds);
            } else {
                $query->where(function ($q) use ($catPattern) {
                    $q->whereHas('category', fn($cq) => $cq->where('slug', 'like', $catPattern)->orWhere('name', 'like', $catPattern))
                      ->orWhere('title', 'like', $catPattern)
                      ->orWhere('brand', 'like', $catPattern);
                });
            }
        }

        if ($condition !== '') {
            $normalizedGrade = $this->usedService->normalizeConditionGrade($condition);
            $aliases = config("used_marketplace.conditions.{$normalizedGrade}.aliases", [$condition]);
            $condPattern = '%' . $escapeLike($condition) . '%';
            $query->where(function ($q) use ($normalizedGrade, $condPattern, $aliases, $escapeLike) {
                $q->where('condition_grade', $normalizedGrade)
                  ->orWhere('custom_fields->condition', 'like', $condPattern)
                  ->orWhere('custom_fields->specs->Condition', 'like', $condPattern);
                foreach ($aliases as $alias) {
                    $q->orWhere('custom_fields->condition', 'like', '%' . $escapeLike((string) $alias) . '%');
                }
            });
        }

        if ($city !== '' && $city !== 'All India') {
            $cityClean = trim(explode(',', $city)[0]);
            $cityPattern = '%' . $escapeLike($cityClean) . '%';
            $query->where(function ($q) use ($cityPattern) {
                $q->where('city', 'like', $cityPattern)
                  ->orWhere('locality', 'like', $cityPattern)
                  ->orWhere('custom_fields->location', 'like', $cityPattern)
                  ->orWhereHas('vendor', fn($vq) => $vq->where('city', 'like', $cityPattern));
            });
        }

        if ($minPrice !== null && $minPrice !== '' && is_numeric($minPrice)) {
            $query->where('base_price', '>=', (int) round(((float) $minPrice) * 100));
        }

        if ($maxPrice !== null && $maxPrice !== '' && is_numeric($maxPrice)) {
            $query->where('base_price', '<=', (int) round(((float) $maxPrice) * 100));
        }

        if ($negotiableOnly || $pricingMode === 'negotiable') {
            $query->where('is_negotiable', true);
        } elseif ($pricingMode === 'fixed') {
            $query->where('is_negotiable', false);
        }

        if ($fulfillment !== '' && in_array($fulfillment, ['local_pickup', 'public_meetup', 'seller_shipping'], true)) {
            $query->where("fulfillment_options->{$fulfillment}", true);
        }

        if ($posted === '24h') {
            $query->where('created_at', '>=', now()->subDay());
        } elseif ($posted === '7d') {
            $query->where('created_at', '>=', now()->subDays(7));
        } elseif ($posted === '30d') {
            $query->where('created_at', '>=', now()->subDays(30));
        }

        if ($refurbished === '1') {
            $query->where('is_refurbished', true);
        }

        foreach ($specFilters as $sKey => $sVal) {
            $safeKey = preg_replace('/[^a-zA-Z0-9_\- ]/', '', (string) $sKey);
            if ($safeKey !== '') {
                $query->where("custom_fields->specs->{$safeKey}", 'like', '%' . $escapeLike((string) $sVal) . '%');
            }
        }

        // Sorting
        if ($sort === 'price_low') {
            $query->orderBy('base_price', 'asc')->orderByDesc('created_at');
        } elseif ($sort === 'price_high') {
            $query->orderBy('base_price', 'desc')->orderByDesc('created_at');
        } elseif ($sort === 'relevance' && $search !== '') {
            $query->orderByRaw('CASE WHEN LOWER(title) LIKE ? THEN 0 ELSE 1 END', ['%' . $escapeLike(strtolower($search)) . '%'])
                  ->orderByDesc('refreshed_at')
                  ->orderByDesc('created_at');
        } else {
            $query->orderByDesc('refreshed_at')->latest();
        }

        $items = $query->paginate(24)->withQueryString();

        // Only genuinely featured & active listings receive featured prominence
        $featuredQuery = Product::withoutGlobalScopes()
            ->with(['images', 'category', 'vendor', 'sellerUser.usedSellerProfile'])
            ->where('vertical', 'used')
            ->where('status', 'active')
            ->where('is_sold', false)
            ->where('is_featured', true)
            ->where(function ($q) {
                $q->whereNull('used_status')->orWhere('used_status', 'active');
            });
        $visibility->scopeVisibleProducts($featuredQuery, 'used', true);
        $featuredItems = $featuredQuery->latest('refreshed_at')->take(4)->get();

        // Nearby / selected-city listings strip when a city is selected
        $nearLocationItems = collect();
        if ($city !== '' && $city !== 'All India') {
            $cityClean = trim(explode(',', $city)[0]);
            $nearPattern = '%' . $escapeLike($cityClean) . '%';
            $nearLocationQuery = Product::withoutGlobalScopes()
                ->with(['images', 'category', 'sellerUser.usedSellerProfile'])
                ->where('vertical', 'used')
                ->where('status', 'active')
                ->where('is_sold', false)
                ->where(function ($q) {
                    $q->whereNull('used_status')->orWhere('used_status', 'active');
                })
                ->where(function ($q) use ($nearPattern) {
                    $q->where('city', 'like', $nearPattern)
                      ->orWhere('locality', 'like', $nearPattern);
                });
            $visibility->scopeVisibleProducts($nearLocationQuery, 'used', true);
            $nearLocationItems = $nearLocationQuery->latest()->take(4)->get();
        }

        // Recently viewed real items from session
        $recentIds = array_map('intval', (array) $request->session()->get('used_recently_viewed', []));
        $recentlyViewed = collect();
        if (!empty($recentIds)) {
            $recentlyViewedQuery = Product::withoutGlobalScopes()
                ->with(['images', 'category'])
                ->where('vertical', 'used')
                ->where('status', 'active')
                ->where(function ($q) {
                    $q->whereNull('used_status')->orWhereIn('used_status', ['active', 'reserved']);
                })
                ->whereIn('id', $recentIds);
            $visibility->scopeVisibleProducts($recentlyViewedQuery, 'used', true);
            $recentlyViewedMap = $recentlyViewedQuery->get()->keyBy('id');
            $recentlyViewed = collect($recentIds)->map(fn($id) => $recentlyViewedMap->get($id))->filter()->values();
        }

        // Relevant recommendations based ONLY on genuine signals (wishlist / recent category)
        $relevantRecommendations = collect();
        $signalCatIds = $recentlyViewed->pluck('category_id')->unique()->filter()->values()->all();
        if (auth()->check() && empty($signalCatIds)) {
            $signalCatIds = Wishlist::where('user_id', auth()->id())
                ->whereHas('product', fn($q) => $q->where('vertical', 'used'))
                ->with('product:id,category_id')
                ->get()
                ->pluck('product.category_id')
                ->unique()
                ->filter()
                ->values()
                ->all();
        }
        if (!empty($signalCatIds)) {
            $relevantQuery = Product::withoutGlobalScopes()
                ->with(['images', 'category', 'sellerUser.usedSellerProfile'])
                ->where('vertical', 'used')
                ->where('status', 'active')
                ->where('is_sold', false)
                ->where(function ($q) {
                    $q->whereNull('used_status')->orWhere('used_status', 'active');
                })
                ->whereIn('category_id', $signalCatIds)
                ->when(!empty($recentIds), fn($q) => $q->whereNotIn('id', $recentIds));
            $visibility->scopeVisibleProducts($relevantQuery, 'used', true);
            $relevantRecommendations = $relevantQuery->latest()->take(4)->get();
        }

        // DB-backed saved IDs + Saved Searches + My Ads summary
        $savedProductIds = [];
        $savedSearches = collect();
        $myAds = collect();

        if (auth()->check()) {
            $user = auth()->user();
            $vendorId = $user->vendor?->id;

            $savedProductIds = Wishlist::where('user_id', $user->id)
                ->pluck('product_id')
                ->map(fn($id) => (int) $id)
                ->all();

            $savedSearches = UsedSavedSearch::where('user_id', $user->id)
                ->latest()
                ->take(6)
                ->get();

            $myAds = Product::withoutGlobalScopes()
                ->with(['images', 'category'])
                ->where('vertical', 'used')
                ->where(function ($q) use ($user, $vendorId) {
                    $q->where('user_id', $user->id)
                      ->orWhere('custom_fields->user_id', $user->id);
                    if ($vendorId) {
                        $q->orWhere('vendor_id', $vendorId);
                    }
                })
                ->latest()
                ->take(12)
                ->get();
        } else {
            $savedProductIds = array_map('intval', (array) $request->session()->get('guest_wishlist', []));
        }

        // Keep legacy view var name $recommendedItems mapped cleanly without synthetic entries
        $recommendedItems = $featuredItems;
        $activeFilterCategoryFields = $activeCategoryObj ? $activeCategoryObj->fields : collect();

        return view('used.index', compact(
            'usedCategories',
            'parentCategories',
            'allUsedCategories',
            'popularCategories',
            'availableCities',
            'items',
            'featuredItems',
            'recommendedItems',
            'nearLocationItems',
            'recentlyViewed',
            'relevantRecommendations',
            'savedProductIds',
            'savedSearches',
            'myAds',
            'search',
            'selectedCategory',
            'activeCategoryObj',
            'activeFilterCategoryFields',
            'condition',
            'city',
            'minPrice',
            'maxPrice',
            'negotiableOnly',
            'pricingMode',
            'fulfillment',
            'posted',
            'refurbished',
            'showSold',
            'openAdSlug',
            'sort',
            'specFilters'
        ));
    }

    /**
     * Show single Used listing detail page (/used/{slug}).
     */
    public function show(Request $request, string $slug): View
    {
        $product = Product::withoutGlobalScopes()
            ->with([
                'images',
                'category.parent',
                'category.fields',
                'vendor',
                'sellerUser.usedSellerProfile',
            ])
            ->where(function ($q) {
                $q->where('vertical', 'used')
                  ->orWhere('listing_type', 'classified')
                  ->orWhereHas('category', fn($cq) => $cq->where('vertical', 'used'));
            })
            ->where('slug', $slug)
            ->firstOrFail();

        $viewer = auth()->user();
        $isOwner = $product->isOwnedBy($viewer);

        $visibility = app(\App\Services\Monetization\VisibilityService::class);
        $isOwnerOrAdmin = $viewer && ($product->isOwnedBy($viewer) || $viewer->hasRole('admin'));
        if ((!$product->isPubliclyVisible() || ($product->vendor_id && !$visibility->isProductPubliclyVisible($product))) && !$isOwnerOrAdmin) {
            abort(404);
        }

        // Genuine view & recently viewed tracking (never inflated)
        $this->usedService->recordListingView($product, $request);
        $product->refresh();

        $ownerUser = $product->ownerUser();
        $sellerProfile = $ownerUser ? $this->usedService->getOrCreateSellerProfile($ownerUser) : null;

        $sellerOtherAds = collect();
        if ($ownerUser) {
            $ownerVendorId = $ownerUser->vendor?->id;
            $sellerOtherQuery = Product::withoutGlobalScopes()
                ->with(['images', 'category'])
                ->where('vertical', 'used')
                ->where('id', '!=', $product->id)
                ->where('status', 'active')
                ->where('is_sold', false)
                ->where(function ($q) {
                    $q->whereNull('used_status')->orWhere('used_status', 'active');
                })
                ->where(function ($q) use ($ownerUser, $ownerVendorId) {
                    $q->where('user_id', $ownerUser->id);
                    if ($ownerVendorId) {
                        $q->orWhere('vendor_id', $ownerVendorId);
                    }
                });
            $visibility->scopeVisibleProducts($sellerOtherQuery, 'used', true);
            $sellerOtherAds = $sellerOtherQuery->latest()->take(4)->get();
        }

        $sellerReviews = $ownerUser
            ? Review::with(['user', 'product'])
                ->where('reviewed_user_id', $ownerUser->id)
                ->where('is_approved', true)
                ->latest()
                ->take(5)
                ->get()
            : collect();

        $similarQuery = Product::withoutGlobalScopes()
            ->with(['images', 'vendor', 'sellerUser.usedSellerProfile'])
            ->where('vertical', 'used')
            ->where('status', 'active')
            ->where('is_sold', false)
            ->where(function ($q) {
                $q->whereNull('used_status')->orWhere('used_status', 'active');
            })
            ->where('id', '!=', $product->id)
            ->where('category_id', $product->category_id);
        $visibility->scopeVisibleProducts($similarQuery, 'used', true);
        $similarItems = $similarQuery->latest()->take(4)->get();

        if ($similarItems->isEmpty()) {
            $fallbackSimilarQuery = Product::withoutGlobalScopes()
                ->with(['images', 'vendor', 'sellerUser.usedSellerProfile'])
                ->where('vertical', 'used')
                ->where('status', 'active')
                ->where('is_sold', false)
                ->where(function ($q) {
                    $q->whereNull('used_status')->orWhere('used_status', 'active');
                })
                ->where('id', '!=', $product->id);
            $visibility->scopeVisibleProducts($fallbackSimilarQuery, 'used', true);
            $similarItems = $fallbackSimilarQuery->latest()->take(4)->get();
        }

        $isSaved = false;
        $myOffers = collect();
        $existingThread = null;

        if ($viewer) {
            $isSaved = Wishlist::where('user_id', $viewer->id)
                ->where('product_id', $product->id)
                ->exists();

            $myOffers = Offer::where('product_id', $product->id)
                ->where('user_id', $viewer->id)
                ->latest()
                ->get();

            $existingThread = UsedThread::where('product_id', $product->id)
                ->where('buyer_id', $viewer->id)
                ->first();
        } else {
            $guestSaved = (array) $request->session()->get('guest_wishlist', []);
            $isSaved = in_array((int) $product->id, array_map('intval', $guestSaved), true);
        }

        // SEO Meta & Accurate Schema.org Structured Data
        $conditionMeta = $product->conditionMeta();
        $availabilityUrl = match ($product->effectiveUsedStatus()) {
            'sold', 'paused', 'draft', 'removed' => 'https://schema.org/OutOfStock',
            'reserved' => 'https://schema.org/LimitedAvailability',
            default => 'https://schema.org/InStock',
        };
        $canonicalUrl = route('used.show', $product->slug);
        $robotsContent = ($product->isDraft() || $product->isPaused() || $request->boolean('preview'))
            ? 'noindex,nofollow'
            : 'index,follow';

        return view('used.show', compact(
            'product',
            'isOwner',
            'ownerUser',
            'sellerProfile',
            'sellerOtherAds',
            'sellerReviews',
            'similarItems',
            'isSaved',
            'myOffers',
            'existingThread',
            'conditionMeta',
            'availabilityUrl',
            'canonicalUrl',
            'robotsContent'
        ));
    }

    /**
     * Save a discovery filter preset for quick alerts/shortcuts.
     */
    public function saveSearch(Request $request): RedirectResponse
    {
        $user = auth()->user();
        if (!$user) {
            return redirect()->route('login');
        }

        $validated = $request->validate([
            'name' => 'nullable|string|max:120',
            'query' => 'nullable|string|max:200',
            'city' => 'nullable|string|max:100',
            'cat' => 'nullable|string|max:100',
            'condition' => 'nullable|string|max:50',
            'min_price' => 'nullable|numeric|min:0',
            'max_price' => 'nullable|numeric|min:0',
            'negotiable' => 'nullable|boolean',
        ]);

        $filters = array_filter([
            'cat' => $validated['cat'] ?? null,
            'condition' => $validated['condition'] ?? null,
            'min_price' => $validated['min_price'] ?? null,
            'max_price' => $validated['max_price'] ?? null,
            'negotiable' => !empty($validated['negotiable']) ? 1 : null,
        ], fn($v) => $v !== null && $v !== '');

        $queryVal = !empty($validated['query']) ? trim((string) $validated['query']) : null;
        $cityVal = !empty($validated['city']) ? trim((string) $validated['city']) : null;
        $nameVal = !empty($validated['name']) ? trim((string) $validated['name']) : null;

        if (!$queryVal && !$cityVal && empty($filters)) {
            return back()->with('error', 'Select at least a search keyword, city, or filter before saving.');
        }

        $labelParts = array_filter([
            $queryVal,
            !empty($filters['cat']) ? ucfirst(str_replace(['used-', '-'], ['', ' '], $filters['cat'])) : null,
            ($cityVal && $cityVal !== 'All India') ? $cityVal : null,
        ]);

        UsedSavedSearch::create([
            'user_id' => $user->id,
            'name' => $nameVal ?: (implode(' • ', $labelParts) ?: 'Used Search Alert'),
            'query' => $queryVal,
            'city' => $cityVal,
            'filters_json' => $filters,
        ]);

        return back()->with('success', 'Search saved! You can jump back to it anytime from K71 Used or your Dashboard.');
    }

    public function deleteSearch(UsedSavedSearch $savedSearch): RedirectResponse
    {
        $user = auth()->user();
        if (!$user || (int) $savedSearch->user_id !== (int) $user->id) {
            abort(403, 'Unauthorized to remove this saved search.');
        }

        $savedSearch->delete();

        return back()->with('success', 'Saved search removed.');
    }

    // Backwards-compatible delegate methods mapped to UsedListingController
    public function storeAd(Request $request)
    {
        return app(UsedListingController::class)->store($request);
    }

    public function editAd(Product $product)
    {
        return app(UsedListingController::class)->edit($product);
    }

    public function updateAd(Request $request, Product $product)
    {
        return app(UsedListingController::class)->update($request, $product);
    }

    public function toggleSold(Request $request, Product $product)
    {
        return app(UsedListingController::class)->toggleSold($request, $product);
    }

    public function deleteAd(Request $request, Product $product)
    {
        return app(UsedListingController::class)->destroy($request, $product);
    }
}
