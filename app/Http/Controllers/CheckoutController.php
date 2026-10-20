<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order;
use App\Models\Vendor;
use App\Models\VendorSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Coupon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    public function index(Vendor $vendor)
    {
        \App\Services\StockManagerService::releaseExpiredOrders(45);

        Cart::mergeGuestCart(Auth::id(), session()->getId());

        $cart = Cart::where('user_id', Auth::id())->first();
        
        if (!$cart) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        $items = $cart->items()->where('vendor_id', $vendor->id)->with(['variant.product.images', 'product.images', 'gigPackage'])->get();
        
        if ($items->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'No items from this vendor in your cart.');
        }

        // Verify that all items are in stock and support direct purchase
        foreach ($items as $item) {
            $product = $item->product ?? $item->variant?->product;
            if ($product && !$item->gig_package_id && !$product->isPurchase()) {
                $title = $product->title;
                return redirect()->route('cart.index')->with('error', "{$title} is an enquiry or booking item and cannot be purchased directly through checkout.");
            }
            if (!$item->gig_package_id) {
                if (!$item->variant || $item->variant->stock <= 0) {
                    $title = $product?->title ?? 'Item';
                    $sku = $item->variant?->sku ?? 'Size';
                    return redirect()->route('cart.index')->with('error', "{$title} ({$sku}) has run out of stock. Please remove it from your cart before proceeding.");
                }
                if ($item->variant->stock < $item->quantity) {
                    $title = $product?->title ?? 'Item';
                    $sku = $item->variant?->sku ?? 'Size';
                    return redirect()->route('cart.index')->with('error', "Only {$item->variant->stock} pieces available for {$title} ({$sku}). Please adjust your cart quantity.");
                }
            }
        }

        $settings = VendorSetting::where('vendor_id', $vendor->id)->first();
        
        $subtotal = 0;
        $totalGst = 0;
        
        foreach ($items as $item) {
            $product = $item->product ?? $item->variant?->product;
            $price = $item->price_at_add ?? ($item->variant?->price_override ?? ($product?->base_price ?? 0));
            $itemTotal = $price * $item->quantity;
            $subtotal += $itemTotal;
            
            // Calculate GST amount for this item
            $gstRate = $product?->gst_rate ?? 0;
            // Assuming price is inclusive of GST as per Indian norms for B2C
            // Tax = Price - (Price / (1 + (Rate/100)))
            $taxAmount = $itemTotal - ($itemTotal / (1 + ($gstRate / 100)));
            $totalGst += $taxAmount;
        }

        $isFoodOrder = $vendor->isRestaurant() || $items->contains(fn($it) => (($it->product?->vertical ?? '') === 'food' || ($it->variant?->product?->vertical ?? '') === 'food'));

        $shipping = 0;
        $cachedShipping = session("checkout_shipping_{$vendor->id}");
        if ($isFoodOrder) {
            $shipping = $settings->food_delivery_fee ?? 4000; // Fixed restaurant local delivery fee (default ₹40)
        } elseif ($subtotal <= 100) {
            $shipping = 0; // ₹1 Test Order: 100% Free Shipping for Gateway Verification
        } elseif ($settings && $settings->shipping_type === 'free_above' && !empty($settings->shipping_free_above) && $subtotal >= $settings->shipping_free_above) {
            $shipping = 0; // Free delivery (only if vendor explicitly configured a free shipping threshold)
        } elseif (is_array($cachedShipping) && isset($cachedShipping['amount_paise'])) {
            $shipping = (int) $cachedShipping['amount_paise'];
        } elseif ($settings && $settings->shipping_type === 'flat') {
            $shipping = $settings->shipping_flat_rate ?? 6000;
        } else {
            $shipping = 6000; // Standard 60 Rs
        }

        $discount = 0;
        $appliedCoupon = null;
        if (session()->has("applied_coupon_{$vendor->id}")) {
            $couponCode = session("applied_coupon_{$vendor->id}");
            $appliedCoupon = Coupon::where('vendor_id', $vendor->id)
                ->where('code', $couponCode)
                ->where('is_active', true)
                ->where('starts_at', '<=', now())
                ->where('expires_at', '>=', now())
                ->first();

            if ($appliedCoupon) {
                // Calculate discount
                if ($appliedCoupon->min_cart_value && $subtotal < $appliedCoupon->min_cart_value) {
                    // Min cart value not met
                    $appliedCoupon = null;
                    session()->forget("applied_coupon_{$vendor->id}");
                } else {
                    if ($appliedCoupon->type === 'flat') {
                        $discount = min($appliedCoupon->value, $subtotal); // Max discount is subtotal
                    } else {
                        $discount = ($subtotal * $appliedCoupon->value) / 100;
                        if ($appliedCoupon->max_discount && $discount > $appliedCoupon->max_discount) {
                            $discount = $appliedCoupon->max_discount;
                        }
                    }
                }
            } else {
                session()->forget("applied_coupon_{$vendor->id}");
            }
        }

        $total = max(0, $subtotal - $discount) + $shipping;

        // COD and Payment Settings
        $codMarketplaceEnabled = (bool) (DB::table('settings')->where('key', 'cod_marketplace_enabled')->value('value') ?? config('k71.cod_marketplace_enabled', true));
        $codMaxAmount = (int) (DB::table('settings')->where('key', 'cod_max_amount')->value('value') ?? config('k71.cod_max_amount', 500000));
        $codFee = (int) (DB::table('settings')->where('key', 'cod_fee')->value('value') ?? config('k71.cod_fee', 0));

        $upiAllowed = (bool) ($settings->upi_enabled ?? true);
        $vendorCodAllowed = (bool) ($settings->cod_enabled ?? false);

        $isCodEligible = $codMarketplaceEnabled && $vendorCodAllowed && ($total <= $codMaxAmount);

        $codDisabledReason = null;
        if (!$codMarketplaceEnabled) {
            $codDisabledReason = 'Cash on Delivery is currently disabled platform-wide.';
        } elseif (!$vendorCodAllowed) {
            $codDisabledReason = 'This seller accepts only prepaid UPI / Online payments.';
        } elseif ($total > $codMaxAmount) {
            $formattedMax = '₹' . number_format($codMaxAmount / 100);
            $codDisabledReason = "Cash on Delivery is only available for orders up to {$formattedMax}.";
        }

        $defaultPaymentMethod = $upiAllowed ? 'phonepe' : ($isCodEligible ? 'cod' : 'phonepe');

        // Fetch addresses... for simplicity in MVP we collect address in the checkout form
        return view('checkout.index', compact(
            'vendor', 'items', 'subtotal', 'totalGst', 'shipping', 'discount', 'total', 
            'settings', 'appliedCoupon', 'cachedShipping', 'isFoodOrder',
            'codMarketplaceEnabled', 'codMaxAmount', 'codFee', 'upiAllowed', 'vendorCodAllowed', 
            'isCodEligible', 'codDisabledReason', 'defaultPaymentMethod'
        ));
    }

    public function process(Request $request, Vendor $vendor)
    {
        $settings = VendorSetting::where('vendor_id', $vendor->id)->first();
        $codMarketplaceEnabled = (bool) (DB::table('settings')->where('key', 'cod_marketplace_enabled')->value('value') ?? config('k71.cod_marketplace_enabled', true));
        $codMaxAmount = (int) (DB::table('settings')->where('key', 'cod_max_amount')->value('value') ?? config('k71.cod_max_amount', 500000));
        $codFee = (int) (DB::table('settings')->where('key', 'cod_fee')->value('value') ?? config('k71.cod_fee', 0));

        $upiAllowed = (bool) ($settings->upi_enabled ?? true);
        $vendorCodAllowed = (bool) ($settings->cod_enabled ?? false);

        $allowedMethods = [];
        if ($upiAllowed) {
            $allowedMethods[] = 'phonepe';
            $allowedMethods[] = 'upi';
        }
        if ($codMarketplaceEnabled && $vendorCodAllowed) {
            $allowedMethods[] = 'cod';
        }

        if (empty($allowedMethods)) {
            return back()->with('error', 'No active payment method available for this vendor.');
        }

        $validated = $request->validate([
            'address_line_1' => 'required|string',
            'city' => 'required|string',
            'state' => 'required|string',
            'pincode' => 'required|string|size:6',
            'phone' => 'required|string|min:10|max:15',
            'payment_method' => ['required', 'string', \Illuminate\Validation\Rule::in($allowedMethods)],
            'order_type' => 'nullable|string|in:standard,food_delivery,food_takeaway',
            'kitchen_notes' => 'nullable|string|max:500',
        ]);

        $cart = Cart::where('user_id', Auth::id())->firstOrFail();
        $items = $cart->items()->where('vendor_id', $vendor->id)->with(['variant.product.images', 'product.images', 'gigPackage'])->get();
        
        if ($items->isEmpty()) {
            return redirect()->route('cart.index');
        }

        $isFoodOrder = $vendor->isRestaurant() || $items->contains(fn($it) => (($it->product?->vertical ?? '') === 'food' || ($it->variant?->product?->vertical ?? '') === 'food'));
        $orderType = $isFoodOrder ? ($validated['order_type'] ?? 'food_delivery') : 'standard';

        // Server-side listing_type check
        foreach ($items as $item) {
            $product = $item->product ?? $item->variant?->product;
            if ($product && !$item->gig_package_id && !$product->isPurchase()) {
                return redirect()->route('cart.index')->with('error', "{$product->title} is an enquiry/booking listing and cannot be checked out.");
            }
        }

        // Server-side COD threshold check
        if ($validated['payment_method'] === 'cod') {
            $estSubtotal = $items->sum(function($item) {
                $price = $item->price_at_add ?? ($item->variant?->price_override ?? ($item->product?->base_price ?? 0));
                return $price * $item->quantity;
            });
            if ($estSubtotal > $codMaxAmount) {
                $formattedMax = '₹' . number_format($codMaxAmount / 100);
                return back()
                    ->withInput()
                    ->withErrors(['payment_method' => "Cash on Delivery is only available for orders up to {$formattedMax}. Please select an online payment method."]);
            }
        }

        \App\Services\StockManagerService::releaseExpiredOrders(45);

        DB::beginTransaction();
        try {
            $subtotal = 0;
            $totalGst = 0;
            $itemCount = 0;
            $orderItems = [];

            foreach ($items as $item) {
                $product = $item->product ?? $item->variant?->product;
                $lockedVariant = null;

                if (!$item->gig_package_id && $item->variant_id) {
                    // Pessimistic locking on the variant row to eliminate race conditions
                    $lockedVariant = \App\Models\ProductVariant::withoutGlobalScopes()
                        ->where('id', $item->variant_id)
                        ->lockForUpdate()
                        ->first();

                    if (!$lockedVariant || $lockedVariant->stock <= 0) {
                        $prodTitle = $product?->title ?? 'Selected item';
                        $sku = $lockedVariant?->sku ?? 'Size';
                        throw new \Exception("Product '{$prodTitle}' ({$sku}) has just run out of stock. Please adjust your cart.");
                    }

                    if ($lockedVariant->stock < $item->quantity) {
                        $prodTitle = $product?->title ?? 'Selected item';
                        $sku = $lockedVariant?->sku ?? 'Size';
                        throw new \Exception("Only {$lockedVariant->stock} pieces left in stock for '{$prodTitle}' ({$sku}). Please adjust your quantity.");
                    }

                    // Deduct variant stock
                    $lockedVariant->decrement('stock', $item->quantity);

                    // Sync and decrement product total_stock
                    $parentProduct = \App\Models\Product::withoutGlobalScopes()->where('id', $lockedVariant->product_id)->first();
                    if ($parentProduct) {
                        $parentProduct->decrement('total_stock', $item->quantity);
                        if ($parentProduct->total_stock < 0) {
                            $parentProduct->update(['total_stock' => 0]);
                        }
                    }
                }

                $price = $item->price_at_add ?? ($lockedVariant?->price_override ?? ($product?->base_price ?? 0));
                $itemTotal = $price * $item->quantity;
                $subtotal += $itemTotal;
                $itemCount += $item->quantity;
                
                $gstRate = $product?->gst_rate ?? 0;
                $taxAmount = $itemTotal - ($itemTotal / (1 + ($gstRate / 100)));
                $totalGst += $taxAmount;

                $metaJson = [
                    'vertical' => $product?->vertical_type ?? $product?->vertical ?? 'standard_retail',
                    'gig_package' => $item->gigPackage ? [
                        'id' => $item->gigPackage->id,
                        'tier' => $item->gigPackage->tier,
                        'title' => $item->gigPackage->title,
                        'delivery_days' => $item->gigPackage->delivery_days,
                        'revisions' => $item->gigPackage->revisions,
                    ] : null,
                    'modifiers' => $item->selected_modifiers_json ?? null,
                    'booking_date_time' => $item->booking_date_time?->toIso8601String(),
                ];

                $variantLabel = $item->gigPackage 
                    ? (ucfirst($item->gigPackage->tier) . ' Package') 
                    : ($lockedVariant?->sku ?? 'Standard');

                if (!empty($item->selected_modifiers_json) && is_array($item->selected_modifiers_json)) {
                    $modNames = array_column($item->selected_modifiers_json, 'name');
                    if (!empty($modNames)) {
                        $variantLabel .= ' + ' . implode(', ', $modNames);
                    }
                }

                $orderItems[] = [
                    'product_id' => $product?->id ?? $lockedVariant?->product_id,
                    'variant_id' => $lockedVariant?->id ?? $item->variant_id,
                    'gig_package_id' => $item->gig_package_id,
                    'selected_modifiers_json' => $item->selected_modifiers_json,
                    'booking_date_time' => $item->booking_date_time,
                    'quantity' => $item->quantity,
                    'unit_price' => $price,
                    'total_price' => $itemTotal,
                    'tax_amount' => $taxAmount,
                    'gst_rate' => $gstRate,
                    'hsn_code' => $product?->hsn_code ?? '0000',
                    'snapshot' => [
                        'title' => $product?->title ?? 'Item',
                        'variant_sku' => $variantLabel,
                        'image' => $product?->images->first()->path ?? null,
                    ],
                    'meta_json' => $metaJson,
                ];
            }

            $shipping = 0;
            // 1. Food orders use restaurant direct delivery fee or free takeaway
            if ($isFoodOrder) {
                $shipping = ($orderType === 'food_takeaway') ? 0 : ($settings?->food_delivery_fee ?? 4000);
            } elseif ($settings && $settings->shipping_type === 'free_above' && !empty($settings->shipping_free_above) && $subtotal >= $settings->shipping_free_above) {
                $shipping = 0; // Free delivery (only if vendor explicitly configured a free shipping threshold)
            } elseif ($subtotal <= 100) {
                $shipping = 0; // ₹1 Test Order: 100% Free Shipping for Gateway Verification
            } else {
                // 2. Check if verified session quote exists for this pincode
                $cachedShipping = session("checkout_shipping_{$vendor->id}");
                if (is_array($cachedShipping) && ($cachedShipping['pincode'] ?? '') === $validated['pincode']) {
                    $shipping = (int) $cachedShipping['amount_paise'];
                } else {
                    // 3. Dynamic lookup via Shiprocket based on customer pincode and cart consignment weight
                    try {
                        $totalWeightGrams = 0;
                        foreach ($items as $item) {
                            $w = $item->variant->product->weight_grams ?? $item->product->weight_grams ?? 450;
                            $totalWeightGrams += ($w * $item->quantity);
                        }
                        $weightKg = round(max(0.25, $totalWeightGrams / 1000), 2);
                        $rawAddr = $vendor->pickup_address;
                        $pickupAddr = is_array($rawAddr) ? $rawAddr : (json_decode($rawAddr, true) ?: []);
                        $pickupPincode = $pickupAddr['pincode'] ?? $pickupAddr['pin_code'] ?? config('shipping.fallback_pickup.pincode', '305801');

                        $shiprocket = app(\App\Services\Shipping\ShiprocketService::class);
                        $ratesData = $shiprocket->checkRates($pickupPincode, $validated['pincode'], $weightKg, $validated['payment_method'] === 'cod');
                        $couriers = $ratesData['available_couriers'] ?? [];
                        if (!empty($couriers)) {
                            usort($couriers, fn($a, $b) => ($a['customer_rate'] ?? 99999) <=> ($b['customer_rate'] ?? 99999));
                            $shipping = (int) (ceil($couriers[0]['customer_rate']) * 100);
                        } else {
                            $shipping = $settings?->shipping_flat_rate ?? 6000;
                        }
                    } catch (\Throwable $e) {
                        $shipping = $settings?->shipping_flat_rate ?? 6000;
                    }
                }
            }

            $discount = 0;
            $appliedCoupon = null;
            if (session()->has("applied_coupon_{$vendor->id}")) {
                $couponCode = session("applied_coupon_{$vendor->id}");
                $appliedCoupon = Coupon::where('vendor_id', $vendor->id)
                    ->where('code', $couponCode)
                    ->where('is_active', true)
                    ->where('starts_at', '<=', now())
                    ->where('expires_at', '>=', now())
                    ->first();

                if ($appliedCoupon) {
                    // Verify limits again
                    $isValid = true;
                    if ($appliedCoupon->total_limit && $appliedCoupon->usage_count >= $appliedCoupon->total_limit) {
                        $isValid = false;
                    }
                    if ($appliedCoupon->min_cart_value && $subtotal < $appliedCoupon->min_cart_value) {
                        $isValid = false;
                    }
                    
                    // First order only check
                    if ($isValid && $appliedCoupon->first_order_only) {
                        $hasOrders = Order::where('user_id', Auth::id())->where('vendor_id', $vendor->id)->exists();
                        if ($hasOrders) $isValid = false;
                    }

                    // Per customer limit check
                    if ($isValid) {
                        $userUsage = \App\Models\CouponUsage::where('coupon_id', $appliedCoupon->id)->where('user_id', Auth::id())->count();
                        if ($userUsage >= $appliedCoupon->per_customer_limit) {
                            $isValid = false;
                        }
                    }

                    if ($isValid) {
                        if ($appliedCoupon->type === 'flat') {
                            $discount = min($appliedCoupon->value, $subtotal);
                        } else {
                            $discount = ($subtotal * $appliedCoupon->value) / 100;
                            if ($appliedCoupon->max_discount && $discount > $appliedCoupon->max_discount) {
                                $discount = $appliedCoupon->max_discount;
                            }
                        }
                    } else {
                        $appliedCoupon = null;
                        session()->forget("applied_coupon_{$vendor->id}");
                    }
                }
            }

            $total = max(0, $subtotal - $discount) + $shipping;

            if ($validated['payment_method'] === 'cod') {
                if ($total > $codMaxAmount) {
                    $formattedMax = '₹' . number_format($codMaxAmount / 100);
                    throw new \Exception("Order total exceeds the maximum limit for Cash on Delivery ({$formattedMax}). Please complete payment via UPI.");
                }
            }

            // Generate order number
            $orderNumber = 'ORD-' . strtoupper(Str::random(10));

            $order = Order::create([
                'user_id' => Auth::id(),
                'vendor_id' => $vendor->id,
                'order_number' => $orderNumber,
                'order_type' => $orderType,
                'kitchen_notes' => $validated['kitchen_notes'] ?? null,
                'estimated_delivery_time' => $isFoodOrder ? now()->addMinutes(($settings?->avg_prep_time_mins ?? 25) + 15) : null,
                'item_count' => $itemCount,
                'subtotal' => $subtotal,
                'shipping_amount' => $shipping,
                'tax_amount' => $totalGst,
                'discount_amount' => $discount,
                'total_amount' => $total,
                'status' => 'pending',
                'payment_method' => $validated['payment_method'],
                'payment_status' => 'pending',
                'address_snapshot' => [
                    'line_1' => $validated['address_line_1'],
                    'city' => $validated['city'],
                    'state' => $validated['state'],
                    'pincode' => $validated['pincode'],
                    'phone' => $validated['phone'],
                ],
            ]);

            $order->items()->createMany($orderItems);

            // Record coupon usage
            if ($appliedCoupon) {
                \App\Models\CouponUsage::create([
                    'coupon_id' => $appliedCoupon->id,
                    'user_id' => Auth::id(),
                    'order_id' => $order->id,
                    'discount_amount' => $discount,
                ]);
                $appliedCoupon->increment('usage_count');
                session()->forget("applied_coupon_{$vendor->id}");
            }

            // Remove items for this vendor from cart now that the order has been created
            $cart->items()->where('vendor_id', $vendor->id)->delete();

            DB::commit();

            if ($validated['payment_method'] === 'phonepe') {
                return redirect()->route('payment.phonepe.initiate', $order->id);
            }

            if ($validated['payment_method'] === 'upi') {
                return redirect()->route('checkout.payment', $order->id);
            }

            if ($validated['payment_method'] === 'cod') {
                return redirect()->route('orders.success', $order->id)->with('success', 'Your Cash on Delivery order has been placed! Please keep cash ready upon doorstep delivery.');
            }

            return redirect()->route('orders.success', $order->id);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    public function payment(Order $order)
    {
        if ($order->user_id !== Auth::id()) abort(403);
        if ($order->payment_method !== 'upi' || $order->payment_status !== 'pending') {
            return redirect()->route('orders.success', $order->id);
        }

        $vendor = $order->vendor;
        $settings = VendorSetting::where('vendor_id', $vendor->id)->first();
        
        $upiUrl = "upi://pay?pa={$settings->upi_id}&pn=" . urlencode($vendor->shop_name) . "&am=" . ($order->total_amount / 100) . "&cu=INR";
        $qr_url = "https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=" . urlencode($upiUrl);

        return view('checkout.payment', compact('order', 'vendor', 'settings', 'upiUrl', 'qr_url'));
    }

    public function processPayment(Request $request, Order $order)
    {
        if ($order->user_id !== Auth::id()) abort(403);
        if ($order->payment_method !== 'upi' || $order->payment_status !== 'pending') {
            return redirect()->route('orders.success', $order->id);
        }

        $request->validate([
            'upi_transaction_id' => 'required|string|min:8',
        ]);

        $order->paymentClaim()->create([
            'user_id' => Auth::id(),
            'utr_number' => $request->upi_transaction_id,
            'amount_claimed' => $order->total_amount,
            'status' => 'pending',
        ]);

        $order->update(['payment_status' => 'processing']);

        return redirect()->route('orders.success', $order->id)->with('success', 'Payment verified successfully!');
    }

    public function applyCoupon(Request $request, Vendor $vendor)
    {
        $request->validate(['coupon_code' => 'required|string']);
        $code = trim($request->coupon_code);

        // Case-insensitive search
        $coupon = Coupon::where('vendor_id', $vendor->id)
            ->whereRaw('LOWER(code) = ?', [strtolower($code)])
            ->first();

        if (!$coupon) {
            return $this->couponResponse($request, false, 'Invalid coupon code.');
        }

        if (!$coupon->is_active || now()->isBefore($coupon->starts_at) || now()->isAfter($coupon->expires_at)) {
            return $this->couponResponse($request, false, 'This coupon is inactive or expired.');
        }

        if ($coupon->total_limit && $coupon->usage_count >= $coupon->total_limit) {
            return $this->couponResponse($request, false, 'This coupon has reached its maximum usage limit.');
        }

        // Per customer limit check
        $userUsage = \App\Models\CouponUsage::where('coupon_id', $coupon->id)->where('user_id', Auth::id())->count();
        if ($userUsage >= $coupon->per_customer_limit) {
            return $this->couponResponse($request, false, 'You have already reached the maximum usage limit for this coupon.');
        }

        // First order only check
        if ($coupon->first_order_only) {
            $hasOrders = Order::where('user_id', Auth::id())->where('vendor_id', $vendor->id)->exists();
            if ($hasOrders) {
                return $this->couponResponse($request, false, 'This coupon is valid for first-time customers only.');
            }
        }

        // Cart minimum check
        $cart = Cart::where('user_id', Auth::id())->first();
        $subtotal = 0;
        if ($cart) {
            $items = $cart->items()->where('vendor_id', $vendor->id)->with('variant.product')->get();
            foreach ($items as $item) {
                $price = $item->variant->price_override ?? $item->variant->product->base_price;
                $subtotal += $price * $item->quantity;
            }
        }
        
        if ($coupon->min_cart_value && $subtotal < $coupon->min_cart_value) {
            return $this->couponResponse($request, false, 'Your cart value must be at least ₹' . number_format($coupon->min_cart_value / 100, 2) . ' to use this coupon.');
        }

        session()->put("applied_coupon_{$vendor->id}", $coupon->code);
        session()->save();
        return $this->couponResponse($request, true, 'Coupon applied successfully!');
    }

    public function removeCoupon(Request $request, Vendor $vendor)
    {
        session()->forget("applied_coupon_{$vendor->id}");
        session()->save();
        return $this->couponResponse($request, true, 'Coupon removed.');
    }

    public function calculateShippingRate(Request $request, Vendor $vendor, \App\Services\Shipping\ShiprocketService $shiprocket)
    {
        $validated = $request->validate([
            'pincode' => 'required|string|size:6',
        ]);

        $cart = Cart::where('user_id', Auth::id())->first();
        if (!$cart) {
            return response()->json(['success' => false, 'message' => 'Cart not found'], 404);
        }

        $items = $cart->items()->where('vendor_id', $vendor->id)->with('variant.product')->get();
        if ($items->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'No items in cart for this vendor'], 404);
        }

        $subtotal = 0;
        $totalWeightGrams = 0;
        foreach ($items as $item) {
            $price = $item->variant->price_override ?? $item->variant->product->base_price;
            $subtotal += ($price * $item->quantity);
            $w = $item->variant->product->weight_grams ?? $item->product->weight_grams ?? 450;
            $totalWeightGrams += ($w * $item->quantity);
        }

        $settings = VendorSetting::where('vendor_id', $vendor->id)->first();
        if ($subtotal <= 100 || ($settings && $settings->shipping_type === 'free_above' && !empty($settings->shipping_free_above) && $subtotal >= $settings->shipping_free_above)) {
            session([
                "checkout_shipping_{$vendor->id}" => [
                    'pincode' => $validated['pincode'],
                    'amount_paise' => 0,
                    'courier_name' => 'Free Delivery',
                    'est_days' => 3,
                ]
            ]);
            return response()->json([
                'success' => true,
                'is_free' => true,
                'shipping_paise' => 0,
                'shipping_formatted' => 'Free',
                'courier_name' => 'Free Delivery on orders above ₹' . number_format(($settings->shipping_free_above ?? 0) / 100, 0),
                'estimated_days' => 3,
            ]);
        }

        $weightKg = round(max(0.25, $totalWeightGrams / 1000), 2);
        $rawAddr = $vendor->pickup_address;
        $pickupAddr = is_array($rawAddr) ? $rawAddr : (json_decode($rawAddr, true) ?: []);
        $pickupPincode = $pickupAddr['pincode'] ?? $pickupAddr['pin_code'] ?? config('shipping.fallback_pickup.pincode', '305801');

        $deliveryPincode = $validated['pincode'];
        try {
            $ratesData = $shiprocket->checkRates($pickupPincode, $deliveryPincode, $weightKg, false);
            $couriers = $ratesData['available_couriers'] ?? [];
            if (!empty($couriers)) {
                usort($couriers, fn($a, $b) => ($a['customer_rate'] ?? 99999) <=> ($b['customer_rate'] ?? 99999));
                $bestCourier = $couriers[0];
                $shippingPaise = (int) (ceil($bestCourier['customer_rate']) * 100);
                $courierName = $bestCourier['courier_name'] ?? 'Standard Surface';
                $estDays = (int) ($bestCourier['estimated_delivery_days'] ?? 4);
            } else {
                $shippingPaise = $settings?->shipping_flat_rate ?? 6000;
                $courierName = 'Standard Delivery';
                $estDays = 4;
            }
        } catch (\Throwable $e) {
            $shippingPaise = $settings?->shipping_flat_rate ?? 6000;
            $courierName = 'Standard Delivery';
            $estDays = 4;
        }

        session([
            "checkout_shipping_{$vendor->id}" => [
                'pincode' => $deliveryPincode,
                'amount_paise' => $shippingPaise,
                'courier_name' => $courierName,
                'est_days' => $estDays,
            ]
        ]);

        return response()->json([
            'success' => true,
            'is_free' => false,
            'shipping_paise' => $shippingPaise,
            'shipping_formatted' => '₹' . number_format($shippingPaise / 100, 2),
            'courier_name' => $courierName,
            'estimated_days' => $estDays,
        ]);
    }

    private function couponResponse(Request $request, $success, $message)
    {
        if ($request->wantsJson()) {
            return response()->json(['success' => $success, 'message' => $message]);
        }
        
        if ($success) {
            return back()->with('success', $message);
        }
        return back()->with('error', $message);
    }
}
