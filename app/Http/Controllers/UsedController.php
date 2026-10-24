<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class UsedController extends Controller
{
    /**
     * Display the Used Items marketplace hub (used.k71.in / used.marblemandi.org).
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
        $openAdSlug = $request->query('ad');

        // Fetch active used categories
        $usedCategories = Category::where('vertical', 'used')
            ->where('is_active', true)
            ->withCount(['products' => function ($q) {
                $q->where('vertical', 'used')->where('status', 'active');
            }])
            ->orderBy('sort_order')
            ->get();

        // Standard Indian classified cities
        $defaultCities = collect([
            'Kishangarh, Rajasthan',
            'Ajmer, Rajasthan',
            'Jaipur, Rajasthan',
            'Jodhpur, Rajasthan',
            'Kota, Rajasthan',
            'Udaipur, Rajasthan',
            'Delhi NCR',
            'Mumbai, Maharashtra',
            'Ahmedabad, Gujarat',
            'Bengaluru, Karnataka',
        ]);

        $dbCities = Vendor::whereHas('products', fn($q) => $q->where('vertical', 'used')->where('status', 'active'))
            ->whereNotNull('city')
            ->pluck('city')
            ->map(fn($c) => str_contains($c, ',') ? $c : "{$c}, Rajasthan")
            ->unique();

        $availableCities = $defaultCities->merge($dbCities)->unique()->values();

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

        if ($selectedCategory && $selectedCategory !== 'all') {
            $query->where(function ($q) use ($selectedCategory) {
                $q->whereHas('category', fn($cq) => $cq->where('slug', 'like', "%{$selectedCategory}%"))
                  ->orWhere('title', 'like', "%{$selectedCategory}%")
                  ->orWhere('brand', 'like', "%{$selectedCategory}%");
            });
        }

        if ($condition) {
            $query->where(function ($q) use ($condition) {
                $q->where('custom_fields->specs->Condition', 'like', "%{$condition}%")
                  ->orWhere('custom_fields->condition', 'like', "%{$condition}%");
            });
        }

        if ($city && $city !== 'All India') {
            $cityClean = explode(',', $city)[0];
            $query->where(function ($q) use ($cityClean) {
                $q->whereHas('vendor', fn($vq) => $vq->where('city', 'like', "%{$cityClean}%"))
                  ->orWhere('custom_fields->location', 'like', "%{$cityClean}%");
            });
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

        $items = $query->latest()->paginate(24)->withQueryString();

        // Recommended / Featured AI Picks (Top 6)
        $recommendedItems = Product::with(['images', 'vendor'])
            ->where('vertical', 'used')
            ->where('status', 'active')
            ->where('is_featured', true)
            ->latest()
            ->take(6)
            ->get();

        if ($recommendedItems->isEmpty()) {
            $recommendedItems = $items->take(6);
        }

        return view('used.index', compact(
            'usedCategories',
            'availableCities',
            'items',
            'recommendedItems',
            'search',
            'selectedCategory',
            'condition',
            'city',
            'minPrice',
            'maxPrice',
            'negotiableOnly',
            'showSold',
            'openAdSlug'
        ));
    }

    /**
     * Show single used product ad directly.
     */
    public function show(string $slug): View
    {
        $product = Product::with(['images', 'category', 'vendor'])
            ->where('vertical', 'used')
            ->where('slug', $slug)
            ->firstOrFail();

        $similarItems = Product::with(['images', 'vendor'])
            ->where('vertical', 'used')
            ->where('status', 'active')
            ->where('id', '!=', $product->id)
            ->latest()
            ->take(4)
            ->get();

        return view('used.show', compact('product', 'similarItems'));
    }

    /**
     * Store new Classified Ad from the frontend Sell Flow.
     */
    public function storeAd(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:150',
            'category' => 'required|string',
            'price' => 'required|numeric|min:1',
            'brand' => 'nullable|string|max:80',
            'city' => 'required|string|max:100',
            'phone' => 'required|string|max:20',
            'description' => 'required|string|max:3000',
            'condition' => 'nullable|string|max:50',
            'image_url' => 'nullable|url',
        ]);

        // Find or create category
        $category = Category::where('vertical', 'used')
            ->where('name', 'like', "%{$validated['category']}%")
            ->first() ?: Category::where('vertical', 'used')->first();

        // Default or active vendor
        $vendor = Vendor::where('vendor_type', 'used')->first() ?: Vendor::first();

        $slug = Str::slug($validated['title']) . '-' . random_int(1000, 9999);
        $adId = (string) random_int(1857000000, 1857999999);

        $product = Product::create([
            'vendor_id' => $vendor->id,
            'category_id' => $category?->id ?? 16,
            'vertical' => 'used',
            'listing_type' => 'classified',
            'title' => $validated['title'],
            'slug' => $slug,
            'description' => $validated['description'],
            'short_description' => Str::limit($validated['description'], 120),
            'brand' => $validated['brand'] ?? 'Unbranded',
            'base_price' => (int) ($validated['price'] * 100),
            'compare_at_price' => (int) ($validated['price'] * 1.25 * 100),
            'gst_rate' => 0,
            'sku' => 'AD-' . strtoupper(Str::random(6)),
            'status' => 'active',
            'is_featured' => true,
            'is_negotiable' => true,
            'is_sold' => false,
            'total_stock' => 1,
            'published_at' => now(),
            'custom_fields' => [
                'ad_id' => $adId,
                'badge' => 'FEATURED',
                'deal_label' => 'Fair Deal',
                'location' => $validated['city'],
                'contact_phone' => $validated['phone'],
                'views_past_hour' => 1,
                'specs' => [
                    'Type' => $validated['category'],
                    'Brand' => $validated['brand'] ?? 'Standard',
                    'Condition' => $validated['condition'] ?? 'Good Condition',
                    'Posted Location' => $validated['city'],
                ],
            ],
        ]);

        $imgUrl = $validated['image_url'] ?: 'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?auto=format&fit=crop&w=800&q=80';
        ProductImage::create([
            'product_id' => $product->id,
            'path' => $imgUrl,
            'alt_text' => $product->title,
            'sort_order' => 0,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Your Ad has been published successfully and is now LIVE!',
                'slug' => $product->slug,
                'product' => $product,
            ]);
        }

        return redirect()->route('used.index', ['ad' => $product->slug])
            ->with('success', 'Your Ad is LIVE across Kishangarh & India!');
    }
}
