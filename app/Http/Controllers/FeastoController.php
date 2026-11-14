<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class FeastoController extends Controller
{
    /**
     * Feasto 10-Minute Hyperlocal Quick-Commerce Hub (Blinkit Style Architecture)
     * Exclusively Real Authenticated Products from Verified Kitchens & Stores
     */
    public function index(Request $request)
    {
        // 1. Fetch real active products from visible verified kitchens/stores
        $feastoQuery = Product::where('status', 'active')
            ->where(function ($q) {
                $q->whereIn('vertical', ['food', 'groceries', 'feasto'])
                  ->orWhereHas('vendor', function ($vq) {
                      $vq->whereIn('vendor_type', ['food', 'feasto', 'groceries', 'kirana']);
                  });
            });
        app(\App\Services\Monetization\VisibilityService::class)->scopeVisibleProducts($feastoQuery, 'food');
        $dbProducts = $feastoQuery
            ->with(['category', 'vendor.settings', 'images', 'variants'])
            ->latest()
            ->get();

        $foodService = app(\App\Services\Food\FoodMarketplaceService::class);
        $vendorStatusCache = [];

        // 2. Format real items with reliable schema & real availability
        $allProducts = collect();
        $categorySlugsPresent = [];

        foreach ($dbProducts as $dp) {
            $isFoodItem = ($dp->vertical === 'food')
                || ($dp->vendor?->isFood() ?? false)
                || ($dp->vendor?->settings?->is_restaurant ?? false);

            $vendorOpen = true;
            if ($dp->vendor) {
                $vId = (int) $dp->vendor->id;
                if (!isset($vendorStatusCache[$vId])) {
                    $vendorStatusCache[$vId] = $foodService->getRestaurantOperationalStatus($dp->vendor);
                }
                if ($isFoodItem && !$vendorStatusCache[$vId]['accepting_orders']) {
                    $vendorOpen = false;
                }
            }

            if (!$vendorOpen) {
                continue;
            }

            if ($isFoodItem && !$foodService->isDishAvailableForOrder($dp)) {
                continue;
            }

            if ($dp->variants->isNotEmpty()) {
                if ((int) $dp->variants->sum('stock') <= 0) {
                    continue;
                }
            } elseif ($dp->total_stock !== null && (int) $dp->total_stock <= 0) {
                continue;
            }
            $catSlug = 'quick_bites';
            $cName = strtolower($dp->category?->name ?? '');
            $cSlug = strtolower($dp->category?->slug ?? '');
            $titleLower = strtolower($dp->title);

            if (str_contains($cSlug, 'biryani') || str_contains($cName, 'biryani') || str_contains($titleLower, 'biryani') || str_contains($titleLower, 'handi')) {
                $catSlug = 'biryani';
            } elseif (str_contains($cSlug, 'pizza') || str_contains($cName, 'pizza') || str_contains($titleLower, 'pizza')) {
                $catSlug = 'pizzas';
            } elseif (str_contains($cSlug, 'burger') || str_contains($cName, 'burger') || str_contains($titleLower, 'burger') || str_contains($titleLower, 'fries')) {
                $catSlug = 'burgers';
            } elseif (str_contains($cSlug, 'dessert') || str_contains($cName, 'dessert') || str_contains($cSlug, 'bakery') || str_contains($cName, 'bakery') || str_contains($titleLower, 'waffle') || str_contains($titleLower, 'cheesecake') || str_contains($titleLower, 'sweet') || str_contains($titleLower, 'brownie') || str_contains($titleLower, 'meetha')) {
                $catSlug = 'sweets';
            } elseif (str_contains($cSlug, 'asian') || str_contains($cName, 'asian') || str_contains($cSlug, 'chinese') || str_contains($cName, 'chinese') || str_contains($titleLower, 'noodle') || str_contains($titleLower, 'dimsum')) {
                $catSlug = 'asian';
            } elseif (str_contains($cSlug, 'south') || str_contains($cName, 'south') || str_contains($titleLower, 'dosa') || str_contains($titleLower, 'idli')) {
                $catSlug = 'south_indian';
            } elseif (str_contains($cSlug, 'beverage') || str_contains($cName, 'beverage') || str_contains($cSlug, 'juice') || str_contains($cName, 'juice') || str_contains($titleLower, 'coffee') || str_contains($titleLower, 'tea') || str_contains($titleLower, 'drink')) {
                $catSlug = 'drinks';
            } elseif (str_contains($cSlug, 'grocer') || str_contains($cName, 'grocer') || str_contains($cSlug, 'kirana') || str_contains($cName, 'kirana') || str_contains($titleLower, 'ghee') || str_contains($titleLower, 'atta') || str_contains($titleLower, 'milk') || str_contains($titleLower, 'dahi')) {
                $catSlug = 'dairy_groceries';
            } elseif (str_contains($cSlug, 'north') || str_contains($cName, 'north') || str_contains($titleLower, 'dal') || str_contains($titleLower, 'paneer') || str_contains($titleLower, 'korma') || str_contains($titleLower, 'kebab') || str_contains($titleLower, 'roti') || str_contains($titleLower, 'naan')) {
                $catSlug = 'north_indian';
            }

            $categorySlugsPresent[$catSlug] = ($categorySlugsPresent[$catSlug] ?? 0) + 1;

            $prepMins = $dp->prep_time_mins ?: $dp->vendor?->settings?->avg_prep_time_mins;
            $realRating = ((float) ($dp->avg_rating ?? 0) > 0 && (int) ($dp->review_count ?? 0) > 0)
                ? (float) round($dp->avg_rating, 1)
                : null;

            $allProducts->push((object)[
                'id' => $dp->id,
                'title' => $dp->title,
                'weight' => $dp->weight_grams ? ($dp->weight_grams >= 1000 ? round($dp->weight_grams / 1000, 1) . ' kg' : $dp->weight_grams . ' g') : '1 Portion',
                'slug' => $dp->slug,
                'base_price' => (float) round($dp->base_price / 100, 2),
                'compare_at_price' => ($dp->compare_at_price && $dp->compare_at_price > $dp->base_price) ? (float) round($dp->compare_at_price / 100, 2) : null,
                'featured_image' => $dp->primary_image_url ?: asset('images/product-placeholder.svg'),
                'brand' => $dp->vendor?->shop_name ?? 'Partner Kitchen',
                'category_slug' => $catSlug,
                'rating' => $realRating,
                'delivery_minutes' => $prepMins ? ($prepMins . ' MINS') : null,
                'description' => $dp->description ?: null,
                'shelf_life' => $dp->vendor?->settings?->is_pure_veg ? 'Pure Veg Kitchen' : ($dp->dietary_label_resolved ?? 'Freshly Prepared'),
                'storage_info' => $dp->vendor?->city ?: null,
                'fssai_license' => $dp->vendor?->isFssaiVerified() ? ($dp->vendor->settings?->fssai_license_number . ' (Verified)') : ($dp->vendor?->settings?->fssai_license_number ?: null),
                'origin' => $dp->country_of_origin ?: 'India',
            ]);
        }

        // 3. Dynamic Categories Filter List based on real items in DB
        $categoryDefinitions = [
            'all' => ['name' => 'All Quick Items', 'icon' => '⚡', 'tag' => 'Instant'],
            'biryani' => ['name' => 'Biryani & Handi', 'icon' => '🍚', 'tag' => 'Dum Cooked'],
            'north_indian' => ['name' => 'North Indian & Curries', 'icon' => '🍛', 'tag' => 'Desi Ghee'],
            'pizzas' => ['name' => 'Pizzas & Bistro', 'icon' => '🍕', 'tag' => 'Wood-Fired'],
            'burgers' => ['name' => 'Burgers & Fries', 'icon' => '🍔', 'tag' => 'Crisp Grills'],
            'asian' => ['name' => 'Pan-Asian & Dimsums', 'icon' => '🍜', 'tag' => 'Wok Tossed'],
            'south_indian' => ['name' => 'South Indian Tiffin', 'icon' => '🥞', 'tag' => 'Ghee Roast'],
            'sweets' => ['name' => 'Sweets, Bakery & Cakes', 'icon' => '🍰', 'tag' => 'Artisan'],
            'drinks' => ['name' => 'Cold Drinks & Coffee', 'icon' => '🥤', 'tag' => 'Chilled'],
            'dairy_groceries' => ['name' => 'Dairy & Daily Essentials', 'icon' => '🥛', 'tag' => 'Farm Fresh'],
            'quick_bites' => ['name' => 'Quick Bites & Snacks', 'icon' => '🍿', 'tag' => 'Fresh Daily'],
        ];

        $categories = [
            ['id' => 'all', 'name' => 'All Quick Items', 'icon' => '⚡', 'tag' => 'Instant', 'count' => $allProducts->count()]
        ];

        foreach ($categoryDefinitions as $slug => $def) {
            if ($slug !== 'all' && !empty($categorySlugsPresent[$slug])) {
                $categories[] = [
                    'id' => $slug,
                    'name' => $def['name'],
                    'icon' => $def['icon'],
                    'tag' => $def['tag'],
                    'count' => $categorySlugsPresent[$slug],
                ];
            }
        }

        // 4. Retrieve existing Cart items for this user/session so cart never resets
        $cart = null;
        if (Auth::check()) {
            $cart = Cart::where('user_id', Auth::id())->first();
        }
        if (!$cart) {
            $sessionId = Session::getId();
            if (!empty($sessionId)) {
                $cart = Cart::where('session_id', $sessionId)
                    ->when(!Auth::check(), fn ($q) => $q->whereNull('user_id'))
                    ->first();
            }
        }
        $cookieCartId = request()->cookie('k71_cart_id');
        if (!$cart && $cookieCartId) {
            $cart = Cart::where('id', $cookieCartId)
                ->when(!Auth::check(), fn ($q) => $q->whereNull('user_id'))
                ->when(Auth::check(), fn ($q) => $q->where(fn ($sq) => $sq->where('user_id', Auth::id())->orWhereNull('user_id')))
                ->first();
        }

        $initialCartState = [];
        if ($cart) {
            $cartItems = $cart->items()->with(['product.images', 'variant.product.images'])->get();
            foreach ($cartItems as $ci) {
                $pId = $ci->product_id ?? $ci->variant?->product_id;
                $prodObj = $allProducts->firstWhere('id', $pId);
                if ($prodObj) {
                    $initialCartState[$pId] = [
                        'id' => $pId,
                        'cart_item_id' => $ci->id,
                        'title' => $prodObj->title,
                        'base_price' => $prodObj->base_price,
                        'featured_image' => $prodObj->featured_image,
                        'weight' => $prodObj->weight,
                        'qty' => $ci->quantity,
                    ];
                }
            }
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'categories' => $categories,
                'products' => $allProducts->values(),
                'total' => $allProducts->count(),
            ]);
        }

        return view('feasto.index', compact('categories', 'allProducts', 'initialCartState'));
    }

    /**
     * Feasto Hyperlocal ETA & Delivery Slot Estimator (Blinkit-style 10-25 min delivery).
     */
    public function estimateEta(Request $request): \Illuminate\Http\JsonResponse
    {
        $pincode = trim((string) $request->query('pincode', ''));
        $city = trim((string) $request->query('city', 'Jaipur'));

        $isHyperlocalSupported = false;
        $etaMinutes = 20;
        $minEta = 15;
        $maxEta = 25;

        // Jaipur central delivery hubs
        $hyperlocalPincodes = [
            '302001', '302002', '302003', '302004', '302005', '302006',
            '302015', '302016', '302017', '302018', '302019', '302020',
            '302021', '302022', '302026', '302029', '302033', '302039'
        ];

        if ($pincode !== '' && in_array($pincode, $hyperlocalPincodes, true)) {
            $isHyperlocalSupported = true;
            $minEta = 10;
            $maxEta = 20;
            $etaMinutes = 15;
        } elseif (strtolower($city) === 'jaipur' || $pincode === '') {
            $isHyperlocalSupported = true;
            $minEta = 15;
            $maxEta = 30;
            $etaMinutes = 20;
        }

        $now = now()->timezone('Asia/Kolkata');
        $slots = [];

        if ($isHyperlocalSupported) {
            $slots[] = [
                'id' => 'instant',
                'label' => "Instant ({$minEta}-{$maxEta} mins)",
                'eta_text' => "Delivery by " . $now->copy()->addMinutes($maxEta)->format('g:i A'),
                'is_instant' => true,
                'available' => true,
            ];

            // Next slot in 1 hour
            $nextSlotStart = $now->copy()->addHour()->startOfHour();
            $slots[] = [
                'id' => 'scheduled_1',
                'label' => $nextSlotStart->format('g A') . ' - ' . $nextSlotStart->copy()->addHour()->format('g A'),
                'eta_text' => 'Today, ' . $nextSlotStart->format('g A') . ' - ' . $nextSlotStart->copy()->addHour()->format('g A'),
                'is_instant' => false,
                'available' => true,
            ];
        }

        return response()->json([
            'success' => true,
            'serviceable' => $isHyperlocalSupported,
            'pincode' => $pincode,
            'city' => $city,
            'min_eta_mins' => $minEta,
            'max_eta_mins' => $maxEta,
            'eta_display' => "{$minEta}–{$maxEta} MINS",
            'free_delivery_threshold_rupees' => 199,
            'delivery_fee_rupees' => 25,
            'slots' => $slots,
        ]);
    }
}
