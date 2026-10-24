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

        $query = Vendor::where('status', 'approved')
            ->withCount(['products' => function($q) {
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

        $totalStoresCount = Vendor::where('status', 'approved')->count();

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
            return redirect()->route('filament.admin.auth.login');
        }

        $vendor = Vendor::with('settings')->where('slug', $slug)->firstOrFail();
        app()->instance('current_vendor', $vendor);
        
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

        return view('storefront.show', compact('vendor', 'products', 'vendorCategories', 'featuredProducts', 'vendorShorts'));
    }
}
