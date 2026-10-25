<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $vendor = $request->attributes->get('current_vendor');
        
        if ($vendor) {
            $vendor->load('settings');
            
            $query = Product::with(['images', 'category', 'variants'])
                ->where('vendor_id', $vendor->id)
                ->where('status', 'active');

            if ($vendor->vendor_type !== 'used') {
                $query->excludeUsed();
            }

            if ($request->filled('search')) {
                $search = $request->input('search');
                $query->where(function($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%")
                      ->orWhere('material', 'like', "%{$search}%");
                });
            }

            if ($request->filled('category')) {
                $catSlug = $request->input('category');
                $cat = Category::where('slug', $catSlug)->first();
                if ($cat) {
                    $catIds = Category::where('id', $cat->id)
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

            $sort = $request->input('sort', 'newest');
            if ($sort === 'price_low') {
                $query->orderBy('base_price', 'asc');
            } elseif ($sort === 'price_high') {
                $query->orderBy('base_price', 'desc');
            } else {
                $query->latest();
            }

            $products = $query->paginate(16)->withQueryString();

            $vendorCategories = Category::whereHas('products', function($q) use ($vendor) {
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

            $featuredQuery = Product::with(['images', 'variants'])
                ->where('vendor_id', $vendor->id)
                ->where('status', 'active')
                ->where('is_featured', true);
            if ($vendor->vendor_type !== 'used') {
                $featuredQuery->excludeUsed();
            }
            $featuredProducts = $featuredQuery->take(6)->get();

            if ($featuredProducts->isEmpty()) {
                $latestQuery = Product::with(['images', 'variants'])
                    ->where('vendor_id', $vendor->id)
                    ->where('status', 'active');
                if ($vendor->vendor_type !== 'used') {
                    $latestQuery->excludeUsed();
                }
                $featuredProducts = $latestQuery->latest()->take(6)->get();
            }

            return view('storefront.show', compact('vendor', 'products', 'vendorCategories', 'featuredProducts'));
        }

        // Vertical-aware homepage dispatch: food.k71.in, used.k71.in, services.k71.in
        $verticalKey = $request->attributes->get('k71_vertical_key');
        if ($verticalKey === 'food') {
            return app(FoodController::class)->index($request);
        }
        if ($verticalKey === 'used') {
            return app(UsedController::class)->index($request);
        }
        if ($verticalKey === 'services') {
            return app(ServicesController::class)->index($request);
        }
        if ($verticalKey === 'enquiry') {
            return app(EnquiryController::class)->index($request);
        }
        if ($verticalKey === 'shorts') {
            return app(ShortsController::class)->index($request);
        }

        // For the public frontend, we MUST bypass the VendorScope or disable it for public routes.
        // Actually, VendorScope only applies if the `vendor_context` is set (meaning we are in the vendor panel).
        // Let's ensure the products shown are published and belong to approved vendors, strictly excluding used/classified ads.

        $retailFilter = function($q) {
            $q->where(function($sq) {
                $sq->whereNull('vertical')
                   ->orWhereNotIn('vertical', ['services', 'used', 'food', 'enquiry']);
            })->where(function($sq) {
                $sq->whereNull('listing_type')
                   ->orWhereNotIn('listing_type', ['classified', 'gig', 'service', 'booking']);
            });
        };

        $featuredProducts = Product::with(['images', 'vendor', 'category', 'variants'])
            ->where('status', 'active')
            ->where($retailFilter)
            ->where('is_featured', true)
            ->latest()
            ->take(12)
            ->get();
            
        $newArrivals = Product::with(['images', 'vendor', 'category', 'variants'])
            ->where('status', 'active')
            ->where($retailFilter)
            ->latest()
            ->take(24)
            ->get();

        // If new arrivals is small, include any active non-used retail products
        if ($newArrivals->count() < 8) {
            $newArrivals = Product::with(['images', 'vendor', 'category', 'variants'])
                ->where('status', 'active')
                ->where($retailFilter)
                ->latest()
                ->take(24)
                ->get();
        }

        // Dedicated Services & Freelancers for Homepage Showcase
        $featuredServices = Product::with(['images', 'vendor', 'category', 'gigPackages'])
            ->where('vertical', 'services')
            ->where('status', 'active')
            ->latest()
            ->take(4)
            ->get();

        // Dedicated Food & Kitchens for Homepage Showcase
        $featuredFood = Product::with(['images', 'vendor', 'category'])
            ->where('vertical', 'food')
            ->where('status', 'active')
            ->latest()
            ->take(4)
            ->get();
            
        $categories = Category::whereNull('parent_id')
            ->where('is_active', true)
            ->where(function($q) {
                $q->whereNull('vertical')->orWhere('vertical', '!=', 'used');
            })
            ->with(['children' => fn($q) => $q->where('is_active', true)])
            ->orderBy('sort_order')
            ->get();

        foreach ($categories as $cat) {
            $categoryIds = $cat->children->pluck('id')->push($cat->id);
            $cat->products_count = Product::whereIn('category_id', $categoryIds)
                ->where('status', 'active')
                ->excludeUsed()
                ->count();

            // Automatically fetch the latest real product image from this category
            $sampleProduct = Product::whereIn('category_id', $categoryIds)
                ->where('status', 'active')
                ->excludeUsed()
                ->whereHas('images')
                ->with(['images' => fn($q) => $q->orderBy('sort_order')])
                ->latest('updated_at')
                ->first();

            $cat->sample_image = $sampleProduct?->images->first()?->path ?? null;
        }

        $topVendors = \App\Models\Vendor::where('status', 'approved')
            ->where(function($q) {
                $q->whereNull('vendor_type')->orWhere('vendor_type', '!=', 'used');
            })
            ->withCount(['products' => function($q) {
                $q->where('status', 'active')->excludeUsed();
            }])
            ->with(['products' => function($q) {
                $q->where('status', 'active')->excludeUsed()->with('images')->latest()->take(3);
            }])
            ->latest()
            ->take(8)
            ->get();

        // Dedicated Used / Re-Commerce Listings
        $featuredUsed = Product::with(['images', 'vendor', 'category'])
            ->where(function($q) {
                $q->where('vertical', 'used')
                  ->orWhere('listing_type', 'classified');
            })
            ->where('status', 'active')
            ->latest()
            ->take(4)
            ->get();

        // Dedicated B2B & Wholesale Enquiries & Products
        $featuredEnquiries = \App\Models\Enquiry::with(['category', 'vendor', 'quotes'])
            ->whereIn('status', ['open', 'quoted'])
            ->latest()
            ->take(4)
            ->get();

        $featuredEnquiryProducts = Product::with(['images', 'vendor', 'category'])
            ->where(function($q) {
                $q->where('vertical', 'enquiry')
                  ->orWhere('listing_type', 'enquiry')
                  ->orWhere('listing_type', 'quote_only');
            })
            ->where('status', 'active')
            ->latest()
            ->take(4)
            ->get();

        if ($featuredEnquiryProducts->isEmpty()) {
            $featuredEnquiryProducts = Product::with(['images', 'vendor', 'category'])
                ->where('status', 'active')
                ->where('base_price', '>=', 50000)
                ->take(4)
                ->get();
        }

        $foodRestaurants = \App\Models\Vendor::where('status', 'approved')
            ->where('vendor_type', 'food')
            ->take(4)
            ->get();

        $homepageSettings = \App\Models\Setting::getGroup('homepage');
        $seoSettings = \App\Models\Setting::getGroup('seo');

        $latestShorts = \App\Models\Short::with(['vendor'])
            ->where('status', 'approved')
            ->latest('trending_score')
            ->take(8)
            ->get();

        return view('home', compact(
            'featuredProducts', 
            'newArrivals', 
            'featuredServices',
            'featuredFood',
            'foodRestaurants',
            'featuredUsed',
            'featuredEnquiries',
            'featuredEnquiryProducts',
            'categories', 
            'topVendors',
            'latestShorts',
            'homepageSettings',
            'seoSettings'
        ));
    }
}
