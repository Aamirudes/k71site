<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\CategoryField;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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

    public function index()
    {
        $vendor = auth()->user()->vendor;
        if (!$vendor) abort(403);

        $targetVertical = $vendor->getVerticalKey();
        $products = Product::where('vendor_id', $vendor->id)->with('category')->latest()->paginate(15);
        return view('vendor.products.index', compact('products', 'vendor', 'targetVertical'));
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

        return view('vendor.products.create', compact('vendor', 'vendorType', 'sectorConfig', 'targetVertical', 'categories', 'verticals', 'listingTypes', 'categoryFields'));
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
            case 'services':
            case 'local_services':
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
        $targetVertical = $vendor ? $vendor->getVerticalKey() : 'marketplace';

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'category_id' => ['required', 'exists:categories,id'],
            'vertical' => ['nullable', 'string'],
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
            'sku' => ['nullable', 'string', 'max:50'],
            'total_stock' => ['required', 'integer', 'min:0'],
            'status' => ['required', 'in:draft,active,archived'],
            'variants' => ['nullable', 'array'],
            'variants.*.name' => ['required_with:variants', 'string', 'max:255'],
            'variants.*.price' => ['nullable', 'numeric', 'min:0'],
            'variants.*.stock' => ['nullable', 'integer', 'min:0'],
            'variants.*.sku' => ['nullable', 'string', 'max:100'],
            'variants.*.color' => ['nullable', 'string', 'max:100'],
            'variants.*.color_hex' => ['nullable', 'string', 'max:20'],
            'variants.*.size' => ['nullable', 'string', 'max:50'],
            'variants.*.image_url' => ['nullable', 'string', 'max:500'],
            'food_portions' => ['nullable', 'array'],
            'modifiers' => ['nullable', 'array'],
            'gig_packages' => ['nullable', 'array'],
            'b2b_tiers' => ['nullable', 'array'],
            'service_config' => ['nullable', 'array'],
            'specifications' => ['nullable', 'array'],
            'enable_rfq' => ['nullable'],
            'images.*' => ['image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'cover_image_index' => ['nullable', 'integer', 'min:0'],
            'video' => ['nullable', 'file', 'mimes:mp4,webm,mov,ogg,m4v,qt,avi', 'max:51200'],
            'video_url' => ['nullable', 'string', 'max:500'],
        ]);

        // Enforce strict vertical isolation from vendor profile with request input
        $targetVertical = $request->input('vertical') ?: ($vendor ? $vendor->getVerticalKey() : 'marketplace');
        $validated['vertical'] = $targetVertical;

        if ($targetVertical === 'services') {
            $validated['listing_type'] = $validated['listing_type'] ?? 'booking';
        } elseif ($targetVertical === 'used') {
            $validated['listing_type'] = $validated['listing_type'] ?? 'contact_seller';
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

        if ($request->has('specifications') && is_array($request->specifications)) {
            $validated['specifications_json'] = $request->specifications;
        }

        if ($request->has('b2b_tiers') && is_array($request->b2b_tiers)) {
            $validated['b2b_tiers_json'] = array_values(array_filter($request->b2b_tiers, fn($t) => !empty($t['min_qty'])));
        }

        if ($request->has('service_config') && is_array($request->service_config)) {
            $validated['service_config_json'] = $request->service_config;
        }

        $baseSlug = Str::slug($validated['title']);
        $slug = $baseSlug;
        $counter = 1;
        while (Product::withoutGlobalScopes()->where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter++;
        }

        $validated['slug'] = $slug;
        $validated['base_price'] = (int) ($validated['base_price'] * 100); // convert to paise
        if (isset($validated['compare_at_price'])) {
            $validated['compare_at_price'] = (int) ($validated['compare_at_price'] * 100);
        }
        
        if (isset($validated['tags']) && !empty($validated['tags'])) {
            $tagsArray = array_map('trim', explode(',', $validated['tags']));
            $validated['tags'] = json_encode($tagsArray);
        }

        $validated['is_returnable'] = $request->has('is_returnable');

        if ($request->filled('size_chart')) {
            $sc = $request->input('size_chart');
            $validated['size_chart'] = is_string($sc) ? json_decode($sc, true) : $sc;
        } else {
            $validated['size_chart'] = null;
        }

        if ($validated['status'] === 'active') {
            $validated['published_at'] = now();
        }

        // Accurately calculate total_stock from variants sum if variants provided
        if ($request->has('variants') && is_array($request->variants) && count($request->variants) > 0) {
            $sumStock = 0;
            foreach ($request->variants as $v) {
                $sumStock += (int) ($v['stock'] ?? 0);
            }
            $validated['total_stock'] = $sumStock;
        } elseif ($request->has('food_portions') && is_array($request->food_portions) && count($request->food_portions) > 0) {
            $sumStock = 0;
            foreach ($request->food_portions as $fp) {
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
            'modifiers',
            'gig_packages',
            'b2b_tiers',
            'service_config',
            'specifications',
            'enable_rfq',
        ])->toArray();

        $productAttributes['vendor_id'] = $vendor->id;

        try {
            return \Illuminate\Support\Facades\DB::transaction(function () use ($request, $validated, $productAttributes, $vendor) {
                $product = Product::create($productAttributes);

            // 1. Process standard / fashion matrix variants
            if ($request->has('variants') && is_array($request->variants) && count($request->variants) > 0) {
                foreach ($request->variants as $index => $variantData) {
                    $sku = !empty($variantData['sku']) ? $variantData['sku'] : $variantData['name'];
                    
                    $baseRupees = round($validated['base_price'] / 100, 2);
                    $priceDecimal = !empty($variantData['price']) ? (float)$variantData['price'] : $baseRupees;
                    if ($priceDecimal <= 0) {
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
            } elseif ($request->has('food_portions') && is_array($request->food_portions) && count($request->food_portions) > 0) {
                // 2. Food Portions as variants
                foreach ($request->food_portions as $index => $portion) {
                    if (empty($portion['name'])) continue;
                    $priceDecimal = !empty($portion['price']) ? (float)$portion['price'] : ($validated['base_price'] / 100);
                    $product->variants()->create([
                        'sku' => $portion['name'],
                        'price_override' => (int) ($priceDecimal * 100),
                        'price' => $priceDecimal,
                        'stock' => (int) ($portion['stock'] ?? 50),
                        'stock_quantity' => (int) ($portion['stock'] ?? 50),
                        'attributes_hash' => ['portion' => $portion['name']],
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

            // 3. Process Food Add-ons / Modifiers
            if ($request->has('modifiers') && is_array($request->modifiers)) {
                foreach ($request->modifiers as $mod) {
                    if (!empty($mod['modifier_name'])) {
                        $product->modifiers()->create([
                            'group_name' => $mod['group_name'] ?? 'Add-ons',
                            'modifier_name' => $mod['modifier_name'],
                            'price_delta' => (float)($mod['price_delta'] ?? 0),
                            'is_required' => !empty($mod['is_required']),
                        ]);
                    }
                }
            }

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
                || $product->modifiers()->exists()
                || $product->gigPackages()->exists();

            $product->update(['has_variants' => $hasMultiple]);

            // Process optional product video
            if ($request->hasFile('video')) {
                $videoPath = $request->file('video')->store('products/videos', 'public');
                $targetVideo = public_path('storage/' . $videoPath);
                @mkdir(dirname($targetVideo), 0755, true);
                @copy(storage_path('app/public/' . $videoPath), $targetVideo);
                if (\Illuminate\Support\Facades\Schema::hasColumn('products', 'video_path')) {
                    $product->update(['video_path' => $videoPath]);
                }
            } elseif ($request->filled('video_url')) {
                if (\Illuminate\Support\Facades\Schema::hasColumn('products', 'video_url')) {
                    $product->update(['video_url' => trim($request->input('video_url'))]);
                }
            }

            if ($request->hasFile('images')) {
                $uploadedFiles = $request->file('images');
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
                    $target = public_path('storage/' . $path);
                    @mkdir(dirname($target), 0755, true);
                    @copy(storage_path('app/public/' . $path), $target);
                    $product->images()->create([
                        'path' => $path,
                        'alt_text' => $product->title,
                        'sort_order' => $sortOrder,
                    ]);
                }
            }

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

        $vendorType = $vendor?->vendor_type ?? 'clothing';
        $sectorConfig = $vendor ? $vendor->getSectorConfig() : [];
        $targetVertical = $product->vertical ?: ($vendor ? $vendor->getVerticalKey() : 'marketplace');

        $categories = $this->resolveVendorCategories($vendorType, $targetVertical);

        $product->load(['variants', 'images', 'modifiers', 'gigPackages', 'category']);

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
        ])->toArray();

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
            'vendor', 'targetVertical', 'product', 'categories', 'existingVariants',
            'existingModifiers', 'existingGigPackages', 'existingB2bTiers',
            'existingServiceConfig', 'existingSpecs', 'verticals', 'listingTypes', 'categoryFields', 'sectorConfig'
        ));
    }

    public function update(Request $request, Product $product)
    {
        $vendor = auth()->user()->vendor;
        if (!$vendor || $product->vendor_id !== $vendor->id) {
            abort(403, 'Unauthorized access to product.');
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'category_id' => ['required', 'exists:categories,id'],
            'vertical' => ['nullable', 'string', 'in:marketplace,food,services,used,enquiry'],
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
            'sku' => ['nullable', 'string', 'max:50'],
            'total_stock' => ['required', 'integer', 'min:0'],
            'status' => ['required', 'in:draft,active,archived'],
            'variants' => ['nullable', 'array'],
            'variants.*.name' => ['required_with:variants', 'string', 'max:255'],
            'variants.*.price' => ['nullable', 'numeric', 'min:0'],
            'variants.*.stock' => ['nullable', 'integer', 'min:0'],
            'variants.*.sku' => ['nullable', 'string', 'max:100'],
            'variants.*.color' => ['nullable', 'string', 'max:100'],
            'variants.*.color_hex' => ['nullable', 'string', 'max:20'],
            'variants.*.size' => ['nullable', 'string', 'max:50'],
            'variants.*.image_url' => ['nullable', 'string', 'max:500'],
            'food_portions' => ['nullable', 'array'],
            'modifiers' => ['nullable', 'array'],
            'gig_packages' => ['nullable', 'array'],
            'b2b_tiers' => ['nullable', 'array'],
            'service_config' => ['nullable', 'array'],
            'specifications' => ['nullable', 'array'],
            'enable_rfq' => ['nullable'],
            'images.*' => ['image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'cover_image_index' => ['nullable', 'integer', 'min:0'],
            'video' => ['nullable', 'file', 'mimes:mp4,webm,mov,ogg,m4v,qt,avi', 'max:51200'],
            'video_url' => ['nullable', 'string', 'max:500'],
            'remove_video' => ['nullable', 'boolean'],
        ]);

        // Enforce strict vertical isolation from vendor profile with request input
        $targetVertical = $request->input('vertical') ?: ($vendor ? $vendor->getVerticalKey() : 'marketplace');
        $validated['vertical'] = $targetVertical;

        if ($targetVertical === 'services') {
            $validated['listing_type'] = $validated['listing_type'] ?? 'booking';
        } elseif ($targetVertical === 'used') {
            $validated['listing_type'] = $validated['listing_type'] ?? 'contact_seller';
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

        if ($request->has('specifications') && is_array($request->specifications)) {
            $validated['specifications_json'] = $request->specifications;
        }

        if ($request->has('b2b_tiers') && is_array($request->b2b_tiers)) {
            $validated['b2b_tiers_json'] = array_values(array_filter($request->b2b_tiers, fn($t) => !empty($t['min_qty'])));
        }

        if ($request->has('service_config') && is_array($request->service_config)) {
            $validated['service_config_json'] = $request->service_config;
        }

        $validated['base_price'] = (int) ($validated['base_price'] * 100);
        if (isset($validated['compare_at_price'])) {
            $validated['compare_at_price'] = (int) ($validated['compare_at_price'] * 100);
        }

        if (isset($validated['tags']) && !empty($validated['tags'])) {
            $tagsArray = array_map('trim', explode(',', $validated['tags']));
            $validated['tags'] = json_encode($tagsArray);
        }

        $validated['is_returnable'] = $request->has('is_returnable');

        if ($request->has('size_chart')) {
            $sc = $request->input('size_chart');
            $validated['size_chart'] = is_string($sc) ? json_decode($sc, true) : $sc;
        }

        if ($validated['status'] === 'active' && !$product->published_at) {
            $validated['published_at'] = now();
        }

        // Accurately calculate total_stock from variants sum if variants provided
        if ($request->has('variants') && is_array($request->variants) && count($request->variants) > 0) {
            $sumStock = 0;
            foreach ($request->variants as $v) {
                $sumStock += (int) ($v['stock'] ?? 0);
            }
            $validated['total_stock'] = $sumStock;
        } elseif ($request->has('food_portions') && is_array($request->food_portions) && count($request->food_portions) > 0) {
            $sumStock = 0;
            foreach ($request->food_portions as $fp) {
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
            'modifiers',
            'gig_packages',
            'b2b_tiers',
            'service_config',
            'specifications',
            'enable_rfq',
        ])->toArray();

        try {
            return \Illuminate\Support\Facades\DB::transaction(function () use ($request, $validated, $productAttributes, $product) {
                $product->update($productAttributes);

            // 1. Process standard / fashion matrix variants
            if ($request->has('variants') && is_array($request->variants) && count($request->variants) > 0) {
                $existingVariants = $product->variants()->withTrashed()->get()->keyBy('sku');
                $activeSkus = [];

                foreach ($request->variants as $index => $variantData) {
                    $sku = !empty($variantData['sku']) ? $variantData['sku'] : $variantData['name'];
                    $activeSkus[] = $sku;
                    
                    $baseRupees = round($validated['base_price'] / 100, 2);
                    $priceDecimal = !empty($variantData['price']) ? (float)$variantData['price'] : $baseRupees;
                    if ($priceDecimal <= 0) {
                        $priceDecimal = $baseRupees;
                    }
                    $priceOverride = (int) round($priceDecimal * 100);

                    $attributesHash = [];
                    if (!empty($variantData['color'])) $attributesHash['color'] = $variantData['color'];
                    if (!empty($variantData['color_hex'])) $attributesHash['color_hex'] = $variantData['color_hex'];
                    if (!empty($variantData['size'])) $attributesHash['size'] = $variantData['size'];
                    if (empty($attributesHash)) $attributesHash['name'] = $variantData['name'];

                    if (isset($existingVariants[$sku])) {
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
                    } else {
                        $product->variants()->create([
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
                    }
                }

                $product->variants()->whereNotIn('sku', $activeSkus)->delete();
            } elseif ($request->has('food_portions') && is_array($request->food_portions) && count($request->food_portions) > 0) {
                // 2. Food Portions
                $existingVariants = $product->variants()->withTrashed()->get()->keyBy('sku');
                $activeSkus = [];

                foreach ($request->food_portions as $index => $portion) {
                    if (empty($portion['name'])) continue;
                    $sku = $portion['name'];
                    $activeSkus[] = $sku;
                    $priceDecimal = !empty($portion['price']) ? (float)$portion['price'] : ($validated['base_price'] / 100);

                    if (isset($existingVariants[$sku])) {
                        $variant = $existingVariants[$sku];
                        if ($variant->trashed()) $variant->restore();
                        $variant->update([
                            'price' => $priceDecimal,
                            'price_override' => (int) ($priceDecimal * 100),
                            'stock' => (int) ($portion['stock'] ?? 50),
                            'stock_quantity' => (int) ($portion['stock'] ?? 50),
                            'attributes_hash' => ['portion' => $portion['name']],
                            'is_active' => true,
                            'status' => 'active',
                            'sort_order' => $index + 1,
                        ]);
                    } else {
                        $product->variants()->create([
                            'sku' => $sku,
                            'price' => $priceDecimal,
                            'price_override' => (int) ($priceDecimal * 100),
                            'stock' => (int) ($portion['stock'] ?? 50),
                            'stock_quantity' => (int) ($portion['stock'] ?? 50),
                            'attributes_hash' => ['portion' => $portion['name']],
                            'is_active' => true,
                            'status' => 'active',
                            'sort_order' => $index + 1,
                        ]);
                    }
                }

                $product->variants()->whereNotIn('sku', $activeSkus)->delete();
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
                        'price_override' => null,
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

            // 3. Process Modifiers
            if ($request->has('modifiers') && is_array($request->modifiers)) {
                $product->modifiers()->delete();
                foreach ($request->modifiers as $mod) {
                    if (!empty($mod['modifier_name'])) {
                        $product->modifiers()->create([
                            'group_name' => $mod['group_name'] ?? 'Add-ons',
                            'modifier_name' => $mod['modifier_name'],
                            'price_delta' => (float)($mod['price_delta'] ?? 0),
                            'is_required' => !empty($mod['is_required']),
                        ]);
                    }
                }
            }

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
                || $product->modifiers()->exists()
                || $product->gigPackages()->exists();

            $product->update(['has_variants' => $hasMultiple]);

            // 1. Delete removed existing images
            if ($request->filled('deleted_image_ids') && is_array($request->deleted_image_ids)) {
                $toDelete = $product->images()->whereIn('id', $request->deleted_image_ids)->get();
                foreach ($toDelete as $img) {
                    @unlink(storage_path('app/public/' . $img->path));
                    @unlink(public_path('storage/' . $img->path));
                    $img->delete();
                }
            }

            // 2. Upload new images
            $newImagesMap = [];
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $index => $image) {
                    $path = $image->store('products', 'public');
                    $target = public_path('storage/' . $path);
                    @mkdir(dirname($target), 0755, true);
                    @copy(storage_path('app/public/' . $path), $target);
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
                    @unlink(storage_path('app/public/' . $product->video_path));
                    @unlink(public_path('storage/' . $product->video_path));
                }
                $updates = [];
                if ($hasVideoPath) $updates['video_path'] = null;
                if ($hasVideoUrl) $updates['video_url'] = null;
                if (!empty($updates)) {
                    $product->update($updates);
                }
            } else {
                if ($request->hasFile('video')) {
                    if ($hasVideoPath && $product->video_path) {
                        @unlink(storage_path('app/public/' . $product->video_path));
                        @unlink(public_path('storage/' . $product->video_path));
                    }
                    $videoPath = $request->file('video')->store('products/videos', 'public');
                    $targetVideo = public_path('storage/' . $videoPath);
                    @mkdir(dirname($targetVideo), 0755, true);
                    @copy(storage_path('app/public/' . $videoPath), $targetVideo);
                    if ($hasVideoPath) {
                        $product->update(['video_path' => $videoPath]);
                    }
                }
                if ($request->has('video_url') && $hasVideoUrl) {
                    $product->update(['video_url' => trim($request->input('video_url')) ?: null]);
                }
            }

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
            // Cleanly delete associated variants, images, and permanently remove product from site
            $product->variants()->forceDelete();
            if (method_exists($product, 'images')) {
                foreach ($product->images as $img) {
                    if ($img->path) {
                        @unlink(storage_path('app/public/' . $img->path));
                        @unlink(public_path('storage/' . $img->path));
                    }
                }
                $product->images()->delete();
            }
            if ($product->video_path) {
                @unlink(storage_path('app/public/' . $product->video_path));
                @unlink(public_path('storage/' . $product->video_path));
            }
            $product->forceDelete();
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
        $product->update([
            'status' => $newStatus,
            'published_at' => $newStatus === 'active' ? ($product->published_at ?? now()) : $product->published_at,
        ]);

        $label = $newStatus === 'active' ? 'Published live' : 'Moved to draft (Hidden from store)';
        return back()->with('success', "Product status changed: {$label}.");
    }

    public function duplicate(Product $product)
    {
        $vendor = auth()->user()->vendor;
        if (!$vendor || $product->vendor_id !== $vendor->id) {
            abort(403, 'Unauthorized access to product.');
        }

        // Replicate product model
        $clone = $product->replicate([
            'slug',
            'created_at',
            'updated_at',
        ]);

        $clone->title = $product->title . ' (Copy)';
        
        $baseSlug = Str::slug($clone->title);
        $slug = $baseSlug;
        $counter = 1;
        while (Product::withoutGlobalScopes()->where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter++;
        }
        $clone->slug = $slug;
        $clone->status = 'draft';
        $clone->published_at = null;
        $clone->save();

        // Replicate variants
        foreach ($product->variants as $variant) {
            $variantClone = $variant->replicate([
                'product_id',
                'created_at',
                'updated_at',
            ]);
            $variantClone->product_id = $clone->id;
            if ($variantClone->sku) {
                $variantClone->sku = $variantClone->sku . '-COPY';
            }
            $variantClone->save();
        }

        // Replicate images (pointing to same storage path)
        foreach ($product->images as $img) {
            $imgClone = $img->replicate([
                'product_id',
                'created_at',
                'updated_at',
            ]);
            $imgClone->product_id = $clone->id;
            $imgClone->save();
        }

        return redirect()->route('vendor.products.edit', $clone)
            ->with('success', "Product '{$product->title}' duplicated successfully as Draft! You can now update images or details and publish.");
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
