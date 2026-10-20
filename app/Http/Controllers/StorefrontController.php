<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use App\Models\Product;
use Illuminate\Http\Request;

class StorefrontController extends Controller
{
    public function show(Request $request, $slug)
    {
        $vendor = Vendor::with('settings')->where('slug', $slug)->firstOrFail();
        app()->instance('current_vendor', $vendor);
        
        $query = Product::with(['images', 'category', 'variants'])
            ->where('vendor_id', $vendor->id)
            ->where('status', 'active');

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
        })
        ->withCount(['products' => function($q) use ($vendor) {
            $q->where('vendor_id', $vendor->id)->where('status', 'active');
        }])
        ->get();

        foreach ($vendorCategories as $cat) {
            $sampleProduct = Product::where('vendor_id', $vendor->id)
                ->where('category_id', $cat->id)
                ->where('status', 'active')
                ->with('images')
                ->latest()
                ->first();
            $cat->sample_image = $sampleProduct?->images->first()?->path ?? null;
        }

        // Featured products for boutique spotlight (up to 6 for horizontal rail)
        $featuredProducts = Product::with(['images', 'variants'])
            ->where('vendor_id', $vendor->id)
            ->where('status', 'active')
            ->where('is_featured', true)
            ->take(6)
            ->get();

        // If no featured products explicitly flagged, use newest products for Spotlight
        if ($featuredProducts->isEmpty()) {
            $featuredProducts = Product::with(['images', 'variants'])
                ->where('vendor_id', $vendor->id)
                ->where('status', 'active')
                ->latest()
                ->take(6)
                ->get();
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
