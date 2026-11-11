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
            return app(\App\Http\Controllers\StorefrontController::class)->show($request, $vendor->slug);
        }

        // Vertical-aware homepage dispatch: food.k71.in, used.k71.in, services.k71.in, feasto.k71.in
        $verticalKey = $request->attributes->get('k71_vertical_key');
        if ($verticalKey === 'feasto' || $verticalKey === 'feato') {
            return app(\App\Http\Controllers\FeastoController::class)->index($request);
        }
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

        // Dedicated 20 Latest Products for Homepage
        $latestProducts = Product::with(['images', 'vendor', 'category', 'variants'])
            ->where('status', 'active')
            ->where($retailFilter)
            ->latest()
            ->take(20)
            ->get();

        if ($latestProducts->count() < 20) {
            $fallback = Product::with(['images', 'vendor', 'category', 'variants'])
                ->where('status', 'active')
                ->latest()
                ->take(20)
                ->get();
            $latestProducts = $latestProducts->merge($fallback)->unique('id')->take(20);
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
            ->where('show_on_home', true)
            ->where(function($q) {
                $q->whereNull('vertical')->orWhere('vertical', '!=', 'used');
            })
            ->with(['children' => fn($q) => $q->where('is_active', true)])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        foreach ($categories as $cat) {
            $categoryIds = $cat->children->pluck('id')->push($cat->id);
            $cat->products_count = Product::whereIn('category_id', $categoryIds)
                ->where('status', 'active')
                ->excludeUsed()
                ->count();

            if (!empty($cat->image)) {
                $cat->sample_image = $cat->image;
            } else {
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

        // Dedicated Used / Re-Commerce Listings (active public only)
        $featuredUsed = Product::with(['images', 'vendor', 'sellerUser.usedSellerProfile', 'category'])
            ->where(function($q) {
                $q->where('vertical', 'used')
                  ->orWhere('listing_type', 'classified');
            })
            ->where('status', 'active')
            ->where('is_sold', false)
            ->where(function ($q) {
                $q->whereNull('used_status')->orWhere('used_status', 'active');
            })
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
        $scrollSequence = \App\Services\ScrollSequenceService::resolveSequence(
            $homepageSettings['scroll_seq_folder'] ?? null
        );
        $seoSettings = \App\Models\Setting::getGroup('seo');

        $latestShorts = \App\Models\Short::with(['vendor'])
            ->where('status', 'approved')
            ->latest('trending_score')
            ->take(12)
            ->get();

        return view('home', compact(
            'featuredProducts', 
            'newArrivals', 
            'latestProducts',
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
            'scrollSequence',
            'seoSettings'
        ));
    }
}
