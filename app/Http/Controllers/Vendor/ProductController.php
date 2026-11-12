<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\CategoryField;
use App\Models\FoodMenuCategory;
use App\Models\Product;
use App\Models\ProductOptionGroup;
use App\Models\ProductVariant;
use App\Services\Catalog\ListingCompletenessService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    protected function getVerticalsData(): array
    {
        return [
            'marketplace' => [
                'name' => 'Marketplace (Physical Products)',
                'icon' => '🛍️',
                'description' => 'Clothes, footwear, electronics, accessories with cart & delivery',
                'default_listing' => 'purchase',
            ],
            'food' => [
                'name' => 'Food & Restaurants',
                'icon' => '🍽️',
                'description' => 'Restaurant dishes, bakery, snacks with dietary tags',
                'default_listing' => 'purchase',
            ],
            'services' => [
                'name' => 'Professional Services',
                'icon' => '🔧',
                'description' => 'Home repairs, grooming, tailoring, bookings, consultations',
                'default_listing' => 'booking',
            ],
            'used' => [
                'name' => 'Used & Pre-Owned Items',
                'icon' => '♻️',
                'description' => 'Second-hand electronics, furniture, vehicles, classifieds',
                'default_listing' => 'contact_seller',
            ],
            'enquiry' => [
                'name' => 'Enquiry / Bulk Sourcing',
                'icon' => '📋',
                'description' => 'Wholesale fabrics, tiles, marble, industrial bulk quotes',
                'default_listing' => 'enquiry',
            ],
        ];
    }

    protected function getListingTypesData(): array
    {
        return [
            'purchase' => [
                'title' => 'Purchase',
                'badge' => 'Cart & Checkout',
                'description' => 'Customers can add to cart, pay online/COD, and track shipment.',
            ],
            'enquiry' => [
                'title' => 'Enquiry Only',
                'badge' => 'Quote Request',
                'description' => 'Customers send requirements and request custom quotes.',
            ],
            'booking' => [
                'title' => 'Booking / Slot',
                'badge' => 'Appointment',
                'description' => 'Customers select appointment dates and booking slots.',
            ],
            'auction' => [
                'title' => 'Auction / Bidding',
                'badge' => 'Live Bids',
                'description' => 'Customers place competitive bids until time expires.',
            ],
            'contact_seller' => [
                'title' => 'Contact Seller',
                'badge' => 'WhatsApp / Direct',
                'description' => 'Direct communication and negotiation with buyer, no checkout.',
            ],
        ];
    }

    protected function buildFilteredProductsQuery($vendor, Request $request)
    {
        $query = Product::where('vendor_id', $vendor->id)
            ->with(['category', 'foodMenuCategory', 'images', 'variants']);

        $search = trim((string) $request->query('search', ''));
        if ($search !== '') {
            $wildcard = '%' . addcslashes($search, '%_\\') . '%';
            $query->where(function ($q) use ($wildcard) {
                $q->where('title', 'LIKE', $wildcard)
                  ->orWhere('sku', 'LIKE', $wildcard)
                  ->orWhere('brand', 'LIKE', $wildcard)
                  ->orWhereHas('category', fn ($cq) => $cq->where('name', 'LIKE', $wildcard));
            });
        }

        $status = (string) $request->query('status', 'all');
        if (in_array($status, ['active', 'draft', 'archived'], true)) {
            $query->where('status', $status);
        }

        $stockFilter = (string) $request->query('stock', 'all');
        if ($stockFilter === 'low') {
            $query->where('total_stock', '>', 0)->where('total_stock', '<', 5);
        } elseif ($stockFilter === 'out') {
            $query->where('total_stock', '<=', 0);
        }

        $categoryId = (int) $request->query('category_id', 0);
        if ($categoryId > 0) {
            $query->where('category_id', $categoryId);
        }

        return $query;
    }

    public function index(Request $request)
    {
        $vendor = auth()->user()->vendor;
        if (!$vendor) abort(403);

        $targetVertical = $vendor->getVerticalKey();
        $search = trim((string) $request->query('search', ''));
        $status = (string) $request->query('status', 'all');
        $stockFilter = (string) $request->query('stock', 'all');
        $categoryId = (int) $request->query('category_id', 0);

        $counts = [
            'all' => Product::where('vendor_id', $vendor->id)->count(),
            'active' => Product::where('vendor_id', $vendor->id)->where('status', 'active')->count(),
            'draft' => Product::where('vendor_id', $vendor->id)->where('status', 'draft')->count(),
            'low_stock' => Product::where('vendor_id', $vendor->id)->where('total_stock', '<', 5)->count(),
        ];

        $categories = Category::whereIn('id', Product::where('vendor_id', $vendor->id)->distinct()->pluck('category_id')->filter())
            ->orderBy('name')
            ->get();

        $products = $this->buildFilteredProductsQuery($vendor, $request)
            ->with(['gigPackages'])
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $completenessMap = app(ListingCompletenessService::class)->evaluateMany($products->items());

        return view('vendor.products.index', compact(
            'products', 'vendor', 'targetVertical', 'search', 'status', 'stockFilter', 'categoryId', 'counts', 'categories', 'completenessMap'
        ));
    }

    public function exportCsv(Request $request)
    {
        $vendor = auth()->user()->vendor;
        if (!$vendor) abort(403);

        $products = $this->buildFilteredProductsQuery($vendor, $request)->latest()->get();
        $filename = 'k71-catalog-' . $vendor->slug . '-' . now()->format('Y-m-d') . '.csv';

        $sanitizeCsv = static function ($value): string {
            $str = str_replace(["\r", "\n"], ' ', (string) $value);
            if ($str !== '' && in_array($str[0], ['=', '+', '-', '@', "\t"], true)) {
                return "'" . $str;
            }
            return $str;
        };

        return response()->streamDownload(function () use ($products, $sanitizeCsv) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, [
                'ID', 'Title', 'SKU', 'Vertical', 'Listing Type', 'Category',
                'Price (INR)', 'Compare At (INR)', 'Stock', 'GST %', 'Status', 'Created At',
            ]);

            foreach ($products as $product) {
                fputcsv($out, [
                    $product->id,
                    $sanitizeCsv($product->title),
                    $sanitizeCsv($product->sku ?? ''),
                    $sanitizeCsv($product->vertical ?? 'marketplace'),
                    $sanitizeCsv($product->listing_type ?? 'purchase'),
                    $sanitizeCsv($product->category?->name ?? 'Uncategorized'),
                    number_format(((int) $product->base_price) / 100, 2, '.', ''),
                    $product->compare_at_price ? number_format(((int) $product->compare_at_price) / 100, 2, '.', '') : '',
                    (int) $product->total_stock,
                    (float) ($product->gst_rate ?? 0),
                    $sanitizeCsv($product->status),
                    optional($product->created_at)->format('Y-m-d H:i:s'),
                ]);
            }
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function bulkAction(Request $request)
    {
        $vendor = auth()->user()->vendor;
        if (!$vendor) abort(403);

        $validated = $request->validate([
            'action' => 'required|in:activate,draft,delete',
            'product_ids' => 'required|array|min:1',
            'product_ids.*' => 'integer',
        ]);

        $products = Product::where('vendor_id', $vendor->id)
            ->whereIn('id', $validated['product_ids'])
            ->get();

        if ($products->isEmpty()) {
            return back()->with('error', 'No matching products found in your catalog.');
        }

        $entitlements = app(\App\Services\Monetization\EntitlementService::class);
        $action = $validated['action'];
        $succeeded = 0;
        $skipped = [];

        foreach ($products as $product) {
            if ($action === 'activate') {
                $sector = $product->vertical ?: $vendor->getCanonicalSectorKey();
                $check = $entitlements->validateListingWrite($vendor, $sector, true, 0, 0, $product->id);
                if (!$check['allowed']) {
                    $skipped[] = "{$product->title}: {$check['reason']}";
                    continue;
                }
                $product->update([
                    'status' => 'active',
                    'published_at' => $product->published_at ?? now(),
                ]);
                $succeeded++;
            } elseif ($action === 'draft') {
                $product->update(['status' => 'draft']);
                $succeeded++;
            } elseif ($action === 'delete') {
                $hasReferences = \App\Models\OrderItem::where('product_id', $product->id)->exists()
                    || \App\Models\Booking::where('product_id', $product->id)->exists()
                    || \App\Models\ServiceOrder::where('product_id', $product->id)->exists()
                    || \App\Models\Bid::where('product_id', $product->id)->exists();

                if ($hasReferences) {
                    $product->update([
                        'status' => 'archived',
                        'total_stock' => 0,
                    ]);
                    $skipped[] = "{$product->title}: archived instead of purged to preserve order/booking history";
                    continue;
                }

                $product->modifiers()->delete();
                $product->optionGroups()->delete();
                $product->gigPackages()->delete();
                $product->variants()->forceDelete();
                if (method_exists($product, 'images')) {
                    foreach ($product->images as $img) {
                        if ($img->path) {
                            k71_delete_public_storage($img->path);
                        }
                    }
                    $product->images()->delete();
                }
                if ($product->video_path) {
                    k71_delete_public_storage($product->video_path);
                }
                $product->forceDelete();
                $succeeded++;
            }
        }

        \App\Http\Controllers\SitemapController::clearCache();

        $summary = "{$succeeded} listing(s) updated (" . ucfirst($action) . ").";
        if (!empty($skipped)) {
            $summary .= ' Skipped ' . count($skipped) . ': ' . implode(' | ', array_slice($skipped, 0, 2));
            return back()->with($succeeded > 0 ? 'success' : 'error', $summary);
        }

        return back()->with('success', $summary);
    }

    public function create()
    {
        $vendor = auth()->user()->vendor;
        $vendorType = $vendor?->vendor_type ?? 'clothing';
        $sectorConfig = $vendor ? $vendor->getSectorConfig() : [];
        $targetVertical = $vendor ? $vendor->getVerticalKey() : 'marketplace';

        // Smart Category Resolution tailored specifically to vendor's trade sector
        $categories = $this->resolveVendorCategories($vendorType, $targetVertical);

        $verticals = $this->getVerticalsData();
        $listingTypes = $this->getListingTypesData();
        $categoryFields = CategoryField::orderBy('sort_order')->get()->groupBy('vertical');
        $foodMenuCategories = $vendor
            ? FoodMenuCategory::where('vendor_id', $vendor->id)->orderBy('sort_order')->get()
            : collect();

        return view('vendor.products.create', compact('vendor', 'vendorType', 'sectorConfig', 'targetVertical', 'categories', 'verticals', 'listingTypes', 'categoryFields', 'foodMenuCategories'));
    }

    protected function resolveVendorCategories(string $vendorType, string $targetVertical)
    {
        $q = Category::with('parent')->where('is_active', true);

        switch ($vendorType) {
            case 'real_estate':
                $q->where(function($sq) {
                    $sq->where('slug', 'LIKE', '%real-estate%')
                       ->orWhere('name', 'LIKE', '%Real Estate%')
                       ->orWhere('name', 'LIKE', '%Residential%')
                       ->orWhere('name', 'LIKE', '%Commercial Properties%')
                       ->orWhere('name', 'LIKE', '%Property Rentals%')
                       ->orWhere('name', 'LIKE', '%Industrial Real Estate%')
                       ->orWhere('parent_id', function($sub) {
                           $sub->select('id')->from('categories')->where('slug', 'LIKE', '%real-estate%');
                       });
                });
                break;
            case 'clothing':
                $q->where(function($sq) {
                    $sq->where('slug', 'LIKE', '%clothing%')
                       ->orWhere('slug', 'LIKE', '%fashion%')
                       ->orWhere('slug', 'LIKE', '%accessories%')
                       ->orWhere('slug', 'LIKE', '%saree%')
                       ->orWhere('slug', 'LIKE', '%men%')
                       ->orWhere('slug', 'LIKE', '%women%')
                       ->orWhere('slug', 'LIKE', '%kids%')
                       ->orWhere('name', 'LIKE', '%Fashion%')
                       ->orWhere('name', 'LIKE', '%Apparel%');
                });
                break;
            case 'footwear':
                $q->where(function($sq) {
                    $sq->where('slug', 'LIKE', '%footwear%')
                       ->orWhere('slug', 'LIKE', '%shoe%')
                       ->orWhere('name', 'LIKE', '%Footwear%')
                       ->orWhere('name', 'LIKE', '%Shoes%');
                });
                break;
            case 'electronics':
                $q->where(function($sq) {
                    $sq->where('slug', 'LIKE', '%electronic%')
                       ->orWhere('slug', 'LIKE', '%mobile%')
                       ->orWhere('slug', 'LIKE', '%computer%')
                       ->orWhere('slug', 'LIKE', '%appliance%')
                       ->orWhere('slug', 'LIKE', '%tv%')
                       ->orWhere('name', 'LIKE', '%Electronic%')
                       ->orWhere('name', 'LIKE', '%Mobile%')
                       ->orWhere('name', 'LIKE', '%Computer%');
                });
                break;
            case 'automotive':
                $q->where(function($sq) {
                    $sq->where('slug', 'LIKE', '%automotive%')
                       ->orWhere('slug', 'LIKE', '%vehicle%')
                       ->orWhere('slug', 'LIKE', '%car%')
                       ->orWhere('slug', 'LIKE', '%bike%')
                       ->orWhere('slug', 'LIKE', '%spare%')
                       ->orWhere('name', 'LIKE', '%Automotive%')
                       ->orWhere('name', 'LIKE', '%Vehicle%');
                });
                break;
            case 'beauty':
                $q->where(function($sq) {
                    $sq->where('slug', 'LIKE', '%beauty%')
                       ->orWhere('slug', 'LIKE', '%skin%')
                       ->orWhere('slug', 'LIKE', '%cosmetic%')
                       ->orWhere('slug', 'LIKE', '%wellness%')
                       ->orWhere('slug', 'LIKE', '%fragrance%')
                       ->orWhere('name', 'LIKE', '%Beauty%')
                       ->orWhere('name', 'LIKE', '%Personal Care%');
                });
                break;
            case 'home_living':
                $q->where(function($sq) {
                    $sq->where('slug', 'LIKE', '%home-living%')
                       ->orWhere('slug', 'LIKE', '%furniture%')
                       ->orWhere('slug', 'LIKE', '%decor%')
                       ->orWhere('slug', 'LIKE', '%kitchenware%')
                       ->orWhere('slug', 'LIKE', '%building%')
                       ->orWhere('name', 'LIKE', '%Home%')
                       ->orWhere('name', 'LIKE', '%Furniture%');
                });
                break;
            case 'feasto':
            case 'groceries':
            case 'kirana':
                $q->where(function($sq) {
                    $sq->where('slug', 'LIKE', '%grocer%')
                       ->orWhere('slug', 'LIKE', '%kirana%')
                       ->orWhere('slug', 'LIKE', '%fmcg%')
                       ->orWhere('slug', 'LIKE', '%staple%')
                       ->orWhere('slug', 'LIKE', '%produce%')
                       ->orWhere('slug', 'LIKE', '%snack%')
                       ->orWhere('slug', 'LIKE', '%beverage%')
                       ->orWhere('name', 'LIKE', '%Grocer%')
                       ->orWhere('name', 'LIKE', '%Kirana%')
                       ->orWhere('name', 'LIKE', '%Daily Essentials%');
                });
                break;
            case 'toys_sports':
                $q->where(function($sq) {
                    $sq->where('slug', 'LIKE', '%toy%')
                       ->orWhere('slug', 'LIKE', '%book%')
                       ->orWhere('slug', 'LIKE', '%sport%')
                       ->orWhere('slug', 'LIKE', '%baby%')
                       ->orWhere('slug', 'LIKE', '%game%')
                       ->orWhere('name', 'LIKE', '%Toys%')
                       ->orWhere('name', 'LIKE', '%Sports%');
                });
                break;
            case 'digital_services':
                $q->where(function($sq) {
                    $sq->where('slug', 'LIKE', '%digital-service%')
                       ->orWhere('slug', 'LIKE', '%freelancing%')
                       ->orWhere('slug', 'LIKE', '%gig%')
                       ->orWhere('slug', 'LIKE', '%programming%')
                       ->orWhere('slug', 'LIKE', '%graphic%')
                       ->orWhere('name', 'LIKE', '%Digital%')
                       ->orWhere('name', 'LIKE', '%Freelanc%');
                });
                break;
            case 'marble':
            case 'machinery':
            case 'b2b':
                $q->where(function($sq) {
                    $sq->where('slug', 'LIKE', '%machinery%')
                       ->orWhere('slug', 'LIKE', '%industrial%')
                       ->orWhere('slug', 'LIKE', '%bulk%')
                       ->orWhere('slug', 'LIKE', '%marble%')
                       ->orWhere('slug', 'LIKE', '%granite%')
                       ->orWhere('slug', 'LIKE', '%stone%')
                       ->orWhere('name', 'LIKE', '%Machinery%')
                       ->orWhere('name', 'LIKE', '%Industrial%')
                       ->orWhere('name', 'LIKE', '%Marble%')
                       ->orWhere('name', 'LIKE', '%Granite%')
                       ->orWhere('vertical', 'enquiry');
                });
                break;
            case 'service':
            case 'services':
            case 'local_services':
            case 'digital_services':
                $q->where(function($sq) {
                    $sq->where('slug', 'LIKE', '%service%')
                       ->orWhere('slug', 'LIKE', '%on-demand%')
                       ->orWhere('name', 'LIKE', '%Service%')
                       ->orWhere('vertical', 'services');
                });
                break;
            case 'food':
                $q->where(function($sq) {
                    $sq->where('slug', 'LIKE', '%food%')
                       ->orWhere('slug', 'LIKE', '%dining%')
                       ->orWhere('slug', 'LIKE', '%restaurant%')
                       ->orWhere('name', 'LIKE', '%Food%')
                       ->orWhere('name', 'LIKE', '%Dining%')
                       ->orWhere('vertical', 'food');
                });
                break;
            case 'used':
                $q->where(function($sq) {
                    $sq->where('slug', 'LIKE', '%used%')
                       ->orWhere('slug', 'LIKE', '%pre-owned%')
                       ->orWhere('slug', 'LIKE', '%pre-loved%')
                       ->orWhere('slug', 'LIKE', '%thrift%')
                       ->orWhere('name', 'LIKE', '%Used%')
                       ->orWhere('name', 'LIKE', '%Pre-Owned%')
                       ->orWhere('vertical', 'used');
                });
                break;
            default:
                if (in_array($targetVertical, ['food', 'services', 'used', 'enquiry'])) {
                    $q->where('vertical', $targetVertical);
                } else {
                    $q->where('vertical', 'marketplace');
                }
                break;
        }

        $cats = $q->get();
        if ($cats->isEmpty()) {
            $cats = Category::with('parent')->where('is_active', true)->get();
        }

        return $cats->sortBy(function($cat) {
            $parentName = $cat->parent ? $cat->parent->name : $cat->name;
            return $parentName . ' - ' . $cat->name;
        })->values();
    }

    public function store(Request $request)
    {
        $vendor = auth()->user()->vendor;
        if (!$vendor) {
            abort(403, 'Vendor store required.');
        }

        if ($request->has('sku')) {
            $trimmedSku = trim((string) $request->input('sku'));
            $request->merge(['sku' => $trimmedSku !== '' ? $trimmedSku : null]);
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'category_id' => ['required', 'exists:categories,id'],
            'vertical' => ['nullable', 'string', 'in:marketplace,ecommerce,retail,food,feasto,services,used,enquiry'],
            'listing_type' => ['nullable', 'string', 'in:purchase,enquiry,booking,auction,contact_seller'],
            'custom_fields' => ['nullable', 'array'],
            'base_price' => ['required', 'numeric', 'min:0'],
            'compare_at_price' => ['nullable', 'numeric', 'min:0'],
            'gst_rate' => ['required', 'numeric', 'in:0,5,12,18,28'],
            'hsn_code' => ['nullable', 'string', 'max:8'],
            'weight_grams' => ['nullable', 'integer', 'min:0'],
            'country_of_origin' => ['nullable', 'string', 'max:50'],
            'is_returnable' => ['nullable', 'boolean'],
            'return_window_days' => ['nullable', 'integer', 'min:0'],
            'brand' => ['nullable', 'string', 'max:100'],
            'material' => ['nullable', 'string', 'max:200'],
            'care_instructions' => ['nullable', 'string'],
            'size_chart' => ['nullable'],
            'tags' => ['nullable', 'string'],
            'gender' => ['nullable', 'string', 'max:20'],
            'age_group' => ['nullable', 'string', 'max:20'],
            'description' => ['required', 'string'],
            'sku' => ['nullable', 'string', 'max:191', Rule::unique('products', 'sku')],
            'total_stock' => ['required', 'integer', 'min:0'],
            'status' => ['required', 'in:draft,active,archived'],
            'variants' => ['nullable', 'array'],
            'variants.*.name' => ['nullable', 'string', 'max:255'],
            'variants.*.price' => ['nullable', 'numeric', 'min:0'],
            'variants.*.stock' => ['nullable', 'integer', 'min:0'],
            'variants.*.sku' => ['nullable', 'string', 'max:191'],
            'variants.*.color' => ['nullable', 'string', 'max:100'],
            'variants.*.color_hex' => ['nullable', 'string', 'max:20'],
            'variants.*.size' => ['nullable', 'string', 'max:50'],
            'variants.*.image_url' => ['nullable', 'string', 'max:500'],
            'food_menu_category_id' => ['nullable', 'integer', 'exists:food_menu_categories,id'],
            'dietary_label' => ['nullable', 'string', 'in:veg,non_veg,egg,vegan,jain,pure_veg'],
            'prep_time_mins' => ['nullable', 'integer', 'min:1', 'max:240'],
            'menu_sort_order' => ['nullable', 'integer', 'min:0', 'max:99999'],
            'is_dish_available' => ['nullable', 'boolean'],
            'available_from' => ['nullable', 'string', 'max:10'],
            'available_until' => ['nullable', 'string', 'max:10'],
            'food_portions' => ['nullable', 'array'],
            'option_groups' => ['nullable', 'array'],
            'modifiers' => ['nullable', 'array'],
            'gig_packages' => ['nullable', 'array'],
            'b2b_tiers' => ['nullable', 'array'],
            'service_config' => ['nullable', 'array'],
            'specifications' => ['nullable', 'array'],
            'enable_rfq' => ['nullable'],
            'images.*' => ['image', 'mimes:jpeg,png,jpg,webp,gif,avif', 'max:15360'],
            'cover_image_index' => ['nullable', 'integer', 'min:0'],
            'video' => ['nullable', 'file', 'mimes:mp4,webm,mov,ogg,m4v,qt,avi', 'max:51200'],
            'video_url' => ['nullable', 'string', 'max:500'],
        ]);

        // Enforce strict vertical isolation from vendor profile with request input
        $targetVertical = $request->input('vertical') ?: ($vendor->getVerticalKey() ?: 'marketplace');
        $validated['vertical'] = $targetVertical;

        if ($targetVertical === 'services') {
            $validated['listing_type'] = $validated['listing_type'] ?? 'booking';
        } elseif ($targetVertical === 'used') {
            $validated['listing_type'] = $validated['listing_type'] ?? 'contact_seller';
        } elseif ($targetVertical === 'enquiry') {
            $validated['listing_type'] = $validated['listing_type'] ?? 'enquiry';
        } else {
            $validated['listing_type'] = $validated['listing_type'] ?? 'purchase';
        }

        if ($request->has('enable_rfq') && $request->boolean('enable_rfq')) {
            $validated['listing_type'] = 'enquiry';
        }

        if ($request->has('is_negotiable')) {
            $validated['is_negotiable'] = $request->boolean('is_negotiable');
        }

        if ($request->has('custom_fields') && is_array($request->custom_fields)) {
            $validated['custom_fields'] = $request->custom_fields;
        }

        // Verify food_menu_category_id ownership + normalize food availability defaults
        if (!empty($validated['food_menu_category_id'])) {
            $ownedCat = FoodMenuCategory::where('vendor_id', $vendor->id)->find($validated['food_menu_category_id']);
            if (!$ownedCat) {
                return back()->withInput()->withErrors(['food_menu_category_id' => 'Invalid menu section selected.']);
            }
        }
        $validated['is_dish_available'] = $request->has('is_dish_available') ? $request->boolean('is_dish_available') : true;
        $validated['menu_sort_order'] = (int) ($validated['menu_sort_order'] ?? 0);
        if (empty($validated['dietary_label']) && !empty($validated['custom_fields']['dietary_type'])) {
            $dtLower = strtolower(trim((string) $validated['custom_fields']['dietary_type']));
            $validated['dietary_label'] = match (true) {
                str_contains($dtLower, 'non') => 'non_veg',
                str_contains($dtLower, 'egg') => 'egg',
                str_contains($dtLower, 'vegan') => 'vegan',
                str_contains($dtLower, 'jain') => 'jain',
                str_contains($dtLower, 'pure') => 'pure_veg',
                str_contains($dtLower, 'veg') => 'veg',
                default => null,
            };
        }

        if ($request->has('specifications') && is_array($request->specifications)) {
            $specsInput = $request->specifications;
            if (array_is_list($specsInput)) {
                $specsInput = array_values(array_filter($specsInput, function ($row) {
                    if (!is_array($row)) return false;
                    $k = trim((string) ($row['name'] ?? $row['key'] ?? ''));
                    $v = trim((string) ($row['value'] ?? ''));
                    return $k !== '' || $v !== '';
                }));
            }
            $validated['specifications_json'] = $specsInput;
        }

        if ($request->has('b2b_tiers') && is_array($request->b2b_tiers)) {
            $validated['b2b_tiers_json'] = array_values(array_filter($request->b2b_tiers, fn($t) => !empty($t['min_qty'])));
        }

        if ($request->has('service_config') && is_array($request->service_config)) {
            $validated['service_config_json'] = $request->service_config;
        }

        $baseSlug = Str::slug($validated['title']) ?: 'product';
        $slug = $baseSlug;
        $counter = 1;
        while (Product::withoutGlobalScopes()->where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter++;
        }

        $validated['slug'] = $slug;
        $validated['base_price'] = (int) round(((float) $validated['base_price']) * 100); // convert to paise
        if (isset($validated['compare_at_price'])) {
            $validated['compare_at_price'] = (int) round(((float) $validated['compare_at_price']) * 100);
        }

        if (isset($validated['tags']) && trim((string) $validated['tags']) !== '') {
            $validated['tags'] = array_values(array_filter(array_map('trim', explode(',', $validated['tags'])), fn ($t) => $t !== ''));
        } else {
            $validated['tags'] = null;
        }

        $validated['is_returnable'] = $request->boolean('is_returnable');

        if ($request->filled('size_chart')) {
            $sc = $request->input('size_chart');
            $validated['size_chart'] = is_string($sc) ? json_decode($sc, true) : $sc;
        } else {
            $validated['size_chart'] = null;
        }

        if ($validated['status'] === 'active') {
            $validated['published_at'] = now();
        }

        // Normalize valid variant & food portion rows before stock aggregation
        $cleanVariants = [];
        if ($request->has('variants') && is_array($request->variants)) {
            foreach ($request->variants as $v) {
                if (!is_array($v)) continue;
                $vName = trim((string) ($v['name'] ?? ''));
                $vSku = trim((string) ($v['sku'] ?? ''));
                $vSize = trim((string) ($v['size'] ?? ''));
                $vColor = trim((string) ($v['color'] ?? ''));
                if ($vName === '' && $vSku === '' && $vSize === '' && $vColor === '') {
                    continue;
                }
                if ($vName === '') {
                    $v['name'] = trim(($vColor ? $vColor . ' / ' : '') . ($vSize ?: $vSku)) ?: 'Standard';
                }
                $cleanVariants[] = $v;
            }
        }

        $cleanFoodPortions = [];
        if ($request->has('food_portions') && is_array($request->food_portions)) {
            foreach ($request->food_portions as $fp) {
                if (!is_array($fp)) continue;
                if (trim((string) ($fp['name'] ?? '')) === '') continue;
                $cleanFoodPortions[] = $fp;
            }
        }

        // Accurately calculate total_stock from variants sum if variants provided
        if (count($cleanVariants) > 0) {
            $sumStock = 0;
            foreach ($cleanVariants as $v) {
                $sumStock += (int) ($v['stock'] ?? 0);
            }
            $validated['total_stock'] = $sumStock;
        } elseif (count($cleanFoodPortions) > 0) {
            $sumStock = 0;
            foreach ($cleanFoodPortions as $fp) {
                $sumStock += (int) ($fp['stock'] ?? 50);
            }
            $validated['total_stock'] = $sumStock;
        }

        $productAttributes = collect($validated)->except([
            'images',
            'cover_image_index',
            'video',
            'video_url',
            'video_path',
            'variants',
            'food_portions',
            'option_groups',
            'modifiers',
            'gig_packages',
            'b2b_tiers',
            'service_config',
            'specifications',
            'enable_rfq',
        ])->toArray();

        $productAttributes['vendor_id'] = $vendor->id;

        // Server-side monetization & entitlement check (active plan, numeric listing limits, image limits, feature flags)
        $newImgCount = $request->hasFile('images') ? count(array_filter($request->file('images') ?? [], fn($f) => $f && $f->isValid())) : 0;
        $requestedFeatures = [];
        if (count($cleanVariants) > 0 && !in_array($targetVertical, ['food', 'services', 'used', 'enquiry'], true)) {
            $requestedFeatures[] = 'allow_variants';
        }
        if ($targetVertical === 'food' && ($request->boolean('option_groups_submitted') || !empty($request->input('option_groups')) || !empty($request->input('modifiers')) || count($cleanFoodPortions) > 0)) {
            $requestedFeatures[] = 'allow_menu_addons';
        }
        if ($targetVertical === 'enquiry' && !empty($validated['b2b_tiers_json'])) {
            $requestedFeatures[] = 'allow_b2b_tiers';
        }
        $entitlementCheck = app(\App\Services\Monetization\EntitlementService::class)->validateListingWrite(
            $vendor,
            $targetVertical,
            ($validated['status'] ?? 'draft') === 'active',
            $newImgCount,
            0,
            null,
            $requestedFeatures
        );
        if (!$entitlementCheck['allowed']) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $entitlementCheck['reason']], 403);
            }
            return back()->withInput()->with('error', $entitlementCheck['reason']);
        }

        try {
            return \Illuminate\Support\Facades\DB::transaction(function () use ($request, $validated, $productAttributes, $vendor, $cleanVariants, $cleanFoodPortions) {
                $product = Product::create($productAttributes);

            // 1. Process standard / fashion matrix variants
            if (count($cleanVariants) > 0) {
                foreach ($cleanVariants as $index => $variantData) {
                    $rawSku = !empty($variantData['sku']) ? trim((string) $variantData['sku']) : trim((string) $variantData['name']);
                    $sku = Str::limit($rawSku ?: 'VARIANT-' . ($index + 1), 191, '');

                    $baseRupees = round($validated['base_price'] / 100, 2);
                    $priceDecimal = isset($variantData['price']) && $variantData['price'] !== '' ? (float) $variantData['price'] : $baseRupees;
                    if ($priceDecimal <= 0 && $baseRupees > 0) {
                        $priceDecimal = $baseRupees;
                    }
                    $priceOverride = (int) round($priceDecimal * 100);

                    $attributesHash = [];
                    if (!empty($variantData['color'])) $attributesHash['color'] = $variantData['color'];
                    if (!empty($variantData['color_hex'])) $attributesHash['color_hex'] = $variantData['color_hex'];
                    if (!empty($variantData['size'])) $attributesHash['size'] = $variantData['size'];
                    if (empty($attributesHash)) $attributesHash['name'] = $variantData['name'];

                    $product->variants()->create([
                        'sku' => $sku,
                        'price_override' => $priceOverride,
                        'price' => $priceDecimal,
                        'stock' => (int) ($variantData['stock'] ?? 0),
                        'stock_quantity' => (int) ($variantData['stock'] ?? 0),
                        'attributes_hash' => $attributesHash,
                        'image_url' => $variantData['image_url'] ?? null,
                        'is_active' => true,
                        'status' => 'active',
                        'sort_order' => $index + 1,
                    ]);
                }
            } elseif (count($cleanFoodPortions) > 0) {
                // 2. Food Portions as variants
                foreach ($cleanFoodPortions as $index => $portion) {
                    $portionName = trim((string) $portion['name']);
                    $priceDecimal = isset($portion['price']) && $portion['price'] !== '' ? (float) $portion['price'] : round($validated['base_price'] / 100, 2);
                    $product->variants()->create([
                        'sku' => Str::limit($portionName, 191, ''),
                        'price_override' => (int) round($priceDecimal * 100),
                        'price' => $priceDecimal,
                        'stock' => (int) ($portion['stock'] ?? 50),
                        'stock_quantity' => (int) ($portion['stock'] ?? 50),
                        'attributes_hash' => ['portion' => $portionName],
                        'is_active' => true,
                        'status' => 'active',
                        'sort_order' => $index + 1,
                    ]);
                }
            } else {
                // Default single variant if they didn't generate any matrix
                $product->variants()->create([
                    'sku' => 'Default',
                    'price' => round($validated['base_price'] / 100, 2),
                    'price_override' => $validated['base_price'],
                    'stock' => $validated['total_stock'],
                    'stock_quantity' => $validated['total_stock'],
                    'attributes_hash' => ['type' => 'Standard'],
                    'is_active' => true,
                    'status' => 'active',
                    'sort_order' => 1,
                ]);
            }

            // 3. Process Food Option Groups & Modifiers
            $this->syncOptionGroupsAndModifiers($product, $request);

            // 4. Process Digital Gig 3-tier Packages (Fiverr Model)
            if ($request->has('gig_packages') && is_array($request->gig_packages)) {
                foreach (['basic', 'standard', 'premium'] as $tier) {
                    if (isset($request->gig_packages[$tier]) && !empty($request->gig_packages[$tier]['title'])) {
                        $pkg = $request->gig_packages[$tier];
                        $product->gigPackages()->create([
                            'tier' => $tier,
                            'title' => $pkg['title'],
                            'description' => $pkg['description'] ?? '',
                            'delivery_days' => (int)($pkg['delivery_days'] ?? 3),
                            'revisions' => (int)($pkg['revisions'] ?? 1),
                            'price' => (float)($pkg['price'] ?? ($tier === 'basic' ? 499 : ($tier === 'standard' ? 1499 : 3499))),
                            'features_json' => $pkg['features'] ?? [],
                        ]);
                    }
                }
            }

            // Update has_variants flag
            $hasMultiple = ($product->variants()->count() > 1 && $product->variants()->first()?->sku !== 'Default')
                || $product->optionGroups()->exists()
                || $product->modifiers()->exists()
                || $product->gigPackages()->exists();

            $product->update(['has_variants' => $hasMultiple]);

            // Process optional product video
            if ($request->hasFile('video') && $request->file('video')->isValid()) {
                $videoPath = $request->file('video')->store('products/videos', 'public');
                k71_sync_public_storage($videoPath);
                if (\Illuminate\Support\Facades\Schema::hasColumn('products', 'video_path')) {
                    $product->update(['video_path' => $videoPath]);
                }
            } elseif ($request->filled('video_url')) {
                if (\Illuminate\Support\Facades\Schema::hasColumn('products', 'video_url')) {
                    $product->update(['video_url' => trim($request->input('video_url'))]);
                }
            }

            if ($request->hasFile('images')) {
                $uploadedFiles = array_values(array_filter($request->file('images') ?? [], fn ($f) => $f && $f->isValid()));
                $coverIdx = (int) $request->input('cover_image_index', 0);

                // Prioritize chosen cover image at sort_order 0
                $orderedFiles = [];
                if (isset($uploadedFiles[$coverIdx])) {
                    $orderedFiles[] = $uploadedFiles[$coverIdx];
                    foreach ($uploadedFiles as $idx => $f) {
                        if ($idx !== $coverIdx) {
                            $orderedFiles[] = $f;
                        }
                    }
                } else {
                    $orderedFiles = $uploadedFiles;
                }

                foreach ($orderedFiles as $sortOrder => $image) {
                    $path = $image->store('products', 'public');
                    k71_sync_public_storage($path);
                    $product->images()->create([
                        'path' => $path,
                        'alt_text' => $product->title,
                        'sort_order' => $sortOrder,
                    ]);
                }
            }

            \App\Http\Controllers\SitemapController::clearCache();

            return redirect()->route('vendor.products.index')->with('success', 'Product created successfully.');
        });
    } catch (\Throwable $e) {
        \Illuminate\Support\Facades\Log::error('Product creation failed: ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString()
        ]);
        return back()->withInput()->with('error', 'Unable to publish product: ' . $e->getMessage());
    }
}

    public function edit(Product $product)
    {
        $vendor = auth()->user()->vendor;
        if (!$vendor || $product->vendor_id !== $vendor->id) {
            abort(403, 'Unauthorized access to product.');
        }

        $vendorType = $vendor->vendor_type ?? 'clothing';
        $sectorConfig = $vendor->getSectorConfig();
        $targetVertical = $product->vertical ?: ($vendor->getVerticalKey() ?: 'marketplace');

        $categories = $this->resolveVendorCategories($vendorType, $targetVertical);

        $product->load(['variants', 'images', 'optionGroups.modifiers', 'modifiers', 'gigPackages', 'category', 'foodMenuCategory']);

        $existingVariants = $product->variants->map(function ($v) use ($product) {
            $attr = is_array($v->attributes_hash) ? $v->attributes_hash : (json_decode($v->attributes_hash ?? '[]', true) ?: []);
            $baseRupees = round($product->base_price / 100, 2);
            $varPrice = $v->price ? (float)$v->price : ($v->price_override ? $v->price_override / 100 : $baseRupees);
            if ((empty($varPrice) || $varPrice <= 10) && $baseRupees > 50) {
                $varPrice = $baseRupees;
            }
            return [
                'name' => $v->sku ?: 'Default',
                'price' => $varPrice,
                'stock' => (int) ($v->stock_quantity ?: $v->stock),
                'sku' => $v->sku ?: '',
                'color' => $attr['color'] ?? '',
                'color_hex' => $attr['color_hex'] ?? '#000000',
                'size' => $attr['size'] ?? '',
                'image_url' => $v->image_url ?? '',
                'attributes_hash' => $attr,
            ];
        })->values()->toArray();

        $existingModifiers = $product->modifiers->map(fn($m) => [
            'group_name' => $m->group_name,
            'modifier_name' => $m->modifier_name,
            'price_delta' => (float)$m->price_delta,
            'is_required' => (bool)$m->is_required,
            'is_available' => $m->is_available !== false,
            'is_default' => (bool) ($m->is_default ?? false),
        ])->toArray();

        $existingOptionGroups = $product->optionGroups->map(fn ($g) => [
            'id' => $g->id,
            'name' => $g->name,
            'selection_type' => $g->selection_type,
            'is_required' => (bool) $g->is_required,
            'min_selections' => (int) $g->min_selections,
            'max_selections' => $g->max_selections,
            'sort_order' => (int) $g->sort_order,
            'options' => $g->modifiers->map(fn ($m) => [
                'id' => $m->id,
                'modifier_name' => $m->modifier_name,
                'price_delta' => (float) $m->price_delta,
                'is_available' => $m->is_available !== false,
                'is_default' => (bool) ($m->is_default ?? false),
            ])->values()->toArray(),
        ])->values()->toArray();

        $foodMenuCategories = FoodMenuCategory::where('vendor_id', $vendor->id)->orderBy('sort_order')->get();

        $existingGigPackages = [];
        foreach ($product->gigPackages as $pkg) {
            $existingGigPackages[$pkg->tier] = [
                'tier' => $pkg->tier,
                'title' => $pkg->title,
                'description' => $pkg->description,
                'delivery_days' => $pkg->delivery_days,
                'revisions' => $pkg->revisions,
                'price' => (float)$pkg->price,
                'features' => $pkg->features_json ?? [],
            ];
        }

        $existingB2bTiers = $product->b2b_tiers_json ?: [];
        $existingServiceConfig = $product->service_config_json ?: [];
        $existingSpecs = $product->specifications_json ?: [];

        $verticals = $this->getVerticalsData();
        $listingTypes = $this->getListingTypesData();
        $categoryFields = CategoryField::orderBy('sort_order')->get()->groupBy('vertical');

        return view('vendor.products.edit', compact(
            'vendor', 'vendorType', 'targetVertical', 'product', 'categories', 'existingVariants',
            'existingModifiers', 'existingOptionGroups', 'foodMenuCategories',
            'existingGigPackages', 'existingB2bTiers',
            'existingServiceConfig', 'existingSpecs', 'verticals', 'listingTypes', 'categoryFields', 'sectorConfig'
        ));
    }

    public function update(Request $request, Product $product)
    {
        $vendor = auth()->user()->vendor;
        if (!$vendor || $product->vendor_id !== $vendor->id) {
            abort(403, 'Unauthorized access to product.');
        }

        if ($request->has('sku')) {
            $trimmedSku = trim((string) $request->input('sku'));
            $request->merge(['sku' => $trimmedSku !== '' ? $trimmedSku : null]);
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'category_id' => ['required', 'exists:categories,id'],
            'vertical' => ['nullable', 'string', 'in:marketplace,ecommerce,retail,food,feasto,services,used,enquiry'],
            'listing_type' => ['nullable', 'string', 'in:purchase,enquiry,booking,auction,contact_seller'],
            'custom_fields' => ['nullable', 'array'],
            'base_price' => ['required', 'numeric', 'min:0'],
            'compare_at_price' => ['nullable', 'numeric', 'min:0'],
            'gst_rate' => ['required', 'numeric', 'in:0,5,12,18,28'],
            'hsn_code' => ['nullable', 'string', 'max:8'],
            'weight_grams' => ['nullable', 'integer', 'min:0'],
            'country_of_origin' => ['nullable', 'string', 'max:50'],
            'is_returnable' => ['nullable', 'boolean'],
            'return_window_days' => ['nullable', 'integer', 'min:0'],
            'brand' => ['nullable', 'string', 'max:100'],
            'material' => ['nullable', 'string', 'max:200'],
            'care_instructions' => ['nullable', 'string'],
            'size_chart' => ['nullable'],
            'tags' => ['nullable', 'string'],
            'gender' => ['nullable', 'string', 'max:20'],
            'age_group' => ['nullable', 'string', 'max:20'],
            'description' => ['required', 'string'],
            'sku' => ['nullable', 'string', 'max:191', Rule::unique('products', 'sku')->ignore($product->id)],
            'total_stock' => ['required', 'integer', 'min:0'],
            'status' => ['required', 'in:draft,active,archived'],
            'variants' => ['nullable', 'array'],
            'variants.*.name' => ['nullable', 'string', 'max:255'],
            'variants.*.price' => ['nullable', 'numeric', 'min:0'],
            'variants.*.stock' => ['nullable', 'integer', 'min:0'],
            'variants.*.sku' => ['nullable', 'string', 'max:191'],
            'variants.*.color' => ['nullable', 'string', 'max:100'],
            'variants.*.color_hex' => ['nullable', 'string', 'max:20'],
            'variants.*.size' => ['nullable', 'string', 'max:50'],
            'variants.*.image_url' => ['nullable', 'string', 'max:500'],
            'food_menu_category_id' => ['nullable', 'integer', 'exists:food_menu_categories,id'],
            'dietary_label' => ['nullable', 'string', 'in:veg,non_veg,egg,vegan,jain,pure_veg'],
            'prep_time_mins' => ['nullable', 'integer', 'min:1', 'max:240'],
            'menu_sort_order' => ['nullable', 'integer', 'min:0', 'max:99999'],
            'is_dish_available' => ['nullable', 'boolean'],
            'available_from' => ['nullable', 'string', 'max:10'],
            'available_until' => ['nullable', 'string', 'max:10'],
            'food_portions' => ['nullable', 'array'],
            'option_groups' => ['nullable', 'array'],
            'modifiers' => ['nullable', 'array'],
            'gig_packages' => ['nullable', 'array'],
            'b2b_tiers' => ['nullable', 'array'],
            'service_config' => ['nullable', 'array'],
            'specifications' => ['nullable', 'array'],
            'enable_rfq' => ['nullable'],
            'images.*' => ['image', 'mimes:jpeg,png,jpg,webp,gif,avif', 'max:15360'],
            'cover_image_index' => ['nullable', 'integer', 'min:0'],
            'video' => ['nullable', 'file', 'mimes:mp4,webm,mov,ogg,m4v,qt,avi', 'max:51200'],
            'video_url' => ['nullable', 'string', 'max:500'],
            'remove_video' => ['nullable', 'boolean'],
        ]);

        // Enforce strict vertical isolation from vendor profile with request input
        $targetVertical = $request->input('vertical') ?: ($vendor->getVerticalKey() ?: 'marketplace');
        $validated['vertical'] = $targetVertical;

        if ($targetVertical === 'services') {
            $validated['listing_type'] = $validated['listing_type'] ?? 'booking';
        } elseif ($targetVertical === 'used') {
            $validated['listing_type'] = $validated['listing_type'] ?? 'contact_seller';
        } elseif ($targetVertical === 'enquiry') {
            $validated['listing_type'] = $validated['listing_type'] ?? 'enquiry';
        } else {
            $validated['listing_type'] = $validated['listing_type'] ?? 'purchase';
        }

        if ($request->has('enable_rfq') && $request->boolean('enable_rfq')) {
            $validated['listing_type'] = 'enquiry';
        }

        if ($request->has('is_negotiable')) {
            $validated['is_negotiable'] = $request->boolean('is_negotiable');
        }

        if ($request->has('custom_fields')) {
            $validated['custom_fields'] = is_array($request->custom_fields) ? $request->custom_fields : [];
        }

        if (!empty($validated['food_menu_category_id'])) {
            $ownedCat = FoodMenuCategory::where('vendor_id', $vendor->id)->find($validated['food_menu_category_id']);
            if (!$ownedCat) {
                return back()->withInput()->withErrors(['food_menu_category_id' => 'Invalid menu section selected.']);
            }
        }
        if ($request->has('food_settings_present') || $request->has('is_dish_available')) {
            $validated['is_dish_available'] = $request->boolean('is_dish_available');
        }
        if (array_key_exists('menu_sort_order', $validated) && $validated['menu_sort_order'] === null) {
            $validated['menu_sort_order'] = $product->menu_sort_order ?? 0;
        }
        if (empty($validated['dietary_label']) && !empty($validated['custom_fields']['dietary_type'])) {
            $dtLower = strtolower(trim((string) $validated['custom_fields']['dietary_type']));
            $validated['dietary_label'] = match (true) {
                str_contains($dtLower, 'non') => 'non_veg',
                str_contains($dtLower, 'egg') => 'egg',
                str_contains($dtLower, 'vegan') => 'vegan',
                str_contains($dtLower, 'jain') => 'jain',
                str_contains($dtLower, 'pure') => 'pure_veg',
                str_contains($dtLower, 'veg') => 'veg',
                default => $product->dietary_label,
            };
        }

        if ($request->has('specifications') && is_array($request->specifications)) {
            $specsInput = $request->specifications;
            if (array_is_list($specsInput)) {
                $specsInput = array_values(array_filter($specsInput, function ($row) {
                    if (!is_array($row)) return false;
                    $k = trim((string) ($row['name'] ?? $row['key'] ?? ''));
                    $v = trim((string) ($row['value'] ?? ''));
                    return $k !== '' || $v !== '';
                }));
            }
            $validated['specifications_json'] = $specsInput;
        }

        if ($request->has('b2b_tiers') && is_array($request->b2b_tiers)) {
            $validated['b2b_tiers_json'] = array_values(array_filter($request->b2b_tiers, fn($t) => !empty($t['min_qty'])));
        }

        if ($request->has('service_config') && is_array($request->service_config)) {
            $validated['service_config_json'] = $request->service_config;
        }

        // Server-side monetization & entitlement check for updates
        $updateSector = $validated['vertical'] ?: $product->vertical ?: $vendor->getCanonicalSectorKey();
        $newImgCount = $request->hasFile('images') ? count(array_filter($request->file('images') ?? [], fn($f) => $f && $f->isValid())) : 0;
        $existingImgCount = $product->images()->count();
        $entitlementCheck = app(\App\Services\Monetization\EntitlementService::class)->validateListingWrite(
            $vendor,
            $updateSector,
            ($validated['status'] ?? $product->status) === 'active',
            $newImgCount,
            $existingImgCount,
            $product->id,
            []
        );
        if (!$entitlementCheck['allowed']) {
            return back()->withInput()->with('error', $entitlementCheck['reason']);
        }

        $validated['base_price'] = (int) round(((float) $validated['base_price']) * 100);
        if (isset($validated['compare_at_price'])) {
            $validated['compare_at_price'] = (int) round(((float) $validated['compare_at_price']) * 100);
        }

        if (isset($validated['tags']) && trim((string) $validated['tags']) !== '') {
            $validated['tags'] = array_values(array_filter(array_map('trim', explode(',', $validated['tags'])), fn ($t) => $t !== ''));
        } else {
            $validated['tags'] = null;
        }

        $validated['is_returnable'] = $request->boolean('is_returnable');

        if ($request->has('size_chart')) {
            $sc = $request->input('size_chart');
            $validated['size_chart'] = is_string($sc) ? json_decode($sc, true) : $sc;
        }

        if ($validated['status'] === 'active' && !$product->published_at) {
            $validated['published_at'] = now();
        }

        // Normalize valid variant & food portion rows before stock aggregation
        $cleanVariants = [];
        if ($request->has('variants') && is_array($request->variants)) {
            foreach ($request->variants as $v) {
                if (!is_array($v)) continue;
                $vName = trim((string) ($v['name'] ?? ''));
                $vSku = trim((string) ($v['sku'] ?? ''));
                $vSize = trim((string) ($v['size'] ?? ''));
                $vColor = trim((string) ($v['color'] ?? ''));
                if ($vName === '' && $vSku === '' && $vSize === '' && $vColor === '') {
                    continue;
                }
                if ($vName === '') {
                    $v['name'] = trim(($vColor ? $vColor . ' / ' : '') . ($vSize ?: $vSku)) ?: 'Standard';
                }
                $cleanVariants[] = $v;
            }
        }

        $cleanFoodPortions = [];
        if ($request->has('food_portions') && is_array($request->food_portions)) {
            foreach ($request->food_portions as $fp) {
                if (!is_array($fp)) continue;
                if (trim((string) ($fp['name'] ?? '')) === '') continue;
                $cleanFoodPortions[] = $fp;
            }
        }

        // Accurately calculate total_stock from variants sum if variants provided
        if (count($cleanVariants) > 0) {
            $sumStock = 0;
            foreach ($cleanVariants as $v) {
                $sumStock += (int) ($v['stock'] ?? 0);
            }
            $validated['total_stock'] = $sumStock;
        } elseif (count($cleanFoodPortions) > 0) {
            $sumStock = 0;
            foreach ($cleanFoodPortions as $fp) {
                $sumStock += (int) ($fp['stock'] ?? 50);
            }
            $validated['total_stock'] = $sumStock;
        }

        $productAttributes = collect($validated)->except([
            'images',
            'cover_image_index',
            'video',
            'remove_video',
            'video_url',
            'video_path',
            'variants',
            'food_portions',
            'option_groups',
            'modifiers',
            'gig_packages',
            'b2b_tiers',
            'service_config',
            'specifications',
            'enable_rfq',
        ])->toArray();

        try {
            return \Illuminate\Support\Facades\DB::transaction(function () use ($request, $validated, $productAttributes, $product, $cleanVariants, $cleanFoodPortions) {
                $product->update($productAttributes);

            // 1. Process standard / fashion matrix variants
            if (count($cleanVariants) > 0) {
                $existingVariants = $product->variants()->withTrashed()->get()->keyBy('sku');
                $keptVariantIds = [];

                foreach ($cleanVariants as $index => $variantData) {
                    $rawSku = !empty($variantData['sku']) ? trim((string) $variantData['sku']) : trim((string) $variantData['name']);
                    $sku = Str::limit($rawSku ?: 'VARIANT-' . ($index + 1), 191, '');

                    $baseRupees = round($validated['base_price'] / 100, 2);
                    $priceDecimal = isset($variantData['price']) && $variantData['price'] !== '' ? (float) $variantData['price'] : $baseRupees;
                    if ($priceDecimal <= 0 && $baseRupees > 0) {
                        $priceDecimal = $baseRupees;
                    }
                    $priceOverride = (int) round($priceDecimal * 100);

                    $attributesHash = [];
                    if (!empty($variantData['color'])) $attributesHash['color'] = $variantData['color'];
                    if (!empty($variantData['color_hex'])) $attributesHash['color_hex'] = $variantData['color_hex'];
                    if (!empty($variantData['size'])) $attributesHash['size'] = $variantData['size'];
                    if (empty($attributesHash)) $attributesHash['name'] = $variantData['name'];

                    if (isset($existingVariants[$sku]) && !in_array($existingVariants[$sku]->id, $keptVariantIds, true)) {
                        $variant = $existingVariants[$sku];
                        if ($variant->trashed()) {
                            $variant->restore();
                        }
                        $variant->update([
                            'price' => $priceDecimal,
                            'price_override' => $priceOverride,
                            'stock' => (int) ($variantData['stock'] ?? 0),
                            'stock_quantity' => (int) ($variantData['stock'] ?? 0),
                            'attributes_hash' => $attributesHash,
                            'image_url' => $variantData['image_url'] ?? null,
                            'is_active' => true,
                            'status' => 'active',
                            'sort_order' => $index + 1,
                        ]);
                        $keptVariantIds[] = $variant->id;
                    } else {
                        $createdVariant = $product->variants()->create([
                            'sku' => $sku,
                            'price' => $priceDecimal,
                            'price_override' => $priceOverride,
                            'stock' => (int) ($variantData['stock'] ?? 0),
                            'stock_quantity' => (int) ($variantData['stock'] ?? 0),
                            'attributes_hash' => $attributesHash,
                            'image_url' => $variantData['image_url'] ?? null,
                            'is_active' => true,
                            'status' => 'active',
                            'sort_order' => $index + 1,
                        ]);
                        $keptVariantIds[] = $createdVariant->id;
                    }
                }

                $product->variants()->whereNotIn('id', $keptVariantIds)->delete();
            } elseif (count($cleanFoodPortions) > 0) {
                // 2. Food Portions
                $existingVariants = $product->variants()->withTrashed()->get()->keyBy('sku');
                $keptVariantIds = [];

                foreach ($cleanFoodPortions as $index => $portion) {
                    $portionName = trim((string) $portion['name']);
                    $sku = Str::limit($portionName, 191, '');
                    $priceDecimal = isset($portion['price']) && $portion['price'] !== '' ? (float) $portion['price'] : round($validated['base_price'] / 100, 2);

                    if (isset($existingVariants[$sku]) && !in_array($existingVariants[$sku]->id, $keptVariantIds, true)) {
                        $variant = $existingVariants[$sku];
                        if ($variant->trashed()) $variant->restore();
                        $variant->update([
                            'price' => $priceDecimal,
                            'price_override' => (int) round($priceDecimal * 100),
                            'stock' => (int) ($portion['stock'] ?? 50),
                            'stock_quantity' => (int) ($portion['stock'] ?? 50),
                            'attributes_hash' => ['portion' => $portionName],
                            'is_active' => true,
                            'status' => 'active',
                            'sort_order' => $index + 1,
                        ]);
                        $keptVariantIds[] = $variant->id;
                    } else {
                        $createdVariant = $product->variants()->create([
                            'sku' => $sku,
                            'price' => $priceDecimal,
                            'price_override' => (int) round($priceDecimal * 100),
                            'stock' => (int) ($portion['stock'] ?? 50),
                            'stock_quantity' => (int) ($portion['stock'] ?? 50),
                            'attributes_hash' => ['portion' => $portionName],
                            'is_active' => true,
                            'status' => 'active',
                            'sort_order' => $index + 1,
                        ]);
                        $keptVariantIds[] = $createdVariant->id;
                    }
                }

                $product->variants()->whereNotIn('id', $keptVariantIds)->delete();
            } else {
                // Default single variant
                $existingDefault = $product->variants()->withTrashed()->where('sku', 'Default')->first();
                if ($existingDefault) {
                    if ($existingDefault->trashed()) {
                        $existingDefault->restore();
                    }
                    $existingDefault->update([
                        'stock' => $validated['total_stock'],
                        'stock_quantity' => $validated['total_stock'],
                        'price' => round($validated['base_price'] / 100, 2),
                        'price_override' => $validated['base_price'],
                        'attributes_hash' => ['type' => 'Standard'],
                        'is_active' => true,
                        'status' => 'active',
                        'sort_order' => 1,
                    ]);
                    $product->variants()->where('id', '!=', $existingDefault->id)->delete();
                } else {
                    $product->variants()->delete();
                    $product->variants()->create([
                        'sku' => 'Default',
                        'price' => round($validated['base_price'] / 100, 2),
                        'price_override' => $validated['base_price'],
                        'stock' => $validated['total_stock'],
                        'stock_quantity' => $validated['total_stock'],
                        'attributes_hash' => ['type' => 'Standard'],
                        'is_active' => true,
                        'status' => 'active',
                        'sort_order' => 1,
                    ]);
                }
            }

            // 3. Process Food Option Groups & Modifiers
            $this->syncOptionGroupsAndModifiers($product, $request);

            // 4. Process Digital Gig 3-tier Packages
            if ($request->has('gig_packages') && is_array($request->gig_packages)) {
                foreach (['basic', 'standard', 'premium'] as $tier) {
                    if (isset($request->gig_packages[$tier]) && !empty($request->gig_packages[$tier]['title'])) {
                        $pkg = $request->gig_packages[$tier];
                        $product->gigPackages()->updateOrCreate(
                            ['tier' => $tier],
                            [
                                'title' => $pkg['title'],
                                'description' => $pkg['description'] ?? '',
                                'delivery_days' => (int)($pkg['delivery_days'] ?? 3),
                                'revisions' => (int)($pkg['revisions'] ?? 1),
                                'price' => (float)($pkg['price'] ?? ($tier === 'basic' ? 499 : ($tier === 'standard' ? 1499 : 3499))),
                                'features_json' => $pkg['features'] ?? [],
                            ]
                        );
                    }
                }
            }

            // Update has_variants flag
            $hasMultiple = ($product->variants()->count() > 1 && $product->variants()->first()?->sku !== 'Default')
                || $product->optionGroups()->exists()
                || $product->modifiers()->exists()
                || $product->gigPackages()->exists();

            $product->update(['has_variants' => $hasMultiple]);

            // 1. Delete removed existing images
            if ($request->filled('deleted_image_ids') && is_array($request->deleted_image_ids)) {
                $toDelete = $product->images()->whereIn('id', $request->deleted_image_ids)->get();
                foreach ($toDelete as $img) {
                    k71_delete_public_storage($img->path);
                    $img->delete();
                }
            }

            // 2. Upload new images
            $newImagesMap = [];
            if ($request->hasFile('images')) {
                $uploadedFiles = array_values(array_filter($request->file('images') ?? [], fn ($f) => $f && $f->isValid()));
                foreach ($uploadedFiles as $index => $image) {
                    $path = $image->store('products', 'public');
                    k71_sync_public_storage($path);
                    $createdImg = $product->images()->create([
                        'path' => $path,
                        'alt_text' => $product->title,
                        'sort_order' => 999 + $index,
                    ]);
                    $newImagesMap[$index] = $createdImg->id;
                }
            }

            // 3. Apply custom reordered sequence (from vendor's drag / move / cover actions)
            if ($request->filled('overall_image_order') && is_array($request->overall_image_order)) {
                foreach ($request->overall_image_order as $sortIdx => $token) {
                    if (str_starts_with($token, 'existing:')) {
                        $imgId = (int) str_replace('existing:', '', $token);
                        $product->images()->where('id', $imgId)->update(['sort_order' => $sortIdx]);
                    } elseif (str_starts_with($token, 'new:')) {
                        $newIdx = (int) str_replace('new:', '', $token);
                        if (isset($newImagesMap[$newIdx])) {
                            $product->images()->where('id', $newImagesMap[$newIdx])->update(['sort_order' => $sortIdx]);
                        }
                    }
                }
            }

            // 4. Process product video (optional)
            $hasVideoUrl = \Illuminate\Support\Facades\Schema::hasColumn('products', 'video_url');
            $hasVideoPath = \Illuminate\Support\Facades\Schema::hasColumn('products', 'video_path');
            if ($request->has('remove_video') && $request->boolean('remove_video')) {
                if ($hasVideoPath && $product->video_path) {
                    k71_delete_public_storage($product->video_path);
                }
                $updates = [];
                if ($hasVideoPath) $updates['video_path'] = null;
                if ($hasVideoUrl) $updates['video_url'] = null;
                if (!empty($updates)) {
                    $product->update($updates);
                }
            } else {
                if ($request->hasFile('video') && $request->file('video')->isValid()) {
                    if ($hasVideoPath && $product->video_path) {
                        k71_delete_public_storage($product->video_path);
                    }
                    $videoPath = $request->file('video')->store('products/videos', 'public');
                    k71_sync_public_storage($videoPath);
                    if ($hasVideoPath) {
                        $product->update(['video_path' => $videoPath]);
                    }
                }
                if ($request->has('video_url') && $hasVideoUrl) {
                    $product->update(['video_url' => trim($request->input('video_url')) ?: null]);
                }
            }

            \App\Http\Controllers\SitemapController::clearCache();

            return redirect()->route('vendor.products.index')->with('success', 'Product updated successfully.');
        });
    } catch (\Throwable $e) {
        \Illuminate\Support\Facades\Log::error('Product update failed: ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString(),
            'product_id' => $product->id,
        ]);
        return back()->withInput()->with('error', 'Unable to update product: ' . $e->getMessage());
    }
}

    public function destroy(Product $product)
    {
        $vendor = auth()->user()->vendor;
        if (!$vendor || $product->vendor_id !== $vendor->id) {
            abort(403, 'Unauthorized access to product.');
        }

        try {
            $productTitle = $product->title;
            $hasReferences = \App\Models\OrderItem::where('product_id', $product->id)->exists()
                || \App\Models\Booking::where('product_id', $product->id)->exists()
                || \App\Models\ServiceOrder::where('product_id', $product->id)->exists()
                || \App\Models\Bid::where('product_id', $product->id)->exists();

            if ($hasReferences) {
                $product->update([
                    'status' => 'archived',
                    'total_stock' => 0,
                ]);
                \App\Http\Controllers\SitemapController::clearCache();
                return redirect()->route('vendor.products.index')->with('success', "Product '{$productTitle}' has linked orders/bookings and was archived safely to preserve transaction records.");
            }

            // Cleanly delete associated variants, add-ons, gig packages, images, and permanently remove product from site
            $product->modifiers()->delete();
            $product->optionGroups()->delete();
            $product->gigPackages()->delete();
            $product->variants()->forceDelete();
            if (method_exists($product, 'images')) {
                foreach ($product->images as $img) {
                    if ($img->path) {
                        k71_delete_public_storage($img->path);
                    }
                }
                $product->images()->delete();
            }
            if ($product->video_path) {
                k71_delete_public_storage($product->video_path);
            }
            $product->forceDelete();
            \App\Http\Controllers\SitemapController::clearCache();
            return redirect()->route('vendor.products.index')->with('success', "Product '{$productTitle}' has been completely removed from site.");
        } catch (\Throwable $e) {
            return redirect()->route('vendor.products.index')->with('error', 'Could not delete product: ' . $e->getMessage());
        }
    }

    public function toggleStatus(Product $product)
    {
        $vendor = auth()->user()->vendor;
        if (!$vendor || $product->vendor_id !== $vendor->id) {
            abort(403, 'Unauthorized access to product.');
        }

        $newStatus = $product->status === 'active' ? 'draft' : 'active';
        $sector = $product->vertical ?: $vendor->getCanonicalSectorKey();
        $entitlementCheck = app(\App\Services\Monetization\EntitlementService::class)->validateListingWrite(
            $vendor,
            $sector,
            $newStatus === 'active',
            0,
            0,
            $product->id
        );
        if (!$entitlementCheck['allowed']) {
            return back()->with('error', $entitlementCheck['reason']);
        }

        $product->update([
            'status' => $newStatus,
            'published_at' => $newStatus === 'active' ? ($product->published_at ?? now()) : $product->published_at,
        ]);
        \App\Http\Controllers\SitemapController::clearCache();

        $label = $newStatus === 'active' ? 'Published live' : 'Moved to draft (Hidden from store)';
        return back()->with('success', "Product status changed: {$label}.");
    }

    public function duplicate(Product $product)
    {
        $vendor = auth()->user()->vendor;
        if (!$vendor || $product->vendor_id !== $vendor->id) {
            abort(403, 'Unauthorized access to product.');
        }

        $sectorKey = app(\App\Services\Monetization\SectorRegistryService::class)->resolveProductSector($product);
        $dupFeature = $sectorKey === 'used' ? 'allow_used_renewal' : null;
        $dupAuth = app(\App\Services\Monetization\EntitlementService::class)->authorizeSectorWrite($vendor, $sectorKey, $dupFeature);
        if (!$dupAuth['allowed']) {
            return back()->with('error', $dupAuth['reason']);
        }

        $clone = DB::transaction(function () use ($product, $vendor) {
            $clone = $product->replicate([
                'slug',
                'created_at',
                'updated_at',
            ]);

            $clone->title = $product->title . ' (Copy)';

            $baseSlug = Str::slug($clone->title) ?: 'product-copy';
            $slug = $baseSlug;
            $counter = 1;
            while (Product::withoutGlobalScopes()->where('slug', $slug)->exists()) {
                $slug = $baseSlug . '-' . $counter++;
            }
            $clone->slug = $slug;
            $clone->status = 'draft';
            $clone->published_at = null;

            if (!empty($product->sku)) {
                $clone->sku = $this->generateUniqueCopySku($product->sku, true);
            }

            $clone->save();

            foreach ($product->variants as $variant) {
                $variantClone = $variant->replicate([
                    'product_id',
                    'created_at',
                    'updated_at',
                ]);
                $variantClone->product_id = $clone->id;
                if (!empty($variantClone->sku)) {
                    $variantClone->sku = $this->generateUniqueCopySku($variantClone->sku, false);
                }
                $variantClone->save();
            }

            foreach ($product->images as $img) {
                $imgClone = $img->replicate([
                    'product_id',
                    'created_at',
                    'updated_at',
                ]);
                $imgClone->product_id = $clone->id;
                $imgClone->save();
            }

            $groupIdMap = [];
            foreach ($product->optionGroups()->with('modifiers')->get() as $group) {
                $groupClone = $group->replicate([
                    'product_id',
                    'created_at',
                    'updated_at',
                ]);
                $groupClone->product_id = $clone->id;
                $groupClone->save();
                $groupIdMap[$group->id] = $groupClone->id;
            }

            foreach ($product->modifiers as $modifier) {
                $modClone = $modifier->replicate([
                    'product_id',
                    'option_group_id',
                    'created_at',
                    'updated_at',
                ]);
                $modClone->product_id = $clone->id;
                $modClone->option_group_id = $modifier->option_group_id
                    ? ($groupIdMap[$modifier->option_group_id] ?? null)
                    : null;
                $modClone->save();
            }

            foreach ($product->gigPackages as $pkg) {
                $pkgClone = $pkg->replicate([
                    'product_id',
                    'created_at',
                    'updated_at',
                ]);
                $pkgClone->product_id = $clone->id;
                $pkgClone->save();
            }

            return $clone;
        });

        return redirect()->route('vendor.products.edit', $clone)
            ->with('success', "Product '{$product->title}' duplicated successfully as Draft! You can now update images or details and publish.");
    }

    protected function generateUniqueCopySku(string $sourceSku, bool $isProductSku): string
    {
        $cleanBase = preg_replace('/-COPY(?:-\d+)?$/', '', trim($sourceSku)) ?: 'ITEM';
        $attempt = 0;

        do {
            $suffix = $attempt === 0 ? '-COPY' : '-COPY-' . ($attempt + 1);
            $maxPrefixLen = max(1, 100 - strlen($suffix));
            $candidate = Str::limit($cleanBase, $maxPrefixLen, '') . $suffix;
            $exists = $isProductSku
                ? Product::withoutGlobalScopes()->where('sku', $candidate)->exists()
                : ProductVariant::where('sku', $candidate)->exists();
            $attempt++;
        } while ($exists && $attempt < 50);

        if ($exists) {
            $randSuffix = '-COPY-' . strtoupper(Str::random(4));
            $candidate = Str::limit($cleanBase, max(1, 100 - strlen($randSuffix)), '') . $randSuffix;
        }

        return $candidate;
    }

    public function toggleAvailability(Request $request, Product $product)
    {
        $vendor = auth()->user()->vendor;
        if (!$vendor || $product->vendor_id !== $vendor->id) {
            abort(403, 'Unauthorized access to product.');
        }

        $sector = $product->vertical ?: $vendor->getCanonicalSectorKey();
        $authCheck = app(\App\Services\Monetization\EntitlementService::class)->authorizeSectorWrite($vendor, $sector);
        if (!$authCheck['allowed']) {
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $authCheck['reason']], 403);
            }
            return back()->with('error', $authCheck['reason']);
        }

        $newState = !$product->is_dish_available;
        $product->update(['is_dish_available' => $newState]);

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'is_dish_available' => $newState,
            ]);
        }

        return back()->with('success', $newState
            ? "'{$product->title}' is now marked Available for ordering."
            : "'{$product->title}' has been marked Sold Out / Unavailable.");
    }

    protected function syncOptionGroupsAndModifiers(Product $product, Request $request): void
    {
        $hasGroupsPayload = $request->boolean('option_groups_submitted')
            || ($request->has('option_groups') && is_array($request->option_groups));

        if ($hasGroupsPayload) {
            $product->modifiers()->delete();
            $product->optionGroups()->delete();

            foreach ((array) $request->input('option_groups', []) as $gIdx => $groupData) {
                if (!is_array($groupData) || empty(trim((string) ($groupData['name'] ?? '')))) {
                    continue;
                }

                $selectionType = ($groupData['selection_type'] ?? 'multi') === 'single' ? 'single' : 'multi';
                $isRequired = !empty($groupData['is_required']);
                $minSel = isset($groupData['min_selections']) && $groupData['min_selections'] !== ''
                    ? (int) $groupData['min_selections']
                    : ($isRequired ? 1 : 0);
                $maxSel = isset($groupData['max_selections']) && $groupData['max_selections'] !== ''
                    ? (int) $groupData['max_selections']
                    : ($selectionType === 'single' ? 1 : null);

                $group = ProductOptionGroup::create([
                    'product_id' => $product->id,
                    'name' => trim((string) $groupData['name']),
                    'selection_type' => $selectionType,
                    'is_required' => $isRequired,
                    'min_selections' => $minSel,
                    'max_selections' => $maxSel,
                    'sort_order' => (int) ($groupData['sort_order'] ?? ($gIdx + 1)),
                    'is_active' => true,
                ]);

                $options = $groupData['options'] ?? $groupData['modifiers'] ?? [];
                foreach ((array) $options as $oIdx => $optData) {
                    if (!is_array($optData) || empty(trim((string) ($optData['modifier_name'] ?? $optData['name'] ?? '')))) {
                        continue;
                    }
                    $product->modifiers()->create([
                        'option_group_id' => $group->id,
                        'group_name' => $group->name,
                        'modifier_name' => trim((string) ($optData['modifier_name'] ?? $optData['name'])),
                        'price_delta' => (float) ($optData['price_delta'] ?? 0),
                        'is_required' => $isRequired,
                        'is_available' => array_key_exists('is_available', $optData) ? (bool) $optData['is_available'] : true,
                        'is_default' => !empty($optData['is_default']),
                        'sort_order' => (int) ($optData['sort_order'] ?? ($oIdx + 1)),
                    ]);
                }
            }
            return;
        }

        if ($request->boolean('modifiers_submitted') || ($request->has('modifiers') && is_array($request->modifiers))) {
            $product->modifiers()->delete();
            foreach ((array) $request->input('modifiers', []) as $oIdx => $mod) {
                if (is_array($mod) && !empty($mod['modifier_name'])) {
                    $product->modifiers()->create([
                        'group_name' => $mod['group_name'] ?? 'Add-ons',
                        'modifier_name' => $mod['modifier_name'],
                        'price_delta' => (float) ($mod['price_delta'] ?? 0),
                        'is_required' => !empty($mod['is_required']),
                        'is_available' => array_key_exists('is_available', $mod) ? (bool) $mod['is_available'] : true,
                        'is_default' => !empty($mod['is_default']),
                        'sort_order' => $oIdx + 1,
                    ]);
                }
            }
        }
    }

    /**
     * AJAX endpoint to retrieve dynamic category fields for selected vertical/category.
     */
    public function getCategoryFields(Request $request)
    {
        $vertical = $request->input('vertical', 'marketplace');
        $categoryId = $request->has('category_id') ? (int) $request->input('category_id') : null;
        $fields = CategoryField::getFieldsFor($vertical, $categoryId);
        return response()->json([
            'success' => true,
            'fields' => $fields,
        ]);
    }
}
