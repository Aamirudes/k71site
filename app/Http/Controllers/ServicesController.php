<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ServicesController extends Controller
{
    /**
     * Display the Services & Booking Marketplace Hub (services.k71.in).
     */
    public function index(Request $request): View
    {
        $search = $request->query('search');
        $selectedCategory = $request->query('cat');
        $city = $request->query('city');
        $minPrice = $request->query('min_price');
        $maxPrice = $request->query('max_price');

        // Fetch active services categories
        $servicesCategories = Category::where('vertical', 'services')
            ->where('is_active', true)
            ->withCount(['products' => function ($q) {
                $q->where('vertical', 'services')->where('status', 'active');
            }])
            ->orderBy('sort_order')
            ->get();

        // Distinct cities from vendors offering services
        $availableCities = Vendor::whereHas('products', fn($q) => $q->where('vertical', 'services')->where('status', 'active'))
            ->whereNotNull('city')
            ->pluck('city')
            ->unique()
            ->values();

        // Main Services Query
        $query = Product::with(['images', 'category', 'vendor.settings'])
            ->where('vertical', 'services')
            ->where('status', 'active');

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

        if ($city) {
            $query->whereHas('vendor', fn($q) => $q->where('city', $city));
        }

        if ($minPrice !== null && is_numeric($minPrice)) {
            $query->where('base_price', '>=', (int) ($minPrice * 100));
        }

        if ($maxPrice !== null && is_numeric($maxPrice)) {
            $query->where('base_price', '<=', (int) ($maxPrice * 100));
        }

        $services = $query->latest()->paginate(16)->withQueryString();

        return view('services.index', compact(
            'servicesCategories',
            'availableCities',
            'services',
            'search',
            'selectedCategory',
            'city',
            'minPrice',
            'maxPrice'
        ));
    }

    /**
     * Display a dedicated Service Provider / Professional profile page.
     */
    public function provider(string $slug): View
    {
        $vendor = Vendor::where('slug', $slug)
            ->with(['settings'])
            ->firstOrFail();

        $services = Product::with(['images', 'category'])
            ->where('vendor_id', $vendor->id)
            ->where('vertical', 'services')
            ->where('status', 'active')
            ->latest()
            ->get();

        return view('services.provider', compact('vendor', 'services'));
    }
}
