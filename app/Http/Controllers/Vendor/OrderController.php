<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderStatusLog;
use App\Models\PaymentClaim;
use App\Models\Shipment;
use App\Notifications\OrderStatusUpdated;
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
            'to_pack' => Order::where('vendor_id', $vendor->id)->whereIn('status', ['pending', 'processing'])->count(),
            'shipped' => Order::where('vendor_id', $vendor->id)->where('status', 'shipped')->count(),
            'delivered' => Order::where('vendor_id', $vendor->id)->where('status', 'delivered')->count(),
            'cancelled' => Order::where('vendor_id', $vendor->id)->where('status', 'cancelled')->count(),
        ];

        $query = Order::with(['user', 'items.product.images', 'items.variant', 'paymentClaim'])
            ->where('vendor_id', $vendor->id);

        // Apply status filter
        if ($status === 'to_pack') {
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
        $vendor = auth()->user()->vendor;
        if (!$vendor || $order->vendor_id !== $vendor->id) {
            abort(403, 'Unauthorized access to order.');
        }

        app(\App\Services\SettlementService::class)->autoClearDueOrders();
        $order->refresh();

        $order->load(['user', 'items.product.images', 'items.variant', 'paymentClaim', 'shipment', 'statusLogs']);

        // Calculate package weight
        $totalWeightGrams = 0;
        foreach ($order->items as $item) {
            $w = $item->variant->product->weight_grams ?? $item->product->weight_grams ?? 450;
            $totalWeightGrams += ($w * $item->quantity);
        }
        $weightKg = round(max(0.25, $totalWeightGrams / 1000), 2);

        // Fetch real-time available couriers & rates via Shiprocket (Bypassed for Food Orders per Decision D13)
        $ratesData = ['available_couriers' => [], 'recommended' => null];
        if (!$order->isFoodOrder()) {
            try {
                $rawAddr = $vendor->pickup_address;
                $pickupAddr = is_array($rawAddr) ? $rawAddr : (json_decode($rawAddr, true) ?: []);
                $pickupPincode = $pickupAddr['pincode'] ?? $pickupAddr['pin_code'] ?? '305801';

                $rawCustAddr = $order->address_snapshot;
                $custAddr = is_array($rawCustAddr) ? $rawCustAddr : (json_decode($rawCustAddr, true) ?: []);
                $deliveryPincode = $custAddr['pincode'] ?? $custAddr['pin_code'] ?? '400050';

                $ratesData = $shiprocket->checkRates($pickupPincode, $deliveryPincode, $weightKg, $order->payment_method === 'cod');
            } catch (\Throwable $e) {
                // Silently fallback if rate query fails
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
            if ($newStatus === 'processing' && !$order->packed_at) {
                $updateData['packed_at'] = now();
            } elseif ($newStatus === 'ready_for_pickup' && !$order->packed_at) {
                $updateData['packed_at'] = now();
            } elseif ($newStatus === 'shipped' && !$order->shipped_at) {
                $updateData['shipped_at'] = now();
            } elseif ($newStatus === 'delivered' && !$order->delivered_at) {
                $updateData['delivered_at'] = now();
            } elseif ($newStatus === 'cancelled' && $oldStatus !== 'cancelled') {
                $updateData['cancelled_at'] = now();
                $updateData['cancelled_by'] = 'vendor';
                $updateData['cancel_reason'] = $request->input('cancel_reason', 'Cancelled by vendor');

                // Restore stock to inventory (both variants and total_stock)
                \App\Services\StockManagerService::restoreOrderStock($order);
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
                $order->user->notify(new OrderStatusUpdated($order));
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
                $order->user->notify(new OrderStatusUpdated($order));
            } catch (\Throwable $e) {}

            return back()->with('success', "🚀 1-Click Shipment Booked Successfully! Courier: {$shipment->courier_name} | AWB: {$shipment->awb_code}. Doorstep pickup scheduled.");
        } catch (\Exception $e) {
            return back()->with('error', "Shipment booking failed: " . $e->getMessage());
        }
    }

    public function simulateShipping(Request $request, Order $order, ShiprocketService $shiprocket)
    {
        if (!app()->environment('local', 'testing')) {
            abort(404);
        }

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
                $order->update([
                    'status' => 'processing',
                    'packed_at' => $order->packed_at ?: now(),
                    'vendor_accepted_at' => now(),
                ]);

                OrderStatusLog::create([
                    'order_id' => $order->id,
                    'from_status' => $order->status,
                    'to_status' => 'processing',
                    'actor_type' => 'vendor',
                    'actor_id' => auth()->id(),
                    'note' => "COD Order accepted by vendor. Ready for courier packing and doorstep delivery.",
                    'ip_address' => $request->ip(),
                ]);

                DB::commit();

                // Notify customer
                try {
                    $order->user->notify(new OrderStatusUpdated($order));
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

            $order->update([
                'status' => 'processing',
                'packed_at' => $order->packed_at ?: now(),
                'vendor_accepted_at' => now(),
            ]);

            OrderStatusLog::create([
                'order_id' => $order->id,
                'from_status' => $order->status,
                'to_status' => 'processing',
                'actor_type' => 'vendor',
                'actor_id' => auth()->id(),
                'note' => "Order accepted by vendor. Instant payout of ₹" . number_format($payoutResult['amount'] / 100, 2) . " disbursed to {$payoutResult['vpa']} (Bank UTR: {$payoutResult['utr']}). Ready for courier packing.",
                'ip_address' => $request->ip(),
            ]);

            DB::commit();

            // Notify customer
            try {
                $order->user->notify(new OrderStatusUpdated($order));
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
                'from_status' => $order->status,
                'to_status' => 'cancelled',
                'actor_type' => 'vendor',
                'actor_id' => auth()->id(),
                'note' => "Order rejected by vendor before payout: {$reason}. Customer refund initiated.",
                'ip_address' => $request->ip(),
            ]);

            DB::commit();

            // Notify customer
            try {
                $order->user->notify(new OrderStatusUpdated($order));
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
        if (!app()->environment('local', 'testing')) {
            abort(404);
        }

        $vendor = auth()->user()->vendor;
        if (!$vendor || $order->vendor_id !== $vendor->id) {
            abort(403, 'Unauthorized access to order.');
        }

        $settlementService->markSettled($order, null, 'vendor_simulator');

        return back()->with('success', "⚡ Bank Settlement Simulation Complete! Funds credited to Buyz Current Account. Order is now UNLOCKED for Acceptance & Instant Payout!");
    }
}

