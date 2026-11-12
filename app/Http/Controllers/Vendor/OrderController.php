<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderStatusLog;
use App\Models\PaymentClaim;
use App\Models\Shipment;
use App\Notifications\OrderStatusUpdated;
use App\Services\Food\FoodMarketplaceService;
use App\Services\PayoutService;
use App\Services\SettlementService;
use App\Services\Shipping\ShiprocketService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $vendor = auth()->user()->vendor;
        if (!$vendor) abort(403);

        \App\Services\StockManagerService::releaseExpiredOrders(45);
        app(\App\Services\SettlementService::class)->autoClearDueOrders();

        $status = $request->query('status', 'all');
        $search = trim($request->query('search', ''));

        // Counts for pipeline filter tabs
        $counts = [
            'all' => Order::where('vendor_id', $vendor->id)->count(),
            'pending_payment' => Order::where('vendor_id', $vendor->id)
                ->where(function($q) {
                    $q->whereHas('paymentClaim', function($cq) {
                        $cq->whereIn('status', ['submitted', 'pending']);
                    })->orWhere(function($oq) {
                        $oq->where('payment_method', 'upi')->whereIn('payment_status', ['processing', 'pending']);
                    });
                })->count(),
            'to_pack' => Order::where('vendor_id', $vendor->id)->whereIn('status', ['pending', 'processing'])->count(),
            'shipped' => Order::where('vendor_id', $vendor->id)->where('status', 'shipped')->count(),
            'delivered' => Order::where('vendor_id', $vendor->id)->where('status', 'delivered')->count(),
            'cancelled' => Order::where('vendor_id', $vendor->id)->where('status', 'cancelled')->count(),
        ];

        $query = Order::with(['user', 'vendor', 'items.product.images', 'items.variant', 'paymentClaim', 'deliveryAssignment.deliveryPartner'])
            ->where('vendor_id', $vendor->id);

        // Apply status filter
        if ($status === 'pending_payment') {
            $query->where(function($q) {
                $q->whereHas('paymentClaim', function($cq) {
                    $cq->whereIn('status', ['submitted', 'pending']);
                })->orWhere(function($oq) {
                    $oq->where('payment_method', 'upi')->whereIn('payment_status', ['processing', 'pending']);
                });
            });
        } elseif ($status === 'to_pack') {
            $query->whereIn('status', ['pending', 'processing']);
        } elseif ($status === 'shipped') {
            $query->where('status', 'shipped');
        } elseif ($status === 'delivered') {
            $query->where('status', 'delivered');
        } elseif ($status === 'cancelled') {
            $query->where('status', 'cancelled');
        }

        // Apply search filter (order number, courier tracking, customer name, email, phone)
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'LIKE', "%{$search}%")
                  ->orWhere('tracking_number', 'LIKE', "%{$search}%")
                  ->orWhere('courier_name', 'LIKE', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'LIKE', "%{$search}%")
                         ->orWhere('email', 'LIKE', "%{$search}%")
                         ->orWhere('phone', 'LIKE', "%{$search}%");
                  });
            });
        }

        $orders = $query->latest()->paginate(20)->withQueryString();
            
        return view('vendor.orders.index', compact('orders', 'counts', 'status', 'search'));
    }

    public function show(Order $order, ShiprocketService $shiprocket)
    {
        $vendor = auth()->user()?->vendor;
        if (!$vendor || $order->vendor_id !== $vendor->id) {
            abort(403, 'Unauthorized access to order.');
        }

        try {
            app(\App\Services\SettlementService::class)->autoClearDueOrders();
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Settlement autoClearDueOrders error: ' . $e->getMessage());
        }

        $order->refresh();
        $order->load(['user', 'vendor.settings', 'items.product.images', 'items.variant.product', 'paymentClaim', 'shipment', 'statusLogs', 'deliveryAssignment.deliveryPartner']);

        // Calculate package weight safely
        $totalWeightGrams = 0;
        foreach ($order->items as $item) {
            $w = $item->variant?->product?->weight_grams ?? $item->product?->weight_grams ?? 450;
            $totalWeightGrams += ($w * ($item->quantity ?? 1));
        }
        $weightKg = round(max(0.25, $totalWeightGrams / 1000), 2);

        // Fetch real-time available couriers & rates via Shiprocket (Bypassed for Food Orders per Decision D13)
        $ratesData = ['available_couriers' => [], 'recommended' => null];
        if (!$order->isFoodOrder()) {
            try {
                $rawAddr = $vendor->pickup_address;
                $pickupAddr = is_array($rawAddr) ? $rawAddr : (json_decode($rawAddr ?? '[]', true) ?: []);
                $pickupPincode = $pickupAddr['pincode'] ?? $pickupAddr['pin_code'] ?? '305801';

                $rawCustAddr = $order->address_snapshot;
                $custAddr = is_array($rawCustAddr) ? $rawCustAddr : (json_decode($rawCustAddr ?? '[]', true) ?: []);
                $deliveryPincode = $custAddr['pincode'] ?? $custAddr['pin_code'] ?? '400050';

                $res = $shiprocket->checkRates($pickupPincode, $deliveryPincode, $weightKg, $order->payment_method === 'cod');
                if (is_array($res) && isset($res['available_couriers']) && is_array($res['available_couriers'])) {
                    $ratesData = $res;
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::info('Shiprocket checkRates fallback: ' . $e->getMessage());
            }
        }

        $shippingMode = config('shipping.mode', 'live');

        return view('vendor.orders.show', compact('order', 'weightKg', 'ratesData', 'shippingMode'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $vendor = auth()->user()->vendor;
        if (!$vendor || $order->vendor_id !== $vendor->id) {
            abort(403, 'Unauthorized access to order.');
        }

        $validated = $request->validate([
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled,ready_for_pickup',
            'courier_name' => 'nullable|string|max:100',
            'tracking_number' => 'nullable|string|max:100',
            'tracking_url' => 'nullable|url|max:500',
            'cancel_reason' => 'nullable|string|max:500',
            'payment_status' => 'nullable|in:processing,paid,failed',
            'rejection_reason' => 'nullable|string|max:500',
            'kitchen_notes' => 'nullable|string|max:500',
            'estimated_delivery_time' => 'nullable|date',
        ]);

        $oldStatus = $order->status;
        $newStatus = $validated['status'];

        if ($order->isFoodOrder()) {
            $foodService = app(FoodMarketplaceService::class);
            if (!$foodService->canTransitionFoodOrderStatus($oldStatus, $newStatus)) {
                return back()->with('error', "Invalid status transition from '{$oldStatus}' to '{$newStatus}' for a food order.");
            }
        }

        // Guard: Cannot ship or deliver non-COD order if payment is not verified as 'paid'
        if (in_array($newStatus, ['shipped', 'delivered', 'ready_for_pickup'])) {
            $effectivePaymentStatus = $request->input('payment_status', $order->payment_status);
            if ($order->payment_method !== 'cod' && $effectivePaymentStatus !== 'paid') {
                return back()->with('error', "Cannot mark order as {$newStatus}: Payment status is '{$effectivePaymentStatus}'. For online/UPI orders, you must verify payment as 'Paid' before dispatch. (Only Cash on Delivery orders can be dispatched before payment).");
            }
        }

        // Auto-generate live tracking link if courier and AWB are provided but URL is omitted
        $awb = trim($validated['tracking_number'] ?? '');
        $courier = strtolower(trim($validated['courier_name'] ?? ''));
        $trackingUrl = $validated['tracking_url'] ?? null;

        if ($awb && empty($trackingUrl)) {
            if (str_contains($courier, 'delhivery')) {
                $trackingUrl = "https://www.delhivery.com/track/package/{$awb}";
            } elseif (str_contains($courier, 'blue') || str_contains($courier, 'dart')) {
                $trackingUrl = "https://www.bluedart.com/tracking?trackNumber={$awb}";
            } elseif (str_contains($courier, 'dtdc')) {
                $trackingUrl = "https://www.dtdc.in/tracking/shipment-tracking.asp?awb={$awb}";
            } elseif (str_contains($courier, 'post') || str_contains($courier, 'speed')) {
                $trackingUrl = "https://www.indiapost.gov.in/_layouts/15/dpt.cept.tracking/trackconsignment.aspx?consignmentNumber={$awb}";
            } elseif (str_contains($courier, 'shadowfax')) {
                $trackingUrl = "https://tracker.shadowfax.in/#/track?awb={$awb}";
            } elseif (str_contains($courier, 'xpressbees')) {
                $trackingUrl = "https://www.xpressbees.com/track?tracking_id={$awb}";
            } elseif (str_contains($courier, 'ekart')) {
                $trackingUrl = "https://ekartlogistics.com/shipmenttrack/{$awb}";
            }
        }

        DB::beginTransaction();
        try {
            $updateData = [
                'status' => $newStatus,
                'courier_name' => $validated['courier_name'] ?? $order->courier_name,
                'tracking_number' => $validated['tracking_number'] ?? $order->tracking_number,
                'tracking_url' => $trackingUrl ?? $order->tracking_url,
            ];

            if ($request->filled('kitchen_notes')) {
                $updateData['kitchen_notes'] = $validated['kitchen_notes'];
            }
            if ($request->filled('estimated_delivery_time')) {
                $updateData['estimated_delivery_time'] = $validated['estimated_delivery_time'];
            }

            // Timestamps based on status
            if ($newStatus === 'processing') {
                if (!$order->packed_at) $updateData['packed_at'] = now();
                if ($order->isFoodOrder()) {
                    $updateData['accepted_at'] = $order->accepted_at ?: now();
                    $updateData['preparing_at'] = $order->preparing_at ?: now();
                    $updateData['food_fulfillment_stage'] = 'preparing';
                }
            } elseif ($newStatus === 'ready_for_pickup') {
                if (!$order->packed_at) $updateData['packed_at'] = now();
                if ($order->isFoodOrder()) {
                    $updateData['accepted_at'] = $order->accepted_at ?: now();
                    $updateData['preparing_at'] = $order->preparing_at ?: now();
                    $updateData['ready_at'] = $order->ready_at ?: now();
                    $updateData['food_fulfillment_stage'] = 'ready';
                }
            } elseif ($newStatus === 'shipped') {
                if (!$order->shipped_at) $updateData['shipped_at'] = now();
                if ($order->isFoodOrder()) {
                    $updateData['out_for_delivery_at'] = $order->out_for_delivery_at ?: now();
                    $updateData['food_fulfillment_stage'] = 'out_for_delivery';
                }
            } elseif ($newStatus === 'delivered') {
                if (!$order->delivered_at) $updateData['delivered_at'] = now();
                if ($order->isFoodOrder()) {
                    $updateData['food_fulfillment_stage'] = 'delivered';
                }
            } elseif ($newStatus === 'cancelled' && $oldStatus !== 'cancelled') {
                $updateData['cancelled_at'] = now();
                $updateData['cancelled_by'] = 'vendor';
                $updateData['cancel_reason'] = $request->input('cancel_reason', 'Cancelled by vendor');
                if ($order->isFoodOrder()) {
                    $updateData['food_fulfillment_stage'] = 'cancelled';
                    if ($order->payment_status === 'paid') {
                        $updateData['payment_status'] = 'refund_pending';
                    }
                }

                // Restore stock to inventory (both variants and total_stock)
                \App\Services\StockManagerService::restoreOrderStock($order);

                // Reverse recorded commission idempotently if order had an active commission charge
                app(\App\Services\Monetization\CommissionService::class)->reverseOrderCommission($order, 'Order cancelled by vendor', auth()->id());
            }

            // Payment status updates
            if ($request->filled('payment_status')) {
                $updateData['payment_status'] = $request->payment_status;
                if ($order->paymentClaim) {
                    $claimStatus = match ($request->payment_status) {
                        'paid' => 'verified',
                        'failed' => 'rejected',
                        default => 'submitted',
                    };
                    $order->paymentClaim->update([
                        'status' => $claimStatus,
                        'verified_by' => auth()->id(),
                        'verified_at' => now(),
                        'rejection_reason' => $request->payment_status === 'failed' 
                            ? $request->input('rejection_reason', 'Unverified UTR or payment not received') 
                            : null,
                    ]);
                }
            }

            $order->update($updateData);

            if ($order->payment_status === 'paid' && $newStatus !== 'cancelled') {
                app(\App\Services\Monetization\CommissionService::class)->recordOrderCommission($order);
            }

            // Log status transition
            if ($oldStatus !== $newStatus) {
                OrderStatusLog::create([
                    'order_id' => $order->id,
                    'from_status' => $oldStatus,
                    'to_status' => $newStatus,
                    'actor_type' => 'vendor',
                    'actor_id' => auth()->id(),
                    'note' => $newStatus === 'cancelled' ? $order->cancel_reason : "Status updated to {$newStatus}",
                    'ip_address' => $request->ip(),
                ]);
            }

            DB::commit();

            // Notify user
            try {
                $order->user?->notify(new OrderStatusUpdated($order));
            } catch (\Throwable $e) {}

            return back()->with('success', 'Order status updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to update order: ' . $e->getMessage());
        }
    }

    public function invoice(Order $order)
    {
        $vendor = auth()->user()->vendor;
        if (!$vendor || $order->vendor_id !== $vendor->id) {
            abort(403, 'Unauthorized access to invoice.');
        }

        $order->load(['user', 'items.product', 'vendor']);
        return view('orders.invoice', compact('order'));
    }

    public function shippingLabel(Order $order)
    {
        $vendor = auth()->user()->vendor;
        if (!$vendor || $order->vendor_id !== $vendor->id) {
            abort(403, 'Unauthorized access to shipping label.');
        }

        $order->load(['user', 'items.product', 'items.variant', 'vendor']);
        return view('vendor.orders.shipping-label', compact('order', 'vendor'));
    }

    public function shipAutomated(Request $request, Order $order, ShiprocketService $shiprocket)
    {
        $vendor = auth()->user()->vendor;
        if (!$vendor || $order->vendor_id !== $vendor->id) {
            abort(403, 'Unauthorized access to order.');
        }

        // Guard: Cannot ship online/UPI orders if payment is not verified as 'paid'
        if ($order->payment_method !== 'cod' && $order->payment_status !== 'paid') {
            return back()->with('error', "Cannot dispatch order: Payment status is '{$order->payment_status}'. Please verify payment as 'Paid' before booking courier. (Only Cash on Delivery orders can be dispatched before payment).");
        }

        try {
            $courierId = $request->input('courier_company_id');
            $customPackage = [
                'weight' => $request->filled('package_weight') ? (float) $request->input('package_weight') : null,
                'length' => $request->filled('package_length') ? (int) $request->input('package_length') : 22,
                'breadth' => $request->filled('package_breadth') ? (int) $request->input('package_breadth') : 18,
                'height' => $request->filled('package_height') ? (int) $request->input('package_height') : 8,
            ];
            $shipment = $shiprocket->createAndBookShipment($order, $courierId ? (int) $courierId : null, $customPackage);

            // Log status transition
            OrderStatusLog::create([
                'order_id' => $order->id,
                'from_status' => $order->status,
                'to_status' => 'shipped',
                'actor_type' => 'vendor',
                'actor_id' => auth()->id(),
                'note' => "1-Click automated shipping booked via {$shipment->courier_name}. AWB: {$shipment->awb_code}.",
                'ip_address' => $request->ip(),
            ]);

            // Notify customer
            try {
                $order->user?->notify(new OrderStatusUpdated($order));
            } catch (\Throwable $e) {}

            return back()->with('success', "🚀 1-Click Shipment Booked Successfully! Courier: {$shipment->courier_name} | AWB: {$shipment->awb_code}. Doorstep pickup scheduled.");
        } catch (\Exception $e) {
            return back()->with('error', "Shipment booking failed: " . $e->getMessage());
        }
    }

    public function simulateShipping(Request $request, Order $order, ShiprocketService $shiprocket)
    {
        $vendor = auth()->user()->vendor;
        if (!$vendor || $order->vendor_id !== $vendor->id) {
            abort(403, 'Unauthorized access to order.');
        }

        $targetState = $request->input('target_status', 'in_transit');
        $shipment = $shiprocket->simulateState($order, $targetState);

        OrderStatusLog::create([
            'order_id' => $order->id,
            'from_status' => $order->status,
            'to_status' => ($targetState === 'delivered' ? 'delivered' : 'shipped'),
            'actor_type' => 'vendor',
            'actor_id' => auth()->id(),
            'note' => "Simulated courier state transition to: {$targetState}",
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', "⚡ Simulated courier status updated to: {$targetState} (AWB: {$shipment->awb_code})");
    }

    /**
     * Vendor accepts the order once bank settlement is cleared.
     * Instantly triggers 95% net payout to the vendor's UPI ID.
     */
    public function acceptOrder(Request $request, Order $order, PayoutService $payoutService)
    {
        $vendor = auth()->user()->vendor;
        if (!$vendor || $order->vendor_id !== $vendor->id) {
            abort(403, 'Unauthorized access to order.');
        }

        // For COD orders: no online gateway settlement is required before accepting to pack
        if ($order->payment_method === 'cod') {
            DB::beginTransaction();
            try {
                $oldStatus = $order->status;
                $acceptPayload = [
                    'status' => 'processing',
                    'packed_at' => $order->packed_at ?: now(),
                    'vendor_accepted_at' => now(),
                ];
                if ($order->isFoodOrder()) {
                    $acceptPayload['accepted_at'] = $order->accepted_at ?: now();
                    $acceptPayload['preparing_at'] = $order->preparing_at ?: now();
                    $acceptPayload['food_fulfillment_stage'] = 'preparing';
                }
                $order->update($acceptPayload);

                OrderStatusLog::create([
                    'order_id' => $order->id,
                    'from_status' => $oldStatus,
                    'to_status' => 'processing',
                    'actor_type' => 'vendor',
                    'actor_id' => auth()->id(),
                    'note' => "COD Order accepted by vendor. Ready for courier packing and doorstep delivery.",
                    'ip_address' => $request->ip(),
                ]);

                DB::commit();

                // Notify customer
                try {
                    $order->user?->notify(new OrderStatusUpdated($order));
                } catch (\Throwable $e) {}

                return back()->with('success', "🎉 COD Order Accepted! Please pack the garment for courier pickup. Cash will be collected upon delivery.");
            } catch (\Exception $e) {
                DB::rollBack();
                return back()->with('error', "Failed to accept COD order: " . $e->getMessage());
            }
        }

        // Guard 1: Cannot accept until bank settlement is cleared
        if ($order->settlement_status !== 'cleared') {
            $unlockTime = $order->settlement_expected_at ? $order->settlement_expected_at->format('M d, Y h:i A') : 'the next banking day';
            return back()->with('error', "Cannot accept order yet: Customer payment settlement is still clearing with PhonePe. Funds will unlock on {$unlockTime}.");
        }

        // Guard 2: Verify vendor has configured a UPI ID
        $settings = $vendor->settings;
        if (!$settings || empty(trim($settings->upi_id ?? ''))) {
            return back()->with('error', "Please configure your UPI ID under Operations & Payments settings before accepting orders.");
        }

        // Guard 3: Prevent duplicate acceptance / payouts
        if ($order->vendor_payout_status === 'paid') {
            return back()->with('info', "Order is already accepted and payout was previously transferred.");
        }

        DB::beginTransaction();
        try {
            // Disburse instant payout to vendor's UPI ID
            $payoutResult = $payoutService->disburseVendorPayout($order);

            $oldStatus = $order->status;
            $acceptOnlinePayload = [
                'status' => 'processing',
                'packed_at' => $order->packed_at ?: now(),
                'vendor_accepted_at' => now(),
            ];
            if ($order->isFoodOrder()) {
                $acceptOnlinePayload['accepted_at'] = $order->accepted_at ?: now();
                $acceptOnlinePayload['preparing_at'] = $order->preparing_at ?: now();
                $acceptOnlinePayload['food_fulfillment_stage'] = 'preparing';
            }
            $order->update($acceptOnlinePayload);

            OrderStatusLog::create([
                'order_id' => $order->id,
                'from_status' => $oldStatus,
                'to_status' => 'processing',
                'actor_type' => 'vendor',
                'actor_id' => auth()->id(),
                'note' => "Order accepted by vendor. Instant payout of ₹" . number_format($payoutResult['amount'] / 100, 2) . " disbursed to {$payoutResult['vpa']} (Bank UTR: {$payoutResult['utr']}). Ready for courier packing.",
                'ip_address' => $request->ip(),
            ]);

            DB::commit();

            // Notify customer
            try {
                $order->user?->notify(new OrderStatusUpdated($order));
            } catch (\Throwable $e) {}

            // Notify Admin with Vendor UPI and exact payout amount
            try {
                $adminEmail = config('mail.admin_email', 'crab.hashir@gmail.com');
                \Illuminate\Support\Facades\Notification::route('mail', $adminEmail)
                    ->notify(new \App\Notifications\AdminPayoutDueNotification($order, $payoutResult));
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning("Failed to send admin payout alert: " . $e->getMessage());
            }

            return back()->with('success', "🎉 Order Accepted! ₹" . number_format($payoutResult['amount'] / 100, 2) . " has been recorded for payout to your UPI ({$payoutResult['vpa']}). Please pack the garment.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', "Payout disbursement failed: " . $e->getMessage());
        }
    }

    /**
     * Vendor rejects the order (e.g. out of stock / defect).
     * Restores stock and marks customer refund initiated without sending any money to vendor.
     */
    public function rejectOrder(Request $request, Order $order)
    {
        $vendor = auth()->user()->vendor;
        if (!$vendor || $order->vendor_id !== $vendor->id) {
            abort(403, 'Unauthorized access to order.');
        }

        // Guard: Cannot reject if payout has already been sent
        if ($order->vendor_payout_status === 'paid') {
            return back()->with('error', "Cannot reject order: Payout has already been transferred to your account.");
        }

        $reason = trim($request->input('cancel_reason', 'Item out of stock or defect found in inventory'));

        DB::beginTransaction();
        try {
            $oldStatus = $order->status;
            $order->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'cancelled_by' => 'vendor',
                'cancel_reason' => $reason,
                'vendor_rejected_at' => now(),
                'vendor_rejection_reason' => $reason,
                'vendor_payout_status' => 'refunded',
            ]);

            // Restore inventory stock
            \App\Services\StockManagerService::restoreOrderStock($order);

            OrderStatusLog::create([
                'order_id' => $order->id,
                'from_status' => $oldStatus,
                'to_status' => 'cancelled',
                'actor_type' => 'vendor',
                'actor_id' => auth()->id(),
                'note' => "Order rejected by vendor before payout: {$reason}. Customer refund initiated.",
                'ip_address' => $request->ip(),
            ]);

            DB::commit();

            // Notify customer
            try {
                $order->user?->notify(new OrderStatusUpdated($order));
            } catch (\Throwable $e) {}

            return back()->with('success', "Order #{$order->order_number} has been rejected. Stock restored to inventory and customer refund initiated.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', "Rejection failed: " . $e->getMessage());
        }
    }

    /**
     * Testing Simulator: Instantly simulate bank settlement clearance
     */
    public function simulateSettlement(Request $request, Order $order, SettlementService $settlementService)
    {
        $vendor = auth()->user()->vendor;
        if (!$vendor || $order->vendor_id !== $vendor->id) {
            abort(403, 'Unauthorized access to order.');
        }

        $settlementService->markSettled($order, null, 'vendor_simulator');

        return back()->with('success', "⚡ Bank Settlement Simulation Complete! Funds credited to K71 Platform Settlement Account. Order is now UNLOCKED for Acceptance & Instant Payout!");
    }

    /**
     * Real-time polling endpoint for incoming orders with loud audio chime
     */
    public function checkNewOrders(Request $request)
    {
        $vendor = auth()->user()->vendor;
        if (!$vendor) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $lastOrderId = (int) $request->query('last_order_id', 0);
        $pendingOrders = Order::where('vendor_id', $vendor->id)
            ->whereIn('status', ['pending', 'processing'])
            ->with(['items.product', 'user'])
            ->latest('id')
            ->get();

        $newOrders = $lastOrderId > 0 
            ? $pendingOrders->where('id', '>', $lastOrderId)->values()
            : collect();

        $latestOrder = Order::where('vendor_id', $vendor->id)->latest('id')->first();

        return response()->json([
            'pending_count' => $pendingOrders->count(),
            'has_new' => $newOrders->isNotEmpty(),
            'new_orders' => $newOrders->map(fn($o) => [
                'id' => $o->id,
                'order_number' => $o->order_number,
                'total' => '₹' . number_format(($o->total_amount ?? 0) / 100, 2),
                'items_count' => $o->items->count(),
                'customer' => $o->user?->name ?? 'Guest Buyer',
                'created_at' => $o->created_at?->diffForHumans() ?? 'Just now',
                'url' => route('vendor.orders.show', $o->id),
            ]),
            'latest_order_id' => $latestOrder?->id ?? 0,
        ]);
    }

    /**
     * Vendor approve customer payment proof (UTR / Screenshot)
     */
    public function approvePaymentProof(Request $request, Order $order)
    {
        $vendor = auth()->user()->vendor;
        if (!$vendor || $order->vendor_id !== $vendor->id) {
            abort(403, 'Unauthorized access to order.');
        }

        DB::beginTransaction();
        try {
            if ($order->paymentClaim) {
                $order->paymentClaim->update([
                    'status' => 'approved',
                    'verified_at' => now(),
                ]);
            }

            $oldStatus = $order->status;
            $newStatus = $oldStatus === 'pending' ? 'processing' : $oldStatus;

            $order->update([
                'payment_status' => 'paid',
                'status' => $newStatus,
            ]);

            app(\App\Services\Monetization\CommissionService::class)->recordOrderCommission($order);

            \App\Models\OrderStatusLog::create([
                'order_id' => $order->id,
                'from_status' => $oldStatus,
                'to_status' => $newStatus,
                'actor_type' => 'vendor',
                'actor_id' => auth()->id(),
                'note' => "Payment verified and approved by vendor. Bank UTR/Proof confirmed.",
                'ip_address' => $request->ip(),
            ]);

            DB::commit();
            return back()->with('success', "Payment approved successfully! Order #{$order->order_number} is now marked as Paid.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', "Failed to approve payment: " . $e->getMessage());
        }
    }

    /**
     * Vendor reject customer payment proof (UTR / Screenshot)
     */
    public function rejectPaymentProof(Request $request, Order $order)
    {
        $vendor = auth()->user()->vendor;
        if (!$vendor || $order->vendor_id !== $vendor->id) {
            abort(403, 'Unauthorized access to order.');
        }

        $reason = $request->input('reason', $request->input('rejection_reason', 'Transaction ID / UTR not found in bank statement'));

        DB::beginTransaction();
        try {
            if ($order->paymentClaim) {
                $order->paymentClaim->update([
                    'status' => 'rejected',
                    'rejection_reason' => $reason,
                ]);
            }

            $order->update([
                'payment_status' => 'failed',
            ]);

            \App\Models\OrderStatusLog::create([
                'order_id' => $order->id,
                'from_status' => $order->status,
                'to_status' => $order->status,
                'actor_type' => 'vendor',
                'actor_id' => auth()->id(),
                'note' => "Payment proof rejected by vendor: {$reason}",
                'ip_address' => $request->ip(),
            ]);

            DB::commit();
            return back()->with('success', "Payment proof rejected for Order #{$order->order_number}.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', "Failed to reject payment: " . $e->getMessage());
        }
    }

    /**
     * Get nearby delivery partners for fast dispatch (Blinkit speed)
     */
    public function getNearbyDeliveryPartners(Order $order)
    {
        $vendor = auth()->user()->vendor;
        if (!$vendor || $order->vendor_id !== $vendor->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $vLat = null;
        $vLng = null;
        if (is_array($vendor->pickup_address) && !empty($vendor->pickup_address['lat']) && !empty($vendor->pickup_address['lng'])) {
            $vLat = (float) $vendor->pickup_address['lat'];
            $vLng = (float) $vendor->pickup_address['lng'];
        }

        $partners = \App\Models\DeliveryPartner::realFleet()->where('is_online', true)
            ->with(['activeAssignments' => function($q) {
                $q->with('order');
            }])
            ->get()
            ->map(function ($p) use ($vLat, $vLng) {
                $dist = $p->distanceTo($vLat, $vLng);
                $fare = $p->calculateDeliveryFee($dist);
                $isBusy = $p->activeAssignments->isNotEmpty();
                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'phone' => $p->phone,
                    'vehicle_type' => ucfirst($p->vehicle_type ?? 'bike'),
                    'vehicle_number' => $p->vehicle_number ?: 'Plate not added',
                    'rating' => number_format((float) ($p->rating ?? 5.0), 1),
                    'total_deliveries' => (int) ($p->total_deliveries ?? 0),
                    'distance_km' => $dist,
                    'eta_minutes' => $dist !== null ? max(3, round($dist * 3.5)) : null,
                    'base_delivery_fee' => (float) ($p->base_delivery_fee ?? 30.00),
                    'per_km_rate' => (float) ($p->per_km_rate ?? 15.00),
                    'calculated_payout' => $fare,
                    'fare_formatted' => '₹' . number_format($fare, 2),
                    'is_busy' => $isBusy,
                    'status_label' => $isBusy ? 'On Active Delivery' : 'Available for Pickup',
                ];
            })
            ->sortBy(fn ($p) => $p['distance_km'] ?? 999999)
            ->values();

        return response()->json([
            'partners' => $partners,
            'vendor_location' => is_array($vendor->pickup_address) ? ($vendor->pickup_address['line_1'] ?? $vendor->shop_name) : $vendor->shop_name,
        ]);
    }

    /**
     * Confirm order and dispatch to chosen delivery partner
     */
    public function assignDeliveryPartner(Request $request, Order $order)
    {
        $vendor = auth()->user()->vendor;
        if (!$vendor || $order->vendor_id !== $vendor->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $partnerId = $request->input('delivery_partner_id');
        $partner = \App\Models\DeliveryPartner::realFleet()->findOrFail($partnerId);

        $vLat = null;
        $vLng = null;
        if (is_array($vendor->pickup_address) && !empty($vendor->pickup_address['lat']) && !empty($vendor->pickup_address['lng'])) {
            $vLat = (float) $vendor->pickup_address['lat'];
            $vLng = (float) $vendor->pickup_address['lng'];
        }

        $dist = $partner->distanceTo($vLat, $vLng);
        $fare = $partner->calculateDeliveryFee($dist);

        $assignment = \App\Models\DeliveryAssignment::create([
            'order_id' => $order->id,
            'delivery_partner_id' => $partner->id,
            'vendor_id' => $vendor->id,
            'status' => 'assigned',
            'pickup_distance_km' => $dist,
            'payout_amount' => $fare,
            'assigned_at' => now(),
        ]);

        $oldStatus = $order->status;
        $order->update([
            'status' => 'processing',
        ]);

        $distText = $dist !== null ? "{$dist} km away • " : '';
        \App\Models\OrderStatusLog::create([
            'order_id' => $order->id,
            'from_status' => $oldStatus,
            'to_status' => 'processing',
            'actor_type' => 'vendor',
            'actor_id' => auth()->id(),
            'note' => "Assigned to Fast Delivery Partner {$partner->name} ({$distText}₹{$fare} delivery fare). Pickup alert ring dispatched.",
            'ip_address' => $request->ip(),
        ]);

        return response()->json([
            'success' => true,
            'status' => 'success',
            'message' => "Order dispatched to {$partner->name}! Alert ring sent to delivery partner.",
            'partner' => [
                'name' => $partner->name,
                'phone' => $partner->phone,
                'vehicle' => $partner->vehicle_number ?: 'Plate not added',
                'fare' => '₹' . number_format($fare, 2),
                'distance' => $dist !== null ? ($dist . ' km') : 'Distance pending',
            ]
        ]);
    }

    /**
     * Dedicated full-page Delivery Fleet and Delivery Boy Management for Food and Feasto vendors
     */
    public function deliveryPartnersIndex(Request $request)
    {
        $vendor = auth()->user()->vendor;
        if (!$vendor) abort(403);

        $vLat = null;
        $vLng = null;
        if (is_array($vendor->pickup_address) && !empty($vendor->pickup_address['lat']) && !empty($vendor->pickup_address['lng'])) {
            $vLat = (float) $vendor->pickup_address['lat'];
            $vLng = (float) $vendor->pickup_address['lng'];
        }

        $partners = \App\Models\DeliveryPartner::realFleet()->with(['assignments' => function($q) {
            $q->whereIn('status', ['assigned', 'accepted', 'picked_up'])->with('order');
        }])->get()->map(function($p) use ($vLat, $vLng) {
            $activeAssignment = $p->assignments->first(function($a) {
                return in_array($a->status, ['assigned', 'accepted', 'picked_up']);
            });
            $p->active_assignment = $activeAssignment;
            $p->is_busy = !is_null($activeAssignment);
            $p->distance_km = $p->distanceTo($vLat, $vLng);
            $p->eta_minutes = $p->distance_km !== null ? max(3, (int) round($p->distance_km * 3.5)) : null;
            return $p;
        })->sortBy(fn ($p) => [$p->is_online ? 0 : 1, $p->distance_km ?? 999999])->values();

        $activeOrdersNeedingDispatch = Order::where('vendor_id', $vendor->id)
            ->whereIn('status', ['pending', 'processing'])
            ->where(function($q) {
                $q->whereDoesntHave('deliveryAssignment')
                  ->orWhereHas('deliveryAssignment', function($aq) {
                      $aq->whereIn('status', ['cancelled', 'failed']);
                  });
            })
            ->latest()
            ->get();

        return view('vendor.delivery-partners.index', compact('vendor', 'partners', 'activeOrdersNeedingDispatch'));
    }
}

