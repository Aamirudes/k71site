<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Vendor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ServicesController extends Controller
{
    /**
     * Display the K71 Freelance & Professional Services Marketplace Hub.
     */
    public function index(Request $request): View
    {
        $search = $request->query('search');
        $selectedCategory = $request->query('cat');
        $city = $request->query('city');
        $minPrice = $request->query('min_price');
        $maxPrice = $request->query('max_price');
        $deliveryDays = $request->query('delivery');
        $sort = $request->query('sort', 'newest');

        // Fetch active service categories with real service count
        $servicesCategories = Category::where('vertical', 'services')
            ->where('is_active', true)
            ->withCount(['products' => function ($q) {
                $q->where('vertical', 'services')->where('status', 'active');
            }])
            ->orderBy('sort_order')
            ->get();

        // Main Services Query
        $query = Product::with(['images', 'category', 'vendor.settings', 'gigPackages'])
            ->where('vertical', 'services')
            ->where('status', 'active');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('brand', 'like', "%{$search}%")
                  ->orWhereHas('vendor', function ($vq) use ($search) {
                      $vq->where('shop_name', 'like', "%{$search}%")
                         ->orWhere('tagline', 'like', "%{$search}%")
                         ->orWhere('city', 'like', "%{$search}%");
                  });
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

        if ($deliveryDays && is_numeric($deliveryDays)) {
            $query->whereHas('gigPackages', function ($gq) use ($deliveryDays) {
                $gq->where('delivery_days', '<=', (int) $deliveryDays);
            });
        }

        // Sorting
        switch ($sort) {
            case 'price_asc':
                $query->orderBy('base_price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('base_price', 'desc');
                break;
            case 'rating':
                $query->orderByDesc('sales_count');
                break;
            case 'newest':
            default:
                $query->latest();
                break;
        }

        $services = $query->paginate(12)->withQueryString();

        // Featured Packages (Gigs with 3-tier packages)
        $featuredPackages = Product::with(['images', 'category', 'vendor', 'gigPackages'])
            ->where('vertical', 'services')
            ->where('status', 'active')
            ->has('gigPackages')
            ->take(6)
            ->get();

        // Recommended Professionals / Freelancers
        $topProviders = Vendor::where('vendor_type', 'service')
            ->orWhereHas('products', fn($q) => $q->where('vertical', 'services')->where('status', 'active'))
            ->where('status', 'approved')
            ->withCount(['products' => fn($q) => $q->where('vertical', 'services')->where('status', 'active')])
            ->orderByDesc('rating')
            ->take(8)
            ->get();

        // Budget Friendly (< ₹1,500)
        $budgetFriendly = Product::with(['images', 'vendor', 'category'])
            ->where('vertical', 'services')
            ->where('status', 'active')
            ->where('base_price', '<=', 150000)
            ->take(4)
            ->get();

        // Premium Pro Services (> ₹5,000)
        $premiumServices = Product::with(['images', 'vendor', 'category', 'gigPackages'])
            ->where('vertical', 'services')
            ->where('status', 'active')
            ->where('base_price', '>=', 500000)
            ->take(4)
            ->get();

        return view('services.index', compact(
            'servicesCategories',
            'services',
            'featuredPackages',
            'topProviders',
            'budgetFriendly',
            'premiumServices',
            'search',
            'selectedCategory',
            'city',
            'minPrice',
            'maxPrice',
            'deliveryDays',
            'sort'
        ));
    }

    /**
     * Category filtered view.
     */
    public function category(string $slug, Request $request): View
    {
        $category = Category::where('slug', $slug)
            ->where('vertical', 'services')
            ->firstOrFail();

        $request->merge(['cat' => $slug]);

        return $this->index($request);
    }

    /**
     * Display a single Freelance Service / Gig page (Fiverr/Upwork style).
     */
    public function show(string $slug): View
    {
        $service = Product::where('slug', $slug)
            ->where('vertical', 'services')
            ->with(['images', 'category', 'vendor.settings', 'gigPackages', 'reviews.user'])
            ->firstOrFail();

        // Other services by same provider
        $providerServices = Product::with(['images', 'category'])
            ->where('vendor_id', $service->vendor_id)
            ->where('vertical', 'services')
            ->where('id', '!=', $service->id)
            ->where('status', 'active')
            ->take(4)
            ->get();

        // Similar services in same category
        $similarServices = Product::with(['images', 'vendor', 'category'])
            ->where('category_id', $service->category_id)
            ->where('id', '!=', $service->id)
            ->where('vertical', 'services')
            ->where('status', 'active')
            ->take(4)
            ->get();

        return view('services.show', compact('service', 'providerServices', 'similarServices'));
    }

    /**
     * Display a dedicated Freelancer / Service Provider profile page.
     */
    public function provider(string $slug): View
    {
        $vendor = Vendor::where('slug', $slug)
            ->with(['settings'])
            ->firstOrFail();

        $services = Product::with(['images', 'category', 'gigPackages'])
            ->where('vendor_id', $vendor->id)
            ->where('vertical', 'services')
            ->where('status', 'active')
            ->latest()
            ->get();

        return view('services.provider', compact('vendor', 'services'));
    }

    /**
     * Become a Seller guided onboarding page.
     */
    public function becomeSeller(): View
    {
        $categories = Category::where('vertical', 'services')
            ->where('is_active', true)
            ->get();

        $existingVendor = auth()->user()?->vendor;

        return view('services.become-seller', compact('categories', 'existingVendor'));
    }

    /**
     * Store Become a Seller application.
     */
    public function storeSeller(Request $request): RedirectResponse
    {
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Please log in or register before onboarding as a service provider.');
        }

        $user = auth()->user();

        $validated = $request->validate([
            'shop_name' => 'required|string|max:100',
            'tagline' => 'required|string|max:150',
            'description' => 'required|string|max:2000',
            'skills' => 'required|string|max:300',
            'languages' => 'nullable|string|max:200',
            'hourly_rate' => 'nullable|numeric|min:0',
            'city' => 'nullable|string|max:100',
            'contact_phone' => 'nullable|string|max:20',
        ]);

        $skillsArray = array_values(array_filter(array_map('trim', explode(',', $validated['skills']))));
        $languagesArray = !empty($validated['languages']) 
            ? array_values(array_filter(array_map('trim', explode(',', $validated['languages']))))
            : ['English', 'Hindi'];

        $vendor = $user->vendor;

        if (!$vendor) {
            $baseSlug = Str::slug($validated['shop_name']);
            $slug = $baseSlug;
            $counter = 1;
            while (Vendor::where('slug', $slug)->exists()) {
                $slug = $baseSlug . '-' . $counter++;
            }

            $vendor = Vendor::create([
                'user_id' => $user->id,
                'shop_name' => $validated['shop_name'],
                'slug' => $slug,
                'vendor_type' => 'service',
                'description' => $validated['description'],
                'tagline' => $validated['tagline'],
                'skills_json' => $skillsArray,
                'languages_json' => $languagesArray,
                'hourly_rate' => $validated['hourly_rate'] ?? null,
                'city' => $validated['city'] ?? 'India',
                'contact_phone' => $validated['contact_phone'] ?? $user->phone,
                'email' => $user->email,
                'status' => 'approved', // Instant onboarding for marketplace agility
                'approved_at' => now(),
                'rating' => 5.0,
                'completed_projects' => 0,
            ]);

            // Update user role to vendor
            $user->update(['role' => 'vendor']);
        } else {
            $vendor->update([
                'shop_name' => $validated['shop_name'],
                'tagline' => $validated['tagline'],
                'description' => $validated['description'],
                'skills_json' => $skillsArray,
                'languages_json' => $languagesArray,
                'hourly_rate' => $validated['hourly_rate'] ?? $vendor->hourly_rate,
                'city' => $validated['city'] ?? $vendor->city,
                'vendor_type' => 'service',
            ]);
        }

        return redirect()->route('vendor.services.index')
            ->with('success', 'Congratulations! Your freelancer profile is live. Create your first service gig below!');
    }
}
