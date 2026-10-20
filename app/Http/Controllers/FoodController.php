<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FoodController extends Controller
{
    /**
     * Display the Food & Restaurants discovery hub (food.k71.in).
     */
    public function index(Request $request): View
    {
        $search = $request->query('search');
        $dietary = $request->query('dietary');
        $cuisine = $request->query('cuisine');
        $pureVegOnly = $request->boolean('pure_veg');

        // Fetch active food categories
        $foodCategories = Category::where('vertical', 'food')
            ->where('is_active', true)
            ->withCount(['products' => function ($q) {
                $q->where('vertical', 'food')->where('status', 'active');
            }])
            ->orderBy('sort_order')
            ->get();

        // Query Restaurants
        $restaurantsQuery = Vendor::where('status', 'approved')
            ->where(function ($q) {
                $q->whereHas('settings', fn($sq) => $sq->where('is_restaurant', true))
                  ->orWhereHas('products', fn($pq) => $pq->where('vertical', 'food')->where('status', 'active'));
            })
            ->with(['settings', 'products' => fn($q) => $q->where('vertical', 'food')->where('status', 'active')->take(4)]);

        if ($pureVegOnly) {
            $restaurantsQuery->whereHas('settings', fn($sq) => $sq->where('is_pure_veg', true));
        }

        if ($search) {
            $restaurantsQuery->where(function ($q) use ($search) {
                $q->where('shop_name', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%")
                  ->orWhereHas('products', fn($pq) => $pq->where('title', 'like', "%{$search}%"));
            });
        }

        $restaurants = $restaurantsQuery->latest()->paginate(12)->withQueryString();

        // Query Trending / Featured Food Dishes
        $dishesQuery = Product::with(['images', 'vendor.settings', 'category'])
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
            $dishesQuery->where('custom_fields->dietary_type', 'like', '%Veg%')
                        ->where('custom_fields->dietary_type', 'not like', '%Non-Veg%');
        }

        $trendingDishes = $dishesQuery->latest()->take(16)->get();

        return view('food.index', compact(
            'foodCategories',
            'restaurants',
            'trendingDishes',
            'search',
            'dietary',
            'cuisine',
            'pureVegOnly'
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

        // Menu items query
        $menuQuery = Product::with(['images', 'category', 'variants'])
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

        $menuItems = $menuQuery->latest()->get();

        // Group menu by category
        $groupedMenu = $menuItems->groupBy(function ($item) {
            return $item->category?->name ?? 'Main Menu';
        });

        // Restaurant categories for tabs
        $menuCategories = Category::whereHas('products', function ($q) use ($restaurant) {
            $q->where('vendor_id', $restaurant->id)
              ->where('vertical', 'food')
              ->where('status', 'active');
        })->get();

        return view('food.restaurant', compact(
            'restaurant',
            'groupedMenu',
            'menuCategories',
            'selectedCategory',
            'vegOnly'
        ));
    }
}
