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
        $sort = $request->query('sort', 'newest');

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
                $q->whereHas('category', fn($cq) => $cq->where('slug', 'like', "%{$selectedCategory}%")->orWhere('name', 'like', "%{$selectedCategory}%"))
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

        // Sorting
        if ($sort === 'price_low') {
            $query->orderBy('base_price', 'asc');
        } elseif ($sort === 'price_high') {
            $query->orderBy('base_price', 'desc');
        } else {
            $query->latest();
        }

        $items = $query->paginate(24)->withQueryString();

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

        // User's own posted ads
        $myAds = collect();
        if (auth()->check()) {
            $user = auth()->user();
            $vendorId = $user->vendor?->id;
            $myAds = Product::with(['images', 'category'])
                ->where('vertical', 'used')
                ->where(function ($q) use ($user, $vendorId) {
                    if ($vendorId) {
                        $q->where('vendor_id', $vendorId);
                    }
                    $q->orWhere('custom_fields->user_id', $user->id);
                })
                ->latest()
                ->get();
        }

        return view('used.index', compact(
            'usedCategories',
            'availableCities',
            'items',
            'recommendedItems',
            'myAds',
            'search',
            'selectedCategory',
            'condition',
            'city',
            'minPrice',
            'maxPrice',
            'negotiableOnly',
            'showSold',
            'openAdSlug',
            'sort'
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
            ->where('category_id', $product->category_id)
            ->latest()
            ->take(4)
            ->get();

        if ($similarItems->isEmpty()) {
            $similarItems = Product::with(['images', 'vendor'])
                ->where('vertical', 'used')
                ->where('status', 'active')
                ->where('id', '!=', $product->id)
                ->latest()
                ->take(4)
                ->get();
        }

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
            'photos' => 'nullable|array|max:10',
            'photos.*' => 'nullable|image|max:12288',
            'image' => 'nullable|image|max:12288',
            'image_url' => 'nullable|string',
        ]);

        // Find or create category
        $category = Category::where('vertical', 'used')
            ->where(function($q) use ($validated) {
                $q->where('name', 'like', "%{$validated['category']}%")
                  ->orWhere('slug', 'like', "%" . Str::slug($validated['category']) . "%");
            })
            ->first() ?: Category::where('vertical', 'used')->first();

        // Default or active vendor
        $vendor = auth()->user()?->vendor ?: (Vendor::where('vendor_type', 'used')->first() ?: Vendor::first());
        $userId = auth()->id();

        $slug = Str::slug($validated['title']) . '-' . random_int(1000, 9999);
        $adId = (string) random_int(1857000000, 1857999999);
        $cond = $validated['condition'] ?? 'Good Condition';

        $product = Product::create([
            'vendor_id' => $vendor->id,
            'category_id' => $category?->id ?? 16,
            'vertical' => 'used',
            'listing_type' => 'classified',
            'title' => $validated['title'],
            'slug' => $slug,
            'description' => $validated['description'],
            'short_description' => Str::limit($validated['description'], 120),
            'brand' => $validated['brand'] ?? 'Standard',
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
                'user_id' => $userId,
                'badge' => 'VERIFIED',
                'deal_label' => 'Fair Deal',
                'location' => $validated['city'],
                'contact_phone' => $validated['phone'],
                'condition' => $cond,
                'views_past_hour' => 1,
                'specs' => [
                    'Type' => $validated['category'],
                    'Brand' => $validated['brand'] ?? 'Standard',
                    'Condition' => $cond,
                    'Posted Location' => $validated['city'],
                ],
            ],
        ]);

        // Multi-photo file upload handling
        $savedImages = [];
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $idx => $photoFile) {
                if ($photoFile && $photoFile->isValid()) {
                    $storedPath = $photoFile->store('products/used', 'public');
                    $savedImages[] = asset('storage/' . $storedPath);
                }
            }
        } elseif ($request->hasFile('image') && $request->file('image')->isValid()) {
            $storedPath = $request->file('image')->store('products/used', 'public');
            $savedImages[] = asset('storage/' . $storedPath);
        } elseif (!empty($validated['image_url'])) {
            $savedImages[] = $validated['image_url'];
        }

        // Fallback default image per category if no photo was uploaded
        if (empty($savedImages)) {
            $catLower = strtolower($validated['category'] . ' ' . $validated['title']);
            if (str_contains($catLower, 'phone') || str_contains($catLower, 'mobile')) {
                $savedImages[] = 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?auto=format&fit=crop&w=800&q=80';
            } elseif (str_contains($catLower, 'car') || str_contains($catLower, 'vehicle')) {
                $savedImages[] = 'https://images.unsplash.com/photo-1549399542-7e3f8b79c341?auto=format&fit=crop&w=800&q=80';
            } elseif (str_contains($catLower, 'bike') || str_contains($catLower, 'scooter') || str_contains($catLower, 'bullet')) {
                $savedImages[] = 'https://images.unsplash.com/photo-1558981403-c5f9899a28bc?auto=format&fit=crop&w=800&q=80';
            } elseif (str_contains($catLower, 'laptop') || str_contains($catLower, 'electronic')) {
                $savedImages[] = 'https://images.unsplash.com/photo-1588872657578-7efd1f1555ed?auto=format&fit=crop&w=800&q=80';
            } else {
                $savedImages[] = 'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?auto=format&fit=crop&w=800&q=80';
            }
        }

        foreach ($savedImages as $sortOrder => $imgUrl) {
            ProductImage::create([
                'product_id' => $product->id,
                'path' => $imgUrl,
                'alt_text' => $product->title,
                'sort_order' => $sortOrder,
            ]);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Your Ad has been published successfully and is now LIVE!',
                'slug' => $product->slug,
                'product' => $product,
            ]);
        }

        return redirect()->route('used.show', $product->slug)
            ->with('success', 'Your Ad is LIVE across Kishangarh & India!');
    }

    /**
     * Toggle Sold / Available status on an ad.
     */
    public function toggleSold(Request $request, Product $product)
    {
        $user = auth()->user();
        if (!$user) abort(401);

        if ($product->vendor?->user_id !== $user->id && ($product->custom_fields['user_id'] ?? null) !== $user->id && !$user->hasRole('admin')) {
            abort(403, 'Unauthorized to modify this listing.');
        }

        $product->is_sold = !$product->is_sold;
        $product->save();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'is_sold' => $product->is_sold]);
        }

        return back()->with('success', $product->is_sold ? 'Ad marked as SOLD.' : 'Ad re-activated as ACTIVE.');
    }

    /**
     * Delete an ad owned by the user.
     */
    public function deleteAd(Request $request, Product $product)
    {
        $user = auth()->user();
        if (!$user) abort(401);

        if ($product->vendor?->user_id !== $user->id && ($product->custom_fields['user_id'] ?? null) !== $user->id && !$user->hasRole('admin')) {
            abort(403, 'Unauthorized to delete this listing.');
        }

        $product->delete();

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('used.index')->with('success', 'Your ad has been removed.');
    }
}
