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

        $visibility = app(\App\Services\Monetization\VisibilityService::class);

        $featuredProductsQuery = Product::with(['images', 'vendor', 'category', 'variants'])
            ->where('status', 'active')
            ->where($retailFilter)
            ->where('is_featured', true);
        $visibility->scopeVisibleProducts($featuredProductsQuery, 'products');
        $featuredProducts = $featuredProductsQuery->latest()->take(12)->get();

        $newArrivalsQuery = Product::with(['images', 'vendor', 'category', 'variants'])
            ->where('status', 'active')
            ->where($retailFilter);
        $visibility->scopeVisibleProducts($newArrivalsQuery, 'products');
        $newArrivals = $newArrivalsQuery->latest()->take(24)->get();

        // Dedicated 20 Latest Retail Products for Homepage (strictly filtered to active retail items)
        $latestProductsQuery = Product::with(['images', 'vendor', 'category', 'variants'])
            ->where('status', 'active')
            ->where($retailFilter);
        $visibility->scopeVisibleProducts($latestProductsQuery, 'products');
        $latestProducts = $latestProductsQuery->latest()->take(20)->get();

        // Dedicated Services & Freelancers for Homepage Showcase
        $featuredServicesQuery = Product::with(['images', 'vendor', 'category', 'gigPackages'])
            ->where('vertical', 'services')
            ->where('status', 'active');
        $visibility->scopeVisibleProducts($featuredServicesQuery, 'services');
        $featuredServices = $featuredServicesQuery->latest()->take(4)->get();

        // Dedicated Food & Kitchens for Homepage Showcase
        $featuredFoodQuery = Product::with(['images', 'vendor', 'category'])
            ->where('vertical', 'food')
            ->where('status', 'active');
        $visibility->scopeVisibleProducts($featuredFoodQuery, 'food');
        $featuredFood = $featuredFoodQuery->latest()->take(4)->get();
            
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
            $catCountQuery = Product::whereIn('category_id', $categoryIds)
                ->where('status', 'active')
                ->excludeUsed();
            $visibility->scopeVisibleProducts($catCountQuery);
            $cat->products_count = $catCountQuery->count();

            if (!empty($cat->image)) {
                $cat->sample_image = $cat->image;
            } else {
                // Automatically fetch the latest visible product image from this category
                $sampleQuery = Product::whereIn('category_id', $categoryIds)
                    ->where('status', 'active')
                    ->excludeUsed()
                    ->whereHas('images')
                    ->with(['images' => fn($q) => $q->orderBy('sort_order')])
                    ->latest('updated_at');
                $visibility->scopeVisibleProducts($sampleQuery);
                $sampleProduct = $sampleQuery->first();

                $cat->sample_image = $sampleProduct?->images->first()?->path ?? null;
            }
        }

        $topVendorsQuery = \App\Models\Vendor::where(function($q) {
                $q->whereNull('vendor_type')->orWhere('vendor_type', '!=', 'used');
            });
        $visibility->scopeVisibleVendors($topVendorsQuery);
        $topVendors = $topVendorsQuery
            ->withCount(['products' => function($q) use ($visibility) {
                $q->where('status', 'active')->excludeUsed();
                $visibility->scopeVisibleProducts($q);
            }])
            ->with(['products' => function($q) use ($visibility) {
                $q->where('status', 'active')->excludeUsed()->with('images')->latest();
                $visibility->scopeVisibleProducts($q);
                $q->take(3);
            }])
            ->latest()
            ->take(8)
            ->get();

        // Dedicated Used / Re-Commerce Listings (active public only)
        $featuredUsedQuery = Product::with(['images', 'vendor', 'sellerUser.usedSellerProfile', 'category'])
            ->where(function($q) {
                $q->where('vertical', 'used')
                  ->orWhere('listing_type', 'classified');
            })
            ->where('status', 'active')
            ->where('is_sold', false)
            ->where(function ($q) {
                $q->whereNull('used_status')->orWhere('used_status', 'active');
            });
        $visibility->scopeVisibleProducts($featuredUsedQuery, 'used', true);
        $featuredUsed = $featuredUsedQuery->latest()->take(4)->get();

        // Dedicated B2B & Wholesale Enquiries & Products
        $featuredEnquiries = \App\Models\Enquiry::with(['category', 'vendor', 'quotes'])
            ->whereIn('status', ['open', 'quoted'])
            ->where('created_at', '>=', now()->subDays(90))
            ->latest()
            ->take(4)
            ->get();

        $featuredEnquiryProductsQuery = Product::with(['images', 'vendor', 'category'])
            ->where(function($q) {
                $q->where('vertical', 'enquiry')
                  ->orWhere('listing_type', 'enquiry')
                  ->orWhere('listing_type', 'quote_only');
            })
            ->where('status', 'active');
        $visibility->scopeVisibleProducts($featuredEnquiryProductsQuery, 'enquiry');
        $featuredEnquiryProducts = $featuredEnquiryProductsQuery->latest()->take(4)->get();

        $foodRestaurantsQuery = \App\Models\Vendor::where(function ($q) {
                $q->where('vendor_type', 'food')
                  ->orWhereHas('settings', fn ($sq) => $sq->where('is_restaurant', true));
            })
            ->with('settings');
        $visibility->scopeVisibleVendors($foodRestaurantsQuery, 'food');
        $foodRestaurants = $foodRestaurantsQuery->latest()->take(4)->get();

        $homepageSettings = \App\Models\Setting::getGroup('homepage');
        $scrollSequence = \App\Services\ScrollSequenceService::resolveSequence(
            $homepageSettings['scroll_seq_folder'] ?? null
        );
        $seoSettings = \App\Models\Setting::getGroup('seo');

        $latestShorts = \App\Models\Short::with(['vendor'])
            ->whereIn('status', ['published', 'approved', 'active'])
            ->whereHas('vendor', fn ($vq) => $visibility->scopeVisibleVendors($vq))
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
