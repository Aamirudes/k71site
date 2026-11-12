<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\Vendor;
use App\Services\Food\FoodMarketplaceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class FoodController extends Controller
{
    public function __construct(private readonly FoodMarketplaceService $foodService)
    {
    }

    /**
     * Persist selected delivery location / address for K71 Food discovery.
     */
    public function setLocation(Request $request)
    {
        $validated = $request->validate([
            'label' => 'nullable|string|max:120',
            'city' => 'nullable|string|max:80',
            'pincode' => 'nullable|string|max:15',
            'address_id' => 'nullable|integer',
            'clear' => 'nullable|boolean',
        ]);

        if ($request->boolean('clear')) {
            session()->forget(FoodMarketplaceService::SESSION_LOCATION_KEY);
            $loc = $this->foodService->getUserSelectedLocation($request);

            return $request->expectsJson()
                ? response()->json(['success' => true, 'location' => $loc])
                : back()->with('success', 'Delivery location reset.');
        }

        $payload = [
            'label' => !empty($validated['label']) ? trim($validated['label']) : null,
            'city' => !empty($validated['city']) ? trim($validated['city']) : null,
            'pincode' => !empty($validated['pincode']) ? trim($validated['pincode']) : null,
            'state' => null,
            'address_id' => null,
        ];

        if (!empty($validated['address_id']) && Auth::check()) {
            $address = Address::where('user_id', Auth::id())->find($validated['address_id']);
            if ($address) {
                $payload = [
                    'label' => $address->shortLabel(),
                    'city' => $address->city,
                    'pincode' => $address->pincode,
                    'state' => $address->state,
                    'address_id' => $address->id,
                ];
            }
        }

        if (empty($payload['label'])) {
            $payload['label'] = trim(collect([$payload['city'], $payload['pincode']])->filter()->implode(' ')) ?: 'All Locations';
        }

        session([FoodMarketplaceService::SESSION_LOCATION_KEY => $payload]);

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'location' => $payload,
            ]);
        }

        return back()->with('success', "Showing food & restaurants for {$payload['label']}.");
    }

    /**
     * Display the Food & Restaurants discovery hub (food.k71.in / food.marblemandi.org).
     */
    public function index(Request $request): View
    {
        $search = trim($request->query('search', ''));
        $dietary = $request->query('dietary');
        $cuisine = $request->query('cuisine');
        $pureVegOnly = $request->boolean('pure_veg') || $request->boolean('veg');
        $openNowOnly = $request->boolean('open_now');
        $takeawayOnly = $request->boolean('takeaway');
        $rating = $request->query('rating');
        $deliveryTime = $request->query('delivery_time');
        $budget = $request->query('budget');
        $sort = $request->query('sort', 'recommended');

        $selectedLocation = $this->foodService->getUserSelectedLocation($request);
        $selectedCity = $selectedLocation['city'] ?? null;
        $selectedPincode = $selectedLocation['pincode'] ?? null;

        // 1. Fetch active food categories
        $foodCategories = Category::where('vertical', 'food')
            ->where('is_active', true)
            ->withCount(['products' => function ($q) {
                $q->where('vertical', 'food')
                  ->where('status', 'active')
                  ->where('is_dish_available', true);
            }])
            ->orderBy('sort_order')
            ->get();

        // 2. Query Restaurants & Cloud Kitchens (both 'approved' and 'active' vendor states)
        $restaurantsQuery = Vendor::whereIn('vendors.status', ['approved', 'active'])
            ->where(function ($q) {
                $q->where('vendors.vendor_type', 'food')
                  ->orWhereHas('settings', fn ($sq) => $sq->where('is_restaurant', true))
                  ->orWhereHas('products', fn ($pq) => $pq->where('products.vertical', 'food')->where('products.status', 'active'));
            })
            ->with([
                'settings',
                'products' => fn ($q) => $q->where('vertical', 'food')
                    ->where('status', 'active')
                    ->where('is_dish_available', true)
                    ->with('images')
                    ->orderBy('menu_sort_order')
                    ->take(4),
            ]);

        if ($pureVegOnly) {
            $restaurantsQuery->whereHas('settings', fn ($sq) => $sq->where('is_pure_veg', true));
        }

        if ($takeawayOnly) {
            $restaurantsQuery->whereHas('settings', fn ($sq) => $sq->whereIn('food_delivery_mode', ['takeaway', 'both']));
        }

        if ($search) {
            $restaurantsQuery->where(function ($q) use ($search) {
                $q->where('vendors.shop_name', 'like', "%{$search}%")
                  ->orWhere('vendors.city', 'like', "%{$search}%")
                  ->orWhere('vendors.description', 'like', "%{$search}%")
                  ->orWhereHas('settings', fn ($sq) => $sq->where('cuisine_types', 'like', "%{$search}%"))
                  ->orWhereHas('products', fn ($pq) => $pq->where('products.vertical', 'food')
                      ->where('products.status', 'active')
                      ->where(function ($dq) use ($search) {
                          $dq->where('products.title', 'like', "%{$search}%")
                             ->orWhere('products.description', 'like', "%{$search}%");
                      }));
            });
        }

        if ($cuisine) {
            $restaurantsQuery->where(function ($q) use ($cuisine) {
                $q->where('vendors.description', 'like', "%{$cuisine}%")
                  ->orWhereHas('settings', fn ($sq) => $sq->where('cuisine_types', 'like', "%{$cuisine}%"))
                  ->orWhereHas('products', fn ($pq) => $pq->where('products.custom_fields->cuisine', 'like', "%{$cuisine}%"));
            });
        }

        if ($deliveryTime === 'under_30') {
            $restaurantsQuery->whereHas('settings', fn ($sq) => $sq->where('avg_prep_time_mins', '<=', 25));
        }

        if ($budget === 'under_200') {
            $restaurantsQuery->whereHas('products', fn ($pq) => $pq->where('vertical', 'food')
                ->where('status', 'active')
                ->where('base_price', '<=', 25000));
        }

        if ($rating === '4plus') {
            $restaurantsQuery->where(function ($q) {
                $q->where('vendors.rating', '>=', 4.0)
                  ->orWhereHas('settings', fn ($sq) => $sq->where('food_avg_rating', '>=', 4.0));
            });
        }

        $candidateRestaurants = $restaurantsQuery->orderByDesc('vendors.created_at')->get();

        // Annotate each restaurant with real serviceability & operational state, then filter/sort honestly
        $filteredRestaurants = $candidateRestaurants->map(function (Vendor $rest) use ($selectedCity, $selectedPincode) {
            $op = $this->foodService->getRestaurantOperationalStatus($rest);
            $rest->setAttribute('operational_status', $op);
            $rest->setAttribute('is_serviceable_for_location', $this->foodService->isServiceableForLocation($rest, $selectedCity, $selectedPincode));
            return $rest;
        })->filter(function (Vendor $rest) use ($selectedCity, $selectedPincode, $openNowOnly) {
            if (($selectedCity || $selectedPincode) && !$rest->getAttribute('is_serviceable_for_location')) {
                return false;
            }
            if ($openNowOnly && !($rest->getAttribute('operational_status')['accepting_orders'] ?? false)) {
                return false;
            }
            return true;
        });

        if ($sort === 'delivery_time') {
            $filteredRestaurants = $filteredRestaurants->sortBy(fn ($r) => (int) ($r->settings?->avg_prep_time_mins ?? 35));
        } elseif ($sort === 'rating') {
            $filteredRestaurants = $filteredRestaurants->sortByDesc(fn ($r) => (float) ($r->food_rating ?? 0));
        } elseif ($sort === 'cost_low') {
            $filteredRestaurants = $filteredRestaurants->sortBy(fn ($r) => (int) ($r->products->min('base_price') ?? 999999));
        } else {
            // Recommended: accepting orders first, then highest rated, then newest
            $filteredRestaurants = $filteredRestaurants->sortByDesc(function (Vendor $r) {
                $openScore = ($r->getAttribute('operational_status')['accepting_orders'] ?? false) ? 1000000 : 0;
                $ratingScore = (int) round(((float) ($r->food_rating ?? 0)) * 100);
                return $openScore + $ratingScore;
            });
        }

        $filteredRestaurants = $filteredRestaurants->values();
        $page = max(1, (int) $request->query('page', 1));
        $perPage = 12;
        $restaurants = new LengthAwarePaginator(
            $filteredRestaurants->forPage($page, $perPage)->values(),
            $filteredRestaurants->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        // Featured kitchens (strictly genuine open/approved kitchens, preferring serviceable ones)
        $featuredRestaurants = $filteredRestaurants
            ->filter(fn ($r) => $r->getAttribute('operational_status')['accepting_orders'] ?? false)
            ->take(6);
        if ($featuredRestaurants->isEmpty()) {
            $featuredRestaurants = $filteredRestaurants->take(6);
        }

        $visibleVendorIds = $filteredRestaurants->pluck('id')->all();
        $restaurantMap = $filteredRestaurants->keyBy('id');

        // 3. Query Trending / Available Food Dishes
        $dishesQuery = Product::with(['images', 'vendor.settings', 'category', 'foodMenuCategory', 'variants', 'optionGroups.modifiers', 'modifiers'])
            ->where('vertical', 'food')
            ->where('status', 'active')
            ->where('is_dish_available', true)
            ->whereHas('vendor', fn ($vq) => $vq->whereIn('status', ['approved', 'active']));

        if ($selectedCity || $selectedPincode || $openNowOnly || $takeawayOnly || $deliveryTime === 'under_30' || $rating === '4plus') {
            $dishesQuery->whereIn('vendor_id', !empty($visibleVendorIds) ? $visibleVendorIds : [-1]);
        }

        if ($search) {
            $dishesQuery->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('vendor', fn ($vq) => $vq->where('shop_name', 'like', "%{$search}%"));
            });
        }

        if ($dietary) {
            $dishesQuery->where(function ($q) use ($dietary) {
                $q->where('dietary_label', 'like', "%{$dietary}%")
                  ->orWhere('custom_fields->dietary_type', 'like', "%{$dietary}%");
            });
        }

        if ($cuisine) {
            $dishesQuery->where(function ($q) use ($cuisine) {
                $q->where('custom_fields->cuisine', 'like', "%{$cuisine}%")
                  ->orWhereHas('category', fn ($cq) => $cq->where('name', 'like', "%{$cuisine}%")->orWhere('slug', 'like', "%{$cuisine}%"))
                  ->orWhereHas('foodMenuCategory', fn ($mq) => $mq->where('name', 'like', "%{$cuisine}%"))
                  ->orWhereHas('vendor.settings', fn ($sq) => $sq->where('cuisine_types', 'like', "%{$cuisine}%"));
            });
        }

        if ($pureVegOnly) {
            $dishesQuery->where(function ($q) {
                $q->whereIn('dietary_label', ['veg', 'pure_veg', 'vegan', 'jain'])
                  ->orWhere(function ($cfq) {
                      $cfq->where('custom_fields->dietary_type', 'like', '%Veg%')
                          ->where('custom_fields->dietary_type', 'not like', '%Non-Veg%');
                  });
            });
        }

        if ($budget === 'under_200') {
            $dishesQuery->where('base_price', '<=', 20000);
        }

        if ($sort === 'cost_low') {
            $dishesQuery->orderBy('base_price');
        } elseif ($sort === 'rating') {
            $dishesQuery->orderByDesc('avg_rating')->latest();
        } else {
            $dishesQuery->orderByDesc('is_featured')->latest();
        }

        $trendingDishes = $dishesQuery->take(40)->get()->filter(function (Product $dish) use ($pureVegOnly, $openNowOnly, $restaurantMap) {
            if (!$this->foodService->isDishAvailableForOrder($dish)) {
                return false;
            }
            if ($pureVegOnly && !$dish->isVeg()) {
                return false;
            }
            $vendorOp = $restaurantMap->get($dish->vendor_id)?->getAttribute('operational_status')
                ?? ($dish->vendor ? $this->foodService->getRestaurantOperationalStatus($dish->vendor) : ['accepting_orders' => true, 'label' => 'Open']);
            if ($openNowOnly && !($vendorOp['accepting_orders'] ?? false)) {
                return false;
            }
            $dish->setAttribute('kitchen_operational_status', $vendorOp);
            return true;
        });

        if ($sort === 'delivery_time') {
            $trendingDishes = $trendingDishes->sortBy(fn (Product $d) => (int) ($d->prep_time_mins ?: ($d->vendor?->settings?->avg_prep_time_mins ?? 35)));
        } elseif ($sort === 'recommended') {
            $trendingDishes = $trendingDishes->sortByDesc(function (Product $d) {
                $op = $d->getAttribute('kitchen_operational_status');
                return (($op['accepting_orders'] ?? true) ? 10000 : 0) + (int) round(((float) ($d->avg_rating ?? 0)) * 100);
            });
        }
        $trendingDishes = $trendingDishes->values();

        // 4. Real Active Restaurant Coupons / Offers only (no fake hardcoded coupons)
        $activeDeals = Coupon::where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>=', now());
            })
            ->whereHas('vendor', function ($vq) {
                $vq->whereIn('status', ['approved', 'active'])
                   ->where(function ($fq) {
                       $fq->where('vendor_type', 'food')
                          ->orWhereHas('settings', fn ($sq) => $sq->where('is_restaurant', true));
                   });
            })
            ->with('vendor')
            ->latest()
            ->take(6)
            ->get();

        // 5. Recent Food Orders + Saved Delivery Addresses for Logged-In Customer
        $recentFoodOrders = collect();
        $savedAddresses = collect();
        if (Auth::check()) {
            $recentFoodOrders = Order::where('user_id', Auth::id())
                ->whereIn('order_type', ['food_delivery', 'food_takeaway'])
                ->with(['items.product.images', 'vendor'])
                ->latest()
                ->take(3)
                ->get();

            if (Schema::hasTable('addresses')) {
                $savedAddresses = Address::where('user_id', Auth::id())
                    ->orderByDesc('is_default')
                    ->latest()
                    ->get();
            }
        }

        // Known available cities for quick location suggestions (from real DB kitchens)
        $availableCities = Vendor::whereIn('status', ['approved', 'active'])
            ->where(function ($q) {
                $q->where('vendor_type', 'food')
                  ->orWhereHas('settings', fn ($sq) => $sq->where('is_restaurant', true));
            })
            ->whereNotNull('city')
            ->where('city', '!=', '')
            ->distinct()
            ->pluck('city')
            ->sort()
            ->values();

        $foodSettings = \App\Models\Setting::getGroup('food_page');

        return view('food.index', compact(
            'foodCategories',
            'restaurants',
            'featuredRestaurants',
            'trendingDishes',
            'activeDeals',
            'recentFoodOrders',
            'savedAddresses',
            'selectedLocation',
            'availableCities',
            'search',
            'dietary',
            'cuisine',
            'pureVegOnly',
            'openNowOnly',
            'takeawayOnly',
            'rating',
            'deliveryTime',
            'budget',
            'sort',
            'foodSettings'
        ));
    }

    /**
     * Display a specific restaurant's digital menu & ordering storefront.
     */
    public function restaurant(Request $request, string $slug): View
    {
        $restaurant = Vendor::where('slug', $slug)
            ->whereIn('status', ['approved', 'active'])
            ->with([
                'settings',
                'foodMenuCategories' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order'),
            ])
            ->firstOrFail();

        $selectedCategory = $request->query('cat');
        $vegOnly = $request->boolean('veg_only');
        $menuSearch = trim($request->query('search', ''));

        $operationalStatus = $this->foodService->getRestaurantOperationalStatus($restaurant);
        $selectedLocation = $this->foodService->getUserSelectedLocation($request);
        $isServiceable = $this->foodService->isServiceableForLocation(
            $restaurant,
            $selectedLocation['city'] ?? null,
            $selectedLocation['pincode'] ?? null
        );

        // Menu items query (show all active items, visually tagging temporarily paused dishes cleanly)
        $menuQuery = Product::with(['images', 'category', 'foodMenuCategory', 'variants', 'optionGroups.modifiers', 'modifiers'])
            ->where('vendor_id', $restaurant->id)
            ->where('vertical', 'food')
            ->where('status', 'active')
            ->orderBy('menu_sort_order')
            ->orderBy('id');

        if ($selectedCategory) {
            $menuQuery->where(function ($q) use ($selectedCategory) {
                $q->whereHas('foodMenuCategory', fn ($fmc) => $fmc->where('slug', $selectedCategory))
                  ->orWhereHas('category', fn ($cq) => $cq->where('slug', $selectedCategory));
            });
        }

        if ($menuSearch) {
            $menuQuery->where(function ($q) use ($menuSearch) {
                $q->where('title', 'like', "%{$menuSearch}%")
                  ->orWhere('description', 'like', "%{$menuSearch}%");
            });
        }

        $menuItems = $menuQuery->get();

        if ($vegOnly) {
            $menuItems = $menuItems->filter(fn (Product $item) => $item->isVeg())->values();
        }

        // Group menu preferring custom restaurant FoodMenuCategory, falling back to Category
        $groupedMenu = $menuItems->groupBy(function (Product $item) {
            return $item->foodMenuCategory?->name
                ?? $item->category?->name
                ?? 'Chef Specials & Recommended';
        });

        // Build ordered category tabs with real counts
        $allActiveDishes = Product::with(['foodMenuCategory', 'category'])
            ->where('vendor_id', $restaurant->id)
            ->where('vertical', 'food')
            ->where('status', 'active')
            ->orderBy('menu_sort_order')
            ->get();

        $menuCategories = collect();
        foreach ($restaurant->foodMenuCategories as $fmc) {
            $cnt = $allActiveDishes->where('food_menu_category_id', $fmc->id)->count();
            if ($cnt > 0) {
                $menuCategories->push((object) [
                    'slug' => $fmc->slug,
                    'name' => $fmc->name,
                    'products_count' => $cnt,
                ]);
            }
        }

        $uncatByGlobalCat = $allActiveDishes->whereNull('food_menu_category_id')->groupBy('category_id');
        foreach ($uncatByGlobalCat as $dishesInGlobal) {
            $cat = $dishesInGlobal->first()?->category;
            if ($cat && !$menuCategories->contains('slug', $cat->slug)) {
                $menuCategories->push((object) [
                    'slug' => $cat->slug,
                    'name' => $cat->name,
                    'products_count' => $dishesInGlobal->count(),
                ]);
            }
        }

        // Active restaurant-specific coupons
        $restaurantCoupons = Coupon::where('vendor_id', $restaurant->id)
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>=', now());
            })
            ->get();

        // Real customer reviews (restaurant + dish reviews combined)
        $reviews = Review::with(['user', 'product'])
            ->where('is_approved', true)
            ->where(function ($q) use ($restaurant) {
                $q->where('vendor_id', $restaurant->id)
                  ->orWhereHas('product', fn ($pq) => $pq->where('vendor_id', $restaurant->id)->where('vertical', 'food'));
            })
            ->latest()
            ->take(20)
            ->get();

        $eligibleOrderForReview = null;
        if (Auth::check()) {
            $eligibleOrderForReview = Order::where('user_id', Auth::id())
                ->where('vendor_id', $restaurant->id)
                ->whereIn('order_type', ['food_delivery', 'food_takeaway'])
                ->where('status', 'delivered')
                ->whereDoesntHave('reviews', fn ($rq) => $rq->where('review_type', 'food_order'))
                ->latest()
                ->first();
        }

        return view('food.restaurant', compact(
            'restaurant',
            'operationalStatus',
            'selectedLocation',
            'isServiceable',
            'groupedMenu',
            'menuCategories',
            'selectedCategory',
            'vegOnly',
            'menuSearch',
            'restaurantCoupons',
            'reviews',
            'eligibleOrderForReview'
        ));
    }

    /**
     * Live search suggestions JSON endpoint for instant autocomplete.
     */
    public function suggest(Request $request): JsonResponse
    {
        $q = trim($request->query('q', ''));
        if (strlen($q) < 2) {
            return response()->json(['restaurants' => [], 'dishes' => []]);
        }

        $restaurants = Vendor::whereIn('status', ['approved', 'active'])
            ->where(function ($query) {
                $query->where('vendor_type', 'food')
                      ->orWhereHas('settings', fn ($sq) => $sq->where('is_restaurant', true))
                      ->orWhereHas('products', fn ($pq) => $pq->where('vertical', 'food')->where('status', 'active'));
            })
            ->where(function ($query) use ($q) {
                $query->where('shop_name', 'like', "%{$q}%")
                      ->orWhere('city', 'like', "%{$q}%")
                      ->orWhereHas('settings', fn ($sq) => $sq->where('cuisine_types', 'like', "%{$q}%"));
            })
            ->with('settings')
            ->take(5)
            ->get(['id', 'shop_name', 'slug', 'city', 'logo', 'rating']);

        $dishes = Product::where('vertical', 'food')
            ->where('status', 'active')
            ->where('is_dish_available', true)
            ->where('title', 'like', "%{$q}%")
            ->whereHas('vendor', fn ($vq) => $vq->whereIn('status', ['approved', 'active']))
            ->with(['vendor:id,shop_name,slug', 'images'])
            ->take(6)
            ->get(['id', 'vendor_id', 'title', 'slug', 'base_price', 'dietary_label', 'custom_fields']);

        return response()->json([
            'restaurants' => $restaurants->map(fn ($r) => [
                'name' => $r->shop_name,
                'url' => route('food.restaurant', $r->slug),
                'city' => $r->city,
                'rating' => $r->food_rating,
                'image' => $r->logo_url,
            ]),
            'dishes' => $dishes->map(fn ($d) => [
                'title' => $d->title,
                'restaurant' => $d->vendor?->shop_name,
                'restaurant_url' => $d->vendor ? route('food.restaurant', $d->vendor->slug) : '#',
                'price' => number_format($d->base_price / 100, 0),
                'dietary' => $d->dietary_label_resolved,
                'is_veg' => $d->isVeg(),
                'image' => $d->primary_image_url,
            ]),
        ]);
    }

    /**
     * 1-Click Reorder previous food order with live price, availability & conflict safety.
     */
    public function reorder(Request $request, Order $order)
    {
        if (!Auth::check() || (int) $order->user_id !== (int) Auth::id()) {
            abort(403);
        }

        $order->loadMissing(['items.product.optionGroups.modifiers', 'items.product.modifiers', 'items.variant', 'vendor.settings']);
        $vendor = $order->vendor;
        if (!$vendor) {
            return back()->with('error', 'This restaurant is no longer on K71 Food.');
        }

        $opStatus = $this->foodService->getRestaurantOperationalStatus($vendor);
        if (!$opStatus['accepting_orders']) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $opStatus['reason']], 422);
            }
            return back()->with('error', $opStatus['reason'] ?: 'This kitchen is not accepting orders right now.');
        }

        $cart = Cart::firstOrCreate(['user_id' => Auth::id()]);

        // Enforce single-restaurant food cart policy cleanly
        $conflictingItems = $cart->items()->with(['vendor.settings', 'product'])->get()->filter(function ($item) use ($vendor) {
            if ((int) $item->vendor_id === (int) $vendor->id) {
                return false;
            }
            return ($item->product?->vertical === 'food')
                || ($item->vendor?->isFood() ?? false)
                || ($item->vendor?->settings?->is_restaurant ?? false);
        });

        if ($conflictingItems->isNotEmpty()) {
            if (!$request->boolean('clear_existing')) {
                $existingName = $conflictingItems->first()?->vendor?->shop_name ?? 'another restaurant';
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'conflict' => true,
                        'message' => "Your bag currently contains dishes from {$existingName}. Clear your bag to reorder from {$vendor->shop_name}?",
                    ], 409);
                }
                return back()->with('error', "Your bag has dishes from {$existingName}. Please clear your bag first before reordering from {$vendor->shop_name}.");
            }
            $cart->items()->whereIn('id', $conflictingItems->pluck('id')->all())->delete();
        }

        $addedCount = 0;
        $skippedNames = [];

        foreach ($order->items as $item) {
            $product = $item->product;
            if (!$product || !$this->foodService->isDishAvailableForOrder($product)) {
                $skippedNames[] = $item->snapshot['title'] ?? 'Unavailable dish';
                continue;
            }

            $variant = $item->variant_id ? $product->variants()->find($item->variant_id) : $product->variants->first();
            $requestedModIds = collect($item->selected_modifiers_json ?? [])->pluck('id')->filter()->values()->all();

            try {
                $calc = $this->foodService->validateAndCalculateCustomizations($product, $variant, $requestedModIds);
            } catch (\Illuminate\Validation\ValidationException $e) {
                // Skip unavailable modifiers cleanly when safe, or skip dish if required option changed
                try {
                    $calc = $this->foodService->validateAndCalculateCustomizations($product, $variant, []);
                } catch (\Illuminate\Validation\ValidationException $e2) {
                    $skippedNames[] = $product->title;
                    continue;
                }
            }

            $cart->items()->create([
                'product_id' => $product->id,
                'variant_id' => $variant?->id,
                'vendor_id' => $vendor->id,
                'quantity' => max(1, (int) $item->quantity),
                'price_at_add' => $calc['unit_price_paise'],
                'selected_modifiers_json' => $calc['modifiers_snapshot'] ?: null,
                'option_selections_json' => $calc['option_selections_snapshot'] ?: null,
                'special_instructions' => $item->special_instructions,
            ]);
            $addedCount++;
        }

        if ($addedCount === 0) {
            return redirect()->route('food.restaurant', $vendor->slug)
                ->with('error', 'None of the dishes from your previous order are currently available. Please choose fresh items from the menu.');
        }

        $message = $addedCount . ' dish(es) added to your bag!';
        if (!empty($skippedNames)) {
            $message .= ' Skipped unavailable: ' . implode(', ', array_unique($skippedNames)) . '.';
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'added' => $addedCount,
                'skipped' => $skippedNames,
                'redirect' => route('checkout.vendor', $vendor->id),
            ]);
        }

        return redirect()->route('checkout.vendor', $vendor->id)->with('success', $message);
    }
}
