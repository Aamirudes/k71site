<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\Vendor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class FoodController extends Controller
{
    /**
     * Display the Food & Restaurants discovery hub (food.k71.in / food.marblemandi.org).
     */
    public function index(Request $request): View
    {
        $search = trim($request->query('search', ''));
        $dietary = $request->query('dietary');
        $cuisine = $request->query('cuisine');
        $pureVegOnly = $request->boolean('pure_veg') || $request->boolean('veg');
        $rating = $request->query('rating');
        $deliveryTime = $request->query('delivery_time');
        $budget = $request->query('budget');
        $sort = $request->query('sort', 'recommended');

        // 1. Fetch active food categories
        $foodCategories = Category::where('vertical', 'food')
            ->where('is_active', true)
            ->withCount(['products' => function ($q) {
                $q->where('vertical', 'food')->where('status', 'active');
            }])
            ->orderBy('sort_order')
            ->get();

        // 2. Query Restaurants & Cloud Kitchens
        $restaurantsQuery = Vendor::where('vendors.status', 'approved')
            ->where(function ($q) {
                $q->where('vendors.vendor_type', 'food')
                  ->orWhereHas('settings', fn($sq) => $sq->where('is_restaurant', true))
                  ->orWhereHas('products', fn($pq) => $pq->where('products.vertical', 'food')->where('products.status', 'active'));
            })
            ->with(['settings', 'products' => fn($q) => $q->where('vertical', 'food')->where('status', 'active')->with('images')->take(4)]);

        // Pure Veg filter
        if ($pureVegOnly) {
            $restaurantsQuery->where(function($q) {
                $q->whereHas('settings', fn($sq) => $sq->where('is_pure_veg', true));
            });
        }

        // Search filter
        if ($search) {
            $restaurantsQuery->where(function ($q) use ($search) {
                $q->where('vendors.shop_name', 'like', "%{$search}%")
                  ->orWhere('vendors.city', 'like', "%{$search}%")
                  ->orWhere('vendors.description', 'like', "%{$search}%")
                  ->orWhereHas('products', fn($pq) => $pq->where('products.title', 'like', "%{$search}%")->orWhere('products.description', 'like', "%{$search}%"));
            });
        }

        // Cuisine filter
        if ($cuisine) {
            $restaurantsQuery->where(function ($q) use ($cuisine) {
                $q->where('vendors.description', 'like', "%{$cuisine}%")
                  ->orWhereHas('settings', fn($sq) => $sq->where('cuisine_types', 'like', "%{$cuisine}%"))
                  ->orWhereHas('products', fn($pq) => $pq->where('products.custom_fields->cuisine', 'like', "%{$cuisine}%"));
            });
        }

        // Delivery time filter (Under 25-30 mins)
        if ($deliveryTime === 'under_30') {
            $restaurantsQuery->whereHas('settings', fn($sq) => $sq->where('avg_prep_time_mins', '<=', 25));
        }

        // Budget filter (Under ₹200 for two or affordable)
        if ($budget === 'under_200') {
            $restaurantsQuery->whereHas('products', fn($pq) => $pq->where('base_price', '<=', 25000));
        }

        // Sort ordering
        if ($sort === 'delivery_time') {
            $restaurantsQuery->leftJoin('vendor_settings', 'vendors.id', '=', 'vendor_settings.vendor_id')
                ->orderBy('vendor_settings.avg_prep_time_mins', 'asc')
                ->select('vendors.*');
        } elseif ($sort === 'cost_low') {
            $restaurantsQuery->leftJoin('products', function($join) {
                $join->on('vendors.id', '=', 'products.vendor_id')
                     ->where('products.vertical', '=', 'food')
                     ->where('products.status', '=', 'active');
            })->orderBy('products.base_price', 'asc')->select('vendors.*')->distinct();
        } else {
            $restaurantsQuery->orderBy('vendors.created_at', 'desc')->select('vendors.*');
        }

        $restaurants = $restaurantsQuery->paginate(12)->withQueryString();

        // Featured & Top Cloud Kitchens for Highlight Carousel
        $featuredRestaurants = Vendor::where('vendors.status', 'approved')
            ->where('vendors.vendor_type', 'food')
            ->with(['settings', 'products' => fn($q) => $q->where('vertical', 'food')->where('status', 'active')->take(3)])
            ->take(6)
            ->get();

        // 3. Query Trending / Featured Food Dishes
        $dishesQuery = Product::with(['images', 'vendor.settings', 'category', 'variants', 'modifiers'])
            ->where('vertical', 'food')
            ->where('status', 'active');

        if ($search) {
            $dishesQuery->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($dietary) {
            $dishesQuery->where('custom_fields->dietary_type', 'like', "%{$dietary}%");
        }

        if ($cuisine) {
            $dishesQuery->where('custom_fields->cuisine', 'like', "%{$cuisine}%");
        }

        if ($pureVegOnly) {
            $dishesQuery->where(function($q) {
                $q->where('custom_fields->dietary_type', 'like', '%Veg%')
                  ->where('custom_fields->dietary_type', 'not like', '%Non-Veg%');
            });
        }

        if ($budget === 'under_200') {
            $dishesQuery->where('base_price', '<=', 20000);
        }

        $trendingDishes = $dishesQuery->latest()->take(20)->get();

        // 4. Active Deals and Discount Coupons
        $activeDeals = Coupon::where('is_active', true)
            ->where('starts_at', '<=', now())
            ->where('expires_at', '>=', now())
            ->take(4)
            ->get();

        // 5. Recent Food Orders for Logged-In Customer
        $recentFoodOrders = collect();
        if (Auth::check()) {
            $recentFoodOrders = Order::where('user_id', Auth::id())
                ->where(function($q) {
                    $q->where('order_type', 'food_delivery')
                      ->orWhere('order_type', 'food_takeaway');
                })
                ->with(['items.product.images', 'vendor'])
                ->latest()
                ->take(3)
                ->get();
        }

        $foodSettings = \App\Models\Setting::getGroup('food_page');

        return view('food.index', compact(
            'foodCategories',
            'restaurants',
            'featuredRestaurants',
            'trendingDishes',
            'activeDeals',
            'recentFoodOrders',
            'search',
            'dietary',
            'cuisine',
            'pureVegOnly',
            'rating',
            'deliveryTime',
            'budget',
            'sort',
            'foodSettings'
        ));
    }

    /**
     * Display a specific restaurant's digital menu & ordering page.
     */
    public function restaurant(Request $request, string $slug): View
    {
        $restaurant = Vendor::where('slug', $slug)
            ->where('status', 'approved')
            ->with('settings')
            ->firstOrFail();

        $selectedCategory = $request->query('cat');
        $vegOnly = $request->boolean('veg_only');
        $menuSearch = trim($request->query('search', ''));

        // Menu items query
        $menuQuery = Product::with(['images', 'category', 'variants', 'modifiers'])
            ->where('vendor_id', $restaurant->id)
            ->where('vertical', 'food')
            ->where('status', 'active');

        if ($selectedCategory) {
            $menuQuery->whereHas('category', fn($q) => $q->where('slug', $selectedCategory));
        }

        if ($vegOnly) {
            $menuQuery->where('custom_fields->dietary_type', 'like', '%Veg%')
                      ->where('custom_fields->dietary_type', 'not like', '%Non-Veg%');
        }

        if ($menuSearch) {
            $menuQuery->where(function($q) use ($menuSearch) {
                $q->where('title', 'like', "%{$menuSearch}%")
                  ->orWhere('description', 'like', "%{$menuSearch}%");
            });
        }

        $menuItems = $menuQuery->latest()->get();

        // Group menu by category
        $groupedMenu = $menuItems->groupBy(function ($item) {
            return $item->category?->name ?? 'Chef Specials & Recommended';
        });

        // Restaurant categories for tabs with real dish counts
        $menuCategories = Category::whereHas('products', function ($q) use ($restaurant) {
            $q->where('vendor_id', $restaurant->id)
              ->where('vertical', 'food')
              ->where('status', 'active');
        })
        ->withCount(['products' => function($q) use ($restaurant) {
            $q->where('vendor_id', $restaurant->id)
              ->where('vertical', 'food')
              ->where('status', 'active');
        }])
        ->get();

        // Active restaurant-specific coupons
        $restaurantCoupons = Coupon::where('vendor_id', $restaurant->id)
            ->where('is_active', true)
            ->where('starts_at', '<=', now())
            ->where('expires_at', '>=', now())
            ->get();

        return view('food.restaurant', compact(
            'restaurant',
            'groupedMenu',
            'menuCategories',
            'selectedCategory',
            'vegOnly',
            'menuSearch',
            'restaurantCoupons'
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

        $restaurants = Vendor::where('status', 'approved')
            ->where(function($query) {
                $query->where('vendor_type', 'food')
                      ->orWhereHas('settings', fn($sq) => $sq->where('is_restaurant', true));
            })
            ->where('shop_name', 'like', "%{$q}%")
            ->take(4)
            ->get(['id', 'shop_name', 'slug', 'city', 'logo']);

        $dishes = Product::where('vertical', 'food')
            ->where('status', 'active')
            ->where('title', 'like', "%{$q}%")
            ->with(['vendor:id,shop_name,slug', 'images'])
            ->take(6)
            ->get(['id', 'vendor_id', 'title', 'slug', 'base_price', 'custom_fields']);

        return response()->json([
            'restaurants' => $restaurants->map(fn($r) => [
                'name' => $r->shop_name,
                'url' => route('food.restaurant', $r->slug),
                'city' => $r->city,
                'image' => $r->logo_url,
            ]),
            'dishes' => $dishes->map(fn($d) => [
                'title' => $d->title,
                'restaurant' => $d->vendor?->shop_name,
                'restaurant_url' => $d->vendor ? route('food.restaurant', $d->vendor->slug) : '#',
                'price' => number_format($d->base_price / 100, 0),
                'dietary' => $d->getCustomField('dietary_type', 'Veg'),
                'image' => $d->images->first()?->path ? (str_starts_with($d->images->first()->path, 'http') ? $d->images->first()->path : asset('storage/' . $d->images->first()->path)) : null,
            ]),
        ]);
    }

    /**
     * 1-Click Reorder previous food order.
     */
    public function reorder(Order $order): RedirectResponse
    {
        if (!Auth::check() || $order->user_id !== Auth::id()) {
            abort(403);
        }

        $cart = Cart::firstOrCreate(['user_id' => Auth::id()]);

        foreach ($order->items as $item) {
            $product = $item->product;
            if ($product && $product->status === 'active') {
                $cart->items()->create([
                    'product_id' => $product->id,
                    'variant_id' => $item->variant_id,
                    'vendor_id' => $order->vendor_id,
                    'quantity' => $item->quantity,
                    'price_at_add' => $item->unit_price,
                    'selected_modifiers_json' => $item->selected_modifiers_json,
                ]);
            }
        }

        return redirect()->route('checkout.vendor', $order->vendor_id)->with('success', 'Dishes from your past order have been added to your bag!');
    }
}
