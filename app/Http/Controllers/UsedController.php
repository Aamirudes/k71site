<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UsedController extends Controller
{
    /**
     * Display the Used Items marketplace hub (used.k71.in).
     */
    public function index(Request $request): View
    {
        $search = $request->query('search');
        $selectedCategory = $request->query('cat');
        $condition = $request->query('condition');
        $city = $request->query('city');
        $minPrice = $request->query('min_price');
        $maxPrice = $request->query('max_price');
        $negotiableOnly = $request->boolean('negotiable');
        $showSold = $request->boolean('include_sold');

        // Fetch active used categories
        $usedCategories = Category::where('vertical', 'used')
            ->where('is_active', true)
            ->withCount(['products' => function ($q) {
                $q->where('vertical', 'used')->where('status', 'active');
            }])
            ->orderBy('sort_order')
            ->get();

        // Distinct cities from vendors having used products
        $availableCities = Vendor::whereHas('products', fn($q) => $q->where('vertical', 'used')->where('status', 'active'))
            ->whereNotNull('city')
            ->pluck('city')
            ->unique()
            ->values();

        // Main Query
        $query = Product::with(['images', 'category', 'vendor'])
            ->where('vertical', 'used')
            ->where('status', 'active');

        if (!$showSold) {
            $query->where('is_sold', false);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('brand', 'like', "%{$search}%")
                  ->orWhereHas('vendor', fn($vq) => $vq->where('shop_name', 'like', "%{$search}%")->orWhere('city', 'like', "%{$search}%"));
            });
        }

        if ($selectedCategory) {
            $query->whereHas('category', fn($q) => $q->where('slug', $selectedCategory));
        }

        if ($condition) {
            $query->where('custom_fields->condition', 'like', "%{$condition}%");
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

        if ($negotiableOnly) {
            $query->where('is_negotiable', true);
        }

        $items = $query->latest()->paginate(16)->withQueryString();

        return view('used.index', compact(
            'usedCategories',
            'availableCities',
            'items',
            'search',
            'selectedCategory',
            'condition',
            'city',
            'minPrice',
            'maxPrice',
            'negotiableOnly',
            'showSold'
        ));
    }
}
