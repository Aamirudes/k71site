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
            })
            ->where(function ($q) {
                $q->where('title', 'NOT LIKE', '%dummy%')
                  ->where('title', 'NOT LIKE', '%test%')
                  ->where('title', 'NOT LIKE', '%sample%')
                  ->where('title', 'NOT LIKE', '%fake%');
            });
        app(\App\Services\Monetization\VisibilityService::class)->scopeVisibleProducts($feastoQuery, 'food');
        $dbProducts = $feastoQuery
            ->with(['category', 'vendor', 'images', 'variants'])
            ->latest()
            ->get();

        // 2. Format real items with reliable schema
        $allProducts = collect();
        $categorySlugsPresent = [];

        foreach ($dbProducts as $dp) {
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
                'fssai_license' => $dp->vendor?->isFssaiVerified() ? ($dp->vendor->settings->fssai_number . ' (Verified)') : ($dp->vendor?->settings?->fssai_number ?: null),
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
            $cart = Cart::where('session_id', Session::getId())->first();
        }
        $cookieCartId = request()->cookie('k71_cart_id');
        if (!$cart && $cookieCartId) {
            $cart = Cart::where('id', $cookieCartId)->first();
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

        return view('feasto.index', compact('categories', 'allProducts', 'initialCartState'));
    }
}
