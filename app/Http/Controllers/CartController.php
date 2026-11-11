<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductGigPackage;
use App\Models\ProductModifier;
use App\Models\ProductVariant;
use App\Services\Food\FoodMarketplaceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;

class CartController extends Controller
{
    public function index()
    {
        $cart = $this->getCart();

        if (!$cart) {
            $items = collect();
        } else {
            $items = $cart->items()->with(['variant.product.images', 'product.images', 'gigPackage', 'vendor'])->get();
        }

        // Group by vendor because checkout generates separate orders
        $groupedItems = $items->groupBy('vendor_id');

        return view('cart.index', compact('groupedItems'));
    }

    public function add(Request $request)
    {
        if ($request->has('modifiers') && !$request->has('selected_modifiers')) {
            $request->merge(['selected_modifiers' => (array) $request->input('modifiers')]);
        }

        $validated = $request->validate([
            'product_id' => 'nullable|integer|exists:products,id',
            'variant_id' => 'nullable|integer|exists:product_variants,id',
            'gig_package_id' => 'nullable|integer|exists:product_gig_packages,id',
            'quantity' => 'required|integer|min:1',
            'selected_modifiers' => 'nullable|array',
            'selected_modifiers.*' => 'integer|exists:product_modifiers,id',
            'special_instructions' => 'nullable|string|max:500',
            'instructions' => 'nullable|string|max:500',
            'clear_existing' => 'nullable|boolean',
            'booking_date_time' => 'nullable|date',
        ]);

        $specialInstructions = trim((string) ($validated['special_instructions'] ?? $validated['instructions'] ?? '')) ?: null;

        // Resolve Product and Variant
        $product = null;
        $variant = null;
        $gigPackage = null;

        if (!empty($validated['variant_id'])) {
            $variant = ProductVariant::withoutGlobalScopes()->with('product.vendor.settings')->findOrFail($validated['variant_id']);
            $product = $variant->product;
        } elseif (!empty($validated['product_id'])) {
            $product = Product::withoutGlobalScopes()->with(['variants', 'gigPackages', 'optionGroups.modifiers', 'modifiers', 'vendor.settings'])->findOrFail($validated['product_id']);
            $variant = $product->variants->first();
        } else {
            return response()->json(['success' => false, 'message' => 'Product or variant selection required.'], 422);
        }

        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Product not found.'], 404);
        }

        // Gig Package resolution
        if (!empty($validated['gig_package_id'])) {
            $gigPackage = ProductGigPackage::where('product_id', $product->id)->findOrFail($validated['gig_package_id']);
        }

        // Listing type enforcement
        if (!$gigPackage && !$product->isPurchase()) {
            $typeLabel = ucfirst(str_replace('_', ' ', $product->listing_type));
            return response()->json([
                'success' => false,
                'message' => "This is an {$typeLabel} listing and cannot be added to the shopping cart for direct purchase."
            ], 422);
        }

        $isFoodProduct = $product->vertical === 'food'
            || ($product->vendor?->isFood() ?? false)
            || ($product->vendor?->settings?->is_restaurant ?? false);

        $foodService = app(FoodMarketplaceService::class);

        if ($isFoodProduct && $product->vendor) {
            $opStatus = $foodService->getRestaurantOperationalStatus($product->vendor);
            if (!$opStatus['accepting_orders']) {
                return response()->json([
                    'success' => false,
                    'message' => $opStatus['reason'] ?: 'This restaurant is currently not accepting new orders.',
                ], 422);
            }

            if (!$foodService->isDishAvailableForOrder($product)) {
                return response()->json([
                    'success' => false,
                    'message' => 'This dish is currently sold out or outside its serving hours.',
                ], 422);
            }
        }

        // Physical inventory check (Gig packages are digital milestone services with virtual availability)
        if (!$gigPackage && $variant) {
            if ($variant->stock <= 0) {
                return response()->json(['success' => false, 'message' => 'This option is completely out of stock.'], 400);
            }

            if ($variant->stock < $validated['quantity']) {
                return response()->json(['success' => false, 'message' => "Only {$variant->stock} pieces available in stock."], 400);
            }
        } elseif (!$gigPackage && !$variant) {
            $availStock = $product->total_stock ?? 100;
            if ($availStock <= 0) {
                return response()->json(['success' => false, 'message' => 'This product is completely out of stock.'], 400);
            }
            if ($availStock < $validated['quantity']) {
                return response()->json(['success' => false, 'message' => "Only {$availStock} pieces available in stock."], 400);
            }
        }

        // Server-side customization & pricing resolution
        $modifiersSnapshot = null;
        $optionSelectionsSnapshot = null;

        if ($gigPackage) {
            $unitPricePaise = (int) round(((float) $gigPackage->price) * 100);
        } elseif ($isFoodProduct) {
            $calc = $foodService->validateAndCalculateCustomizations(
                $product,
                $variant,
                $validated['selected_modifiers'] ?? []
            );
            $unitPricePaise = $calc['unit_price_paise'];
            $modifiersSnapshot = $calc['modifiers_snapshot'] ?: null;
            $optionSelectionsSnapshot = $calc['option_selections_snapshot'] ?: null;
        } else {
            $unitPricePaise = $variant?->price_override
                ?? (int) round(((float) ($variant?->price ?? 0)) * 100)
                ?: $product->base_price;

            // B2B tiered MOQ volume discount pricing calculation
            if (!empty($product->b2b_tiers_json) && is_array($product->b2b_tiers_json)) {
                $tiers = collect($product->b2b_tiers_json)->sortByDesc(fn ($t) => (int) ($t['min_qty'] ?? 1));
                foreach ($tiers as $tier) {
                    if ($validated['quantity'] >= (int) ($tier['min_qty'] ?? 1)) {
                        if (!empty($tier['price'])) {
                            $unitPricePaise = (int) round(((float) $tier['price']) * 100);
                        }
                        break;
                    }
                }
            }

            // Customization Modifiers calculation (non-food custom products)
            if (!empty($validated['selected_modifiers'])) {
                $validModifiers = ProductModifier::where('product_id', $product->id)
                    ->whereIn('id', $validated['selected_modifiers'])
                    ->get();

                if ($validModifiers->count() !== count(array_unique($validated['selected_modifiers']))) {
                    return response()->json([
                        'success' => false,
                        'message' => 'One or more selected options do not belong to this product.',
                    ], 422);
                }

                $modSumRupees = (float) $validModifiers->sum('price_delta');
                $unitPricePaise += (int) round($modSumRupees * 100);

                $modifiersSnapshot = $validModifiers->map(fn ($m) => [
                    'id' => $m->id,
                    'name' => $m->modifier_name,
                    'group' => $m->group_name,
                    'price_delta' => (float) $m->price_delta,
                ])->values()->toArray();
            }
        }

        $cart = $this->getOrCreateCart();

        // Enforce single-restaurant food bag rule (while leaving retail multi-vendor bags untouched)
        if ($isFoodProduct) {
            $conflictingFoodItems = $cart->items()->with(['product', 'vendor.settings'])->get()->filter(function ($existing) use ($product) {
                if ((int) $existing->vendor_id === (int) $product->vendor_id) {
                    return false;
                }
                return ($existing->product?->vertical === 'food')
                    || ($existing->vendor?->isFood() ?? false)
                    || ($existing->vendor?->settings?->is_restaurant ?? false);
            });

            if ($conflictingFoodItems->isNotEmpty()) {
                if (!$request->boolean('clear_existing')) {
                    $firstConflictVendor = $conflictingFoodItems->first()?->vendor;
                    $currentVendorName = $firstConflictVendor?->shop_name ?? 'another restaurant';
                    $newVendorName = $product->vendor?->shop_name ?? 'this restaurant';

                    return response()->json([
                        'success' => false,
                        'conflict' => true,
                        'current_vendor_id' => $firstConflictVendor?->id,
                        'current_vendor_name' => $currentVendorName,
                        'new_vendor_id' => $product->vendor_id,
                        'new_vendor_name' => $newVendorName,
                        'message' => "Your bag contains items from {$currentVendorName}. Clear bag to start a new order from {$newVendorName}?",
                    ], 409);
                }

                $cart->items()->whereIn('id', $conflictingFoodItems->pluck('id')->all())->delete();
            }
        }

        // Match existing item in cart
        $cartItem = null;
        if ($gigPackage) {
            $cartItem = $cart->items()->where('gig_package_id', $gigPackage->id)->first();
        } else {
            $query = $cart->items()->where('product_id', $product->id);
            if ($variant) {
                $query->where('variant_id', $variant->id);
            } else {
                $query->whereNull('variant_id');
            }
            $existingVariantItems = $query->get();
            $incomingIds = !empty($modifiersSnapshot)
                ? collect($modifiersSnapshot)->pluck('id')->sort()->values()->all()
                : (!empty($validated['selected_modifiers']) ? collect($validated['selected_modifiers'])->map(fn ($id) => (int) $id)->sort()->values()->all() : []);

            foreach ($existingVariantItems as $existing) {
                $existingIds = !empty($existing->selected_modifiers_json)
                    ? collect($existing->selected_modifiers_json)->pluck('id')->map(fn ($id) => (int) $id)->sort()->values()->all()
                    : [];
                $existingInstr = trim((string) ($existing->special_instructions ?? '')) ?: null;

                if ($incomingIds === $existingIds && $existingInstr === $specialInstructions) {
                    $cartItem = $existing;
                    break;
                }
            }
        }

        if ($cartItem) {
            if (!$gigPackage && $variant && ($cartItem->quantity + $validated['quantity'] > $variant->stock)) {
                return response()->json(['success' => false, 'message' => 'Cannot add more than available stock.'], 400);
            }
            $cartItem->increment('quantity', $validated['quantity']);
        } else {
            $cart->items()->create([
                'product_id' => $product->id,
                'variant_id' => $variant?->id,
                'vendor_id' => $product->vendor_id,
                'gig_package_id' => $gigPackage?->id,
                'selected_modifiers_json' => $modifiersSnapshot,
                'option_selections_json' => $optionSelectionsSnapshot,
                'special_instructions' => $specialInstructions,
                'booking_date_time' => !empty($validated['booking_date_time']) ? $validated['booking_date_time'] : null,
                'quantity' => $validated['quantity'],
                'price_at_add' => $unitPricePaise,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Added to cart.',
            'cart_count' => $cart->items()->sum('quantity'),
        ]);
    }

    public function remove(Request $request, CartItem $item)
    {
        if ($item->cart_id !== $this->getCart()?->id) {
            abort(403);
        }

        $item->delete();

        if ($request->expectsJson() || $request->wantsJson()) {
            return $this->data();
        }

        return back()->with('success', 'Item removed from cart.');
    }

    public function update(Request $request, CartItem $item)
    {
        if ($item->cart_id !== $this->getCart()?->id) {
            abort(403);
        }

        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        if ($item->gig_package_id) {
            $item->update(['quantity' => $validated['quantity']]);
            if ($request->expectsJson() || $request->wantsJson()) {
                return $this->data();
            }
            return back()->with('success', 'Cart updated.');
        }

        if ($item->variant_id) {
            $variant = ProductVariant::withoutGlobalScopes()->find($item->variant_id);
            if (!$variant) {
                if ($request->expectsJson()) {
                    return response()->json(['success' => false, 'message' => 'This item is no longer available.'], 404);
                }
                return back()->with('error', 'This item is no longer available.');
            }
            $availableStock = (int) $variant->stock;
        } else {
            $product = Product::withoutGlobalScopes()->find($item->product_id);
            if (!$product) {
                if ($request->expectsJson()) {
                    return response()->json(['success' => false, 'message' => 'This product is no longer available.'], 404);
                }
                return back()->with('error', 'This product is no longer available.');
            }
            $availableStock = (int) ($product->total_stock ?? 100);
        }

        if ($availableStock <= 0) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'This item is now out of stock.'], 400);
            }
            return back()->with('error', 'This item is now out of stock.');
        }

        if ($validated['quantity'] > $availableStock) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => "Only {$availableStock} items available in stock."], 400);
            }
            return back()->with('error', "Only {$availableStock} items available in stock.");
        }

        $item->update(['quantity' => $validated['quantity']]);

        if ($request->expectsJson() || $request->wantsJson()) {
            return $this->data();
        }

        return back()->with('success', 'Cart updated.');
    }

    public function data()
    {
        $cart = $this->getCart();
        if (!$cart) {
            return response()->json([
                'items' => [],
                'count' => 0,
                'subtotal' => 0,
                'subtotal_formatted' => '₹0.00',
                'is_food_cart' => false,
                'delivery_fee_paise' => 0,
                'packaging_fee_paise' => 0,
                'free_delivery_above_paise' => null,
                'min_order_amount_paise' => 0,
                'taxes_paise' => 0,
                'vendor_name' => null,
                'checkout_url' => route('cart.index'),
            ]);
        }

        $items = $cart->items()->with(['variant.product.images', 'product.images', 'variant', 'gigPackage', 'vendor.settings'])->get();

        $subtotal = 0;
        $mappedItems = $items->map(function ($item) use (&$subtotal) {
            $price = $item->price_at_add;
            $lineTotal = $price * $item->quantity;
            $subtotal += $lineTotal;

            $product = $item->product ?? $item->variant?->product;
            $imageUrl = $product ? $product->primary_image_url : null;

            $variantLabel = 'Standard';
            if ($item->gigPackage) {
                $variantLabel = ucfirst($item->gigPackage->tier) . ' Package (' . $item->gigPackage->delivery_days . 'd Delivery)';
            } elseif ($item->variant) {
                $variantLabel = $item->variant->name ?? ($item->variant->sku ?? ($item->variant->size ?? 'Standard'));
            }

            if (!empty($item->selected_modifiers_json) && is_array($item->selected_modifiers_json)) {
                $modNames = array_column($item->selected_modifiers_json, 'name');
                if (!empty($modNames)) {
                    $variantLabel .= ' + ' . implode(', ', $modNames);
                }
            }

            return [
                'id' => $item->id,
                'product_id' => $item->product_id ?? $item->variant?->product_id,
                'product_title' => $product?->title ?? 'Product',
                'variant_label' => $variantLabel,
                'special_instructions' => $item->special_instructions,
                'is_veg' => $product ? $product->isVeg() : true,
                'vendor_name' => $item->vendor?->shop_name ?? 'K71 Store',
                'vendor_id' => $item->vendor_id,
                'quantity' => $item->quantity,
                'unit_price' => $price,
                'unit_price_formatted' => '₹' . number_format($price / 100, 2),
                'line_total' => $lineTotal,
                'line_total_formatted' => '₹' . number_format($lineTotal / 100, 2),
                'image_url' => $imageUrl,
                'checkout_url' => $item->vendor ? (Route::has('checkout.vendor') ? route('checkout.vendor', $item->vendor) : route('cart.index')) : route('cart.index'),
            ];
        });

        // Compute food-specific cart fees when food items are present
        $foodItem = $items->first(fn ($it) => ($it->product?->vertical === 'food') || ($it->vendor?->isFood() ?? false) || ($it->vendor?->settings?->is_restaurant ?? false));
        $isFoodCart = (bool) $foodItem;
        $deliveryFeePaise = 0;
        $packagingFeePaise = 0;
        $freeDeliveryAbovePaise = null;
        $minOrderAmountPaise = 0;
        $vendorName = $items->first()?->vendor?->shop_name;
        $checkoutUrl = $items->first()?->vendor
            ? (Route::has('checkout.vendor') ? route('checkout.vendor', $items->first()->vendor) : route('cart.index'))
            : route('cart.index');

        if ($foodItem && $foodItem->vendor) {
            $foodVendor = $foodItem->vendor;
            $vendorName = $foodVendor->shop_name;
            $checkoutUrl = Route::has('checkout.vendor') ? route('checkout.vendor', $foodVendor) : route('cart.index');
            $foodSubtotal = $items->where('vendor_id', $foodVendor->id)->sum(fn ($it) => $it->price_at_add * $it->quantity);
            $fees = app(FoodMarketplaceService::class)->calculateFoodFees($foodVendor, $foodSubtotal, 'food_delivery');
            $deliveryFeePaise = $fees['delivery_fee'];
            $packagingFeePaise = $fees['packaging_fee'];
            $freeDeliveryAbovePaise = $fees['free_delivery_above'];
            $minOrderAmountPaise = $fees['min_order_amount'];
        }

        return response()->json([
            'items' => $mappedItems->values(),
            'count' => $items->sum('quantity'),
            'subtotal' => $subtotal,
            'subtotal_formatted' => '₹' . number_format($subtotal / 100, 2),
            'is_food_cart' => $isFoodCart,
            'delivery_fee_paise' => $deliveryFeePaise,
            'packaging_fee_paise' => $packagingFeePaise,
            'free_delivery_above_paise' => $freeDeliveryAbovePaise,
            'min_order_amount_paise' => $minOrderAmountPaise,
            'taxes_paise' => 0,
            'vendor_name' => $vendorName,
            'checkout_url' => $checkoutUrl,
        ]);
    }

    public function getCount()
    {
        $cart = $this->getCart();
        return response()->json(['count' => $cart ? $cart->items()->sum('quantity') : 0]);
    }

    private function getCart()
    {
        $cart = null;
        if (Auth::check()) {
            Cart::mergeGuestCart(Auth::id(), Session::getId());
            $cart = Cart::where('user_id', Auth::id())->first();
        }

        if (!$cart) {
            $cart = Cart::where('session_id', Session::getId())->first();
        }

        $cookieCartId = request()->cookie('k71_cart_id');
        if (!$cart && $cookieCartId) {
            $cart = Cart::where('id', $cookieCartId)->first();
            if ($cart && Auth::check() && !$cart->user_id) {
                $cart->update(['user_id' => Auth::id()]);
            }
        }

        if ($cart) {
            cookie()->queue(cookie('k71_cart_id', $cart->id, 60 * 24 * 30, '/', config('session.domain'), null, false));
        }

        return $cart;
    }

    private function getOrCreateCart()
    {
        $cart = null;
        if (Auth::check()) {
            Cart::mergeGuestCart(Auth::id(), Session::getId());
            $cart = Cart::firstOrCreate(['user_id' => Auth::id()]);
        } else {
            Session::put('cart_initialized', true);
            $cookieCartId = request()->cookie('k71_cart_id');
            if ($cookieCartId) {
                $cart = Cart::where('id', $cookieCartId)->first();
            }
            if (!$cart) {
                $cart = Cart::firstOrCreate(['session_id' => Session::getId()]);
            }
        }

        cookie()->queue(cookie('k71_cart_id', $cart->id, 60 * 24 * 30, '/', config('session.domain'), null, false));

        return $cart;
    }
}
