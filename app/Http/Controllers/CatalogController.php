<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Vendor;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['images', 'vendor', 'category'])
            ->where('status', 'active');

        $activeFilters = [];
        $appName = config('marketplace.name', 'K71');

        // 1. Category Filter (supports slug or ID, including child categories)
        $currentCategory = null;
        if ($request->filled('category')) {
            $catSlug = $request->input('category');
            $currentCategory = Category::where('slug', $catSlug)->orWhere('id', $catSlug)->first();
            if ($currentCategory) {
                $categoryIds = Category::where('id', $currentCategory->id)
                    ->orWhere('parent_id', $currentCategory->id)
                    ->pluck('id');

                $query->whereIn('category_id', $categoryIds);

                $activeFilters[] = [
                    'key' => 'category',
                    'label' => $currentCategory->name,
                    'remove_url' => $this->buildFilterUrl($request, 'category')
                ];
            }
        }

        // 2. Boutique / Atelier Store Filter
        $currentStore = null;
        if ($request->filled('store') || $request->filled('vendor')) {
            $storeIdentifier = $request->input('store', $request->input('vendor'));
            $currentStore = Vendor::where('slug', $storeIdentifier)->orWhere('id', $storeIdentifier)->first();
            if ($currentStore) {
                $query->where('vendor_id', $currentStore->id);

                $activeFilters[] = [
                    'key' => 'store',
                    'label' => 'Boutique: ' . $currentStore->shop_name,
                    'remove_url' => $this->buildFilterUrl($request, ['store', 'vendor'])
                ];
            }
        }

        // 3. Gender / Silhouette Filter
        if ($request->filled('gender')) {
            $gender = strtolower(trim($request->input('gender')));
            if (in_array($gender, ['men', 'women', 'kids', 'unisex'])) {
                $query->where('gender', $gender);

                $activeFilters[] = [
                    'key' => 'gender',
                    'label' => ucfirst($gender),
                    'remove_url' => $this->buildFilterUrl($request, 'gender')
                ];
            }
        }

        // 4. Quick Price Range Presets
        if ($request->filled('price_range')) {
            $pRange = $request->input('price_range');
            switch ($pRange) {
                case 'under_999':
                    $query->where('base_price', '<', 100000);
                    $activeFilters[] = ['key' => 'price_range', 'label' => 'Under ₹999', 'remove_url' => $this->buildFilterUrl($request, 'price_range')];
                    break;
                case '1000_1999':
                    $query->whereBetween('base_price', [100000, 199900]);
                    $activeFilters[] = ['key' => 'price_range', 'label' => '₹1,000 - ₹1,999', 'remove_url' => $this->buildFilterUrl($request, 'price_range')];
                    break;
                case '2000_4999':
                    $query->whereBetween('base_price', [200000, 499900]);
                    $activeFilters[] = ['key' => 'price_range', 'label' => '₹2,000 - ₹4,999', 'remove_url' => $this->buildFilterUrl($request, 'price_range')];
                    break;
                case '5000_above':
                    $query->where('base_price', '>=', 500000);
                    $activeFilters[] = ['key' => 'price_range', 'label' => '₹5,000 & Above', 'remove_url' => $this->buildFilterUrl($request, 'price_range')];
                    break;
            }
        }

        // 5. Custom Price Range (Min / Max)
        $minPriceVal = $request->input('min_price', $request->input('price_min'));
        if ($minPriceVal !== null && $minPriceVal !== '') {
            $minPrice = max(0, (float) $minPriceVal);
            $query->where('base_price', '>=', $minPrice * 100);
            if (!$request->filled('price_range')) {
                $activeFilters[] = [
                    'key' => 'min_price',
                    'label' => 'Min ₹' . number_format($minPrice),
                    'remove_url' => $this->buildFilterUrl($request, ['min_price', 'price_min'])
                ];
            }
        }

        $maxPriceVal = $request->input('max_price', $request->input('price_max'));
        if ($maxPriceVal !== null && $maxPriceVal !== '') {
            $maxPrice = max(0, (float) $maxPriceVal);
            $query->where('base_price', '<=', $maxPrice * 100);
            if (!$request->filled('price_range')) {
                $activeFilters[] = [
                    'key' => 'max_price',
                    'label' => 'Max ₹' . number_format($maxPrice),
                    'remove_url' => $this->buildFilterUrl($request, ['max_price', 'price_max'])
                ];
            }
        }

        // 6. Minimum Discount % Filter (Amazon/Flipkart style)
        if ($request->filled('discount')) {
            $minDiscount = (int) $request->input('discount');
            if ($minDiscount > 0) {
                $query->whereNotNull('compare_at_price')
                      ->whereRaw('compare_at_price > base_price')
                      ->whereRaw('((compare_at_price - base_price) * 100 / compare_at_price) >= ?', [$minDiscount]);

                $activeFilters[] = [
                    'key' => 'discount',
                    'label' => $minDiscount . '% Off or More',
                    'remove_url' => $this->buildFilterUrl($request, 'discount')
                ];
            }
        }

        // 7. In-Stock Only Filter
        if ($request->boolean('in_stock')) {
            $query->where('total_stock', '>', 0);
            $activeFilters[] = [
                'key' => 'in_stock',
                'label' => 'In Stock Only',
                'remove_url' => $this->buildFilterUrl($request, 'in_stock')
            ];
        }

        // 8. Minimum Customer Rating Filter
        if ($request->filled('rating')) {
            $minRating = (float) $request->input('rating');
            if ($minRating > 0) {
                $query->where('avg_rating', '>=', $minRating);
                $activeFilters[] = [
                    'key' => 'rating',
                    'label' => $minRating . '★ & Above',
                    'remove_url' => $this->buildFilterUrl($request, 'rating')
                ];
            }
        }

        // 9. Featured / Curated Artisanal Only
        if ($request->boolean('featured')) {
            $query->where('is_featured', true);
            $activeFilters[] = [
                'key' => 'featured',
                'label' => 'Artisanal Edit',
                'remove_url' => $this->buildFilterUrl($request, 'featured')
            ];
        }

        // 10. Powerful Search Filter across titles, categories, vendors, and tags
        $searchQuery = $request->input('search', $request->input('q'));
        if (!empty($searchQuery)) {
            $searchTerm = trim($searchQuery);
            $wildcard = '%' . $searchTerm . '%';
            $query->where(function($q) use ($wildcard) {
                $q->where('title', 'LIKE', $wildcard)
                  ->orWhere('description', 'LIKE', $wildcard)
                  ->orWhere('short_description', 'LIKE', $wildcard)
                  ->orWhere('material', 'LIKE', $wildcard)
                  ->orWhere('brand', 'LIKE', $wildcard)
                  ->orWhereHas('vendor', function($vq) use ($wildcard) {
                      $vq->where('shop_name', 'LIKE', $wildcard);
                  })
                  ->orWhereHas('category', function($cq) use ($wildcard) {
                      $cq->where('name', 'LIKE', $wildcard);
                  });
            });

            $activeFilters[] = [
                'key' => 'search',
                'label' => '"' . $searchTerm . '"',
                'remove_url' => $this->buildFilterUrl($request, ['search', 'q'])
            ];
        }

        // 11. Cross-Vertical Filter (Phase 11)
        if ($request->filled('vertical') && $request->input('vertical') !== 'all') {
            $vert = strtolower(trim($request->input('vertical')));
            $query->where('vertical', $vert);
            $activeFilters[] = [
                'key' => 'vertical',
                'label' => 'Vertical: ' . ucfirst($vert),
                'remove_url' => $this->buildFilterUrl($request, 'vertical')
            ];
        }

        // 12. Listing Type Filter (Phase 11)
        if ($request->filled('listing_type')) {
            $lType = strtolower(trim($request->input('listing_type')));
            $query->where('listing_type', $lType);
            $activeFilters[] = [
                'key' => 'listing_type',
                'label' => 'Type: ' . ucfirst($lType),
                'remove_url' => $this->buildFilterUrl($request, 'listing_type')
            ];
        }

        // 13. City / Location Filter (Phase 11)
        if ($request->filled('city')) {
            $city = trim($request->input('city'));
            $query->whereHas('vendor', function($vq) use ($city) {
                $vq->where('city', 'LIKE', '%' . $city . '%');
            });
            $activeFilters[] = [
                'key' => 'city',
                'label' => 'City: ' . $city,
                'remove_url' => $this->buildFilterUrl($request, 'city')
            ];
        }

        // 14. Multi-Dimensional Sorting with friendly aliases
        $sort = $request->input('sort', 'recommended');
        if ($sort === 'price_asc') {
            $sort = 'price_low';
        } elseif ($sort === 'price_desc') {
            $sort = 'price_high';
        } elseif ($sort === 'latest') {
            $sort = 'newest';
        }

        switch ($sort) {
            case 'price_low':
                $query->orderBy('base_price', 'asc');
                break;
            case 'price_high':
                $query->orderBy('base_price', 'desc');
                break;
            case 'newest':
                $query->orderBy('published_at', 'desc')->orderBy('id', 'desc');
                break;
            case 'discount':
                $query->orderByRaw('(CASE WHEN compare_at_price > base_price THEN ((compare_at_price - base_price) * 100 / compare_at_price) ELSE 0 END) DESC');
                break;
            case 'rating':
                $query->orderBy('avg_rating', 'desc')->orderBy('review_count', 'desc');
                break;
            case 'popular':
                $query->orderBy('order_count', 'desc')->orderBy('view_count', 'desc');
                break;
            case 'recommended':
            default:
                $query->orderBy('is_featured', 'desc')
                      ->orderBy('order_count', 'desc')
                      ->orderBy('published_at', 'desc');
                break;
        }

        // Pagination with query string preservation
        $perPage = (int) $request->input('per_page', 24);
        $perPage = in_array($perPage, [12, 24, 48]) ? $perPage : 24;
        $products = $query->paginate($perPage)->withQueryString();

        // Available Categories Hierarchy with Live Product Counts
        $categories = Category::whereNull('parent_id')
            ->where('is_active', true)
            ->with(['children' => function($q) {
                $q->where('is_active', true)
                  ->withCount(['products' => function($pq) {
                      $pq->where('status', 'active');
                  }]);
            }])
            ->withCount(['products' => function($q) {
                $q->where('status', 'active');
            }])
            ->orderBy('sort_order')
            ->get();

        // Available Verified Boutiques / Ateliers with Live Product Counts
        $vendors = Vendor::whereIn('status', ['approved', 'active'])
            ->withCount(['products' => function($q) {
                $q->where('status', 'active');
            }])
            ->orderBy('shop_name')
            ->get();

        // Category Hierarchy (Parent, Subcategories, Siblings)
        $parentCategory = null;
        $subCategories = collect();
        $siblingCategories = collect();

        if ($currentCategory) {
            $currentCategory->load(['parent', 'children']);
            if ($currentCategory->parent_id) {
                $parentCategory = $currentCategory->parent;
                $siblingCategories = Category::where('parent_id', $currentCategory->parent_id)
                    ->where('is_active', true)
                    ->orderBy('sort_order')
                    ->get();
            } else {
                $subCategories = $currentCategory->children()
                    ->where('is_active', true)
                    ->orderBy('sort_order')
                    ->get();
            }
        }

        // Recommended / Trending Masterpieces Carousel
        $recommendedProducts = Product::with(['images', 'vendor'])
            ->where('status', 'active')
            ->where('is_featured', true)
            ->inRandomOrder()
            ->take(8)
            ->get();

        if ($recommendedProducts->count() < 4) {
            $recommendedProducts = Product::with(['images', 'vendor'])
                ->where('status', 'active')
                ->orderBy('published_at', 'desc')
                ->take(8)
                ->get();
        }

        // SEO Title & Description Generation
        if ($currentCategory) {
            $seoTitle = $currentCategory->seo_title ?: "{$currentCategory->name} - Handcrafted & Designer Collection | {$appName}";
            $seoDescription = $currentCategory->seo_description ?: "Explore our curated collection of {$currentCategory->name} directly from independent boutiques and master artisans across India.";
            $canonicalUrl = route('category.show', $currentCategory->slug);
        } elseif ($currentStore) {
            $seoTitle = "{$currentStore->shop_name} Official Collection | {$appName}";
            $seoDescription = "Explore the handcrafted apparel and designer catalog by {$currentStore->shop_name} on {$appName}. Direct atelier shipping across India.";
            $canonicalUrl = route('catalog.index', ['store' => $currentStore->slug]);
        } elseif ($request->filled('gender')) {
            $genderName = ucfirst($request->gender);
            $seoTitle = "{$genderName}'s Fashion & Designer Wear | {$appName}";
            $seoDescription = "Shop modern {$genderName}'s clothing, artisanal ethnic wear, and slow fashion handcrafted by independent ateliers on {$appName}.";
            $canonicalUrl = route('catalog.index', ['gender' => $request->gender]);
        } elseif ($request->filled('search')) {
            $seoTitle = "Search Results for \"" . strip_tags($request->search) . "\" | {$appName}";
            $seoDescription = "Browse search results for {$request->search} on {$appName}.";
            $canonicalUrl = route('catalog.index');
        } else {
            $seoTitle = "Shop All Products - Handcrafted & Designer Fashion | {$appName}";
            $seoDescription = "Discover slow fashion, handcrafted sarees, bespoke kurtas, and luxury linen apparel directly from independent boutiques on {$appName}.";
            $canonicalUrl = route('catalog.index');
        }

        // Schema.org CollectionPage & ItemList JSON-LD
        $schemaItemList = [];
        foreach ($products->take(12) as $idx => $prod) {
            $schemaItemList[] = [
                '@type' => 'ListItem',
                'position' => $idx + 1,
                'url' => route('product.show', $prod->slug),
                'name' => $prod->title,
            ];
        }

        // Breadcrumbs Schema
        $breadcrumbsList = [
            [
                '@type' => 'ListItem',
                'position' => 1,
                'name' => 'Home',
                'item' => url('/'),
            ],
            [
                '@type' => 'ListItem',
                'position' => 2,
                'name' => 'Catalog',
                'item' => route('catalog.index'),
            ],
        ];

        if ($parentCategory) {
            $breadcrumbsList[] = [
                '@type' => 'ListItem',
                'position' => 3,
                'name' => $parentCategory->name,
                'item' => route('category.show', $parentCategory->slug),
            ];
            $breadcrumbsList[] = [
                '@type' => 'ListItem',
                'position' => 4,
                'name' => $currentCategory->name,
                'item' => $canonicalUrl,
            ];
        } elseif ($currentCategory) {
            $breadcrumbsList[] = [
                '@type' => 'ListItem',
                'position' => 3,
                'name' => $currentCategory->name,
                'item' => $canonicalUrl,
            ];
        }

        $catalogSchema = [
            '@context' => 'https://schema.org',
            '@graph' => [
                [
                    '@type' => 'CollectionPage',
                    'name' => $seoTitle,
                    'description' => $seoDescription,
                    'url' => $canonicalUrl,
                    'mainEntity' => [
                        '@type' => 'ItemList',
                        'itemListElement' => $schemaItemList,
                    ],
                ],
                [
                    '@type' => 'BreadcrumbList',
                    'itemListElement' => $breadcrumbsList,
                ],
            ],
        ];

        if ($request->ajax()) {
            $gridHtml = view('catalog.partials.products_grid', compact('products'))->render();
            $chipsHtml = view('catalog.partials.active_chips', compact('activeFilters'))->render();
            $paginationHtml = $products->hasPages() ? $products->links()->render() : '';

            return response()->json([
                'success' => true,
                'total' => $products->total(),
                'total_text' => $products->total() . ' ' . \Illuminate\Support\Str::plural('piece', $products->total()),
                'products_html' => $gridHtml,
                'active_chips_html' => $chipsHtml,
                'pagination_html' => $paginationHtml,
                'seo_title' => $seoTitle,
            ]);
        }

        return view('catalog.index', compact(
            'products', 
            'categories', 
            'vendors',
            'currentCategory',
            'currentStore',
            'parentCategory',
            'subCategories',
            'siblingCategories',
            'activeFilters',
            'recommendedProducts',
            'sort',
            'seoTitle', 
            'seoDescription', 
            'canonicalUrl', 
            'catalogSchema'
        ));
    }

    /**
     * Helper to build filter toggle URLs (removing single or multiple parameters)
     */
    private function buildFilterUrl(Request $request, $keysToRemove): string
    {
        $keys = is_array($keysToRemove) ? $keysToRemove : [$keysToRemove];
        $all = $request->except(array_merge($keys, ['page']));
        return route('catalog.index', $all);
    }

    /**
     * Dedicated clean SEO route for Category Silos (/category/{slug})
     */
    public function category(Request $request, string $slug)
    {
        $request->merge(['category' => $slug]);
        return $this->index($request);
    }

    /**
     * Instant Search Autocomplete / Suggestions
     */
    public function suggest(Request $request)
    {
        $term = trim($request->input('q', $request->input('search', '')));
        if (strlen($term) < 2) {
            return response()->json(['results' => []]);
        }

        $wildcard = '%' . $term . '%';
        $products = Product::where('status', 'active')
            ->where(function($q) use ($wildcard) {
                $q->where('title', 'LIKE', $wildcard)
                  ->orWhere('brand', 'LIKE', $wildcard);
            })
            ->with(['images', 'vendor'])
            ->take(8)
            ->get();

        $results = $products->map(function($p) {
            $img = $p->images->first();
            $thumb = $img ? (str_starts_with($img->path, 'http') ? $img->path : asset('storage/' . $img->path)) : asset('images/product-placeholder.svg');
            return [
                'id' => $p->id,
                'title' => $p->title,
                'slug' => $p->slug,
                'url' => route('product.show', $p->slug),
                'price' => '₹' . number_format($p->base_price / 100, 2),
                'vertical' => $p->vertical ?: 'goods',
                'thumb' => $thumb,
                'vendor' => $p->vendor?->shop_name ?? '',
            ];
        });

        return response()->json(['results' => $results]);
    }
}
