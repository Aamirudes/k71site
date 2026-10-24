<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderStatusLog;
use App\Models\VendorSetting;
use App\Notifications\OrderStatusUpdated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function success(Order $order)
    {
        // Security check (Authenticated user or Guest Order Session)
        if ($order->user_id !== Auth::id() && !session("guest_order_{$order->id}")) {
            abort(403);
        }

        return view('orders.success', compact('order'));
    }

    public function index()
    {
        // Customer's list of orders
        $orders = Order::with(['vendor.settings', 'items.product.images', 'items.variant', 'paymentClaim', 'shipment'])
            ->where('user_id', Auth::id())
            ->latest()
            ->paginate(10);
            
        return view('orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        if ($order->user_id !== Auth::id() && !session("guest_order_{$order->id}")) {
            abort(403);
        }
        
        $order->load(['vendor.settings', 'items.product.images', 'items.variant', 'paymentClaim', 'shipment']);
        
        return view('orders.show', compact('order'));
    }

    public function cancel(Request $request, Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        // Check if order is already cancelled or final
        if (in_array($order->status, ['cancelled', 'shipped', 'out_for_delivery', 'delivered', 'returned', 'refunded'])) {
            return back()->with('error', 'This order cannot be cancelled because it is already ' . ucfirst($order->status) . '.');
        }

        // Check vendor cancellation policy
        $vendorSettings = VendorSetting::where('vendor_id', $order->vendor_id)->first();
        if ($vendorSettings && isset($vendorSettings->allow_customer_cancellation) && !$vendorSettings->allow_customer_cancellation) {
            return back()->with('error', 'This vendor does not allow direct cancellation. Please contact store support.');
        }

        // Check cancellation window if configured
        if ($vendorSettings && !empty($vendorSettings->cancellation_window_hours)) {
            $hoursSinceOrder = $order->created_at->diffInHours(now());
            if ($hoursSinceOrder > $vendorSettings->cancellation_window_hours) {
                return back()->with('error', "The cancellation window ({$vendorSettings->cancellation_window_hours} hours) for this order has expired.");
            }
        }

        $oldStatus = $order->status;
        $reason = $request->input('reason', 'Cancelled by customer');

        DB::beginTransaction();
        try {
            $updatePayload = [
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'cancelled_by' => 'customer',
                'cancel_reason' => $reason,
            ];

            // If customer paid online, flag for refund
            if ($order->payment_status === 'paid') {
                $updatePayload['payment_status'] = 'refund_pending';
            }

            // If vendor payout was already disbursed, flag for reversal
            if ($order->vendor_payout_status === 'paid') {
                $updatePayload['vendor_payout_status'] = 'reversal_pending';
            }

            $order->update($updatePayload);

            // Cancel Shiprocket consignment if shipment was created
            if ($order->shipment && $order->shipment->status !== 'cancelled') {
                try {
                    app(\App\Services\Shipping\ShiprocketService::class)->cancelShipment($order->shipment);
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::warning("Failed to cancel Shiprocket shipment on order cancel: " . $e->getMessage());
                }
            }

            // Restore product inventory stock (both variant and total_stock)
            \App\Services\StockManagerService::restoreOrderStock($order);

            // Log status history
            $refundNote = ($order->payment_status === 'paid' || $updatePayload['payment_status'] ?? '' === 'refund_pending')
                ? " (Online payment of ₹" . number_format($order->total_amount / 100, 2) . " flagged for refund)"
                : "";

            OrderStatusLog::create([
                'order_id' => $order->id,
                'from_status' => $oldStatus,
                'to_status' => 'cancelled',
                'actor_type' => 'customer',
                'actor_id' => Auth::id(),
                'note' => $reason . $refundNote,
                'ip_address' => $request->ip(),
            ]);

            DB::commit();

            // Send notification to customer
            try {
                $order->user->notify(new OrderStatusUpdated($order));
            } catch (\Throwable $e) {}

            // Send email alert to Admin if online refund is required
            if ($order->payment_method === 'phonepe' || ($updatePayload['payment_status'] ?? '') === 'refund_pending') {
                try {
                    $adminEmail = config('mail.admin_email', 'crab.hashir@gmail.com');
                    \Illuminate\Support\Facades\Mail::raw(
                        "Order #{$order->order_number} has been cancelled by the customer.\n\n" .
                        "Payment Method: " . strtoupper($order->payment_method) . "\n" .
                        "Total Amount to Refund: ₹" . number_format($order->total_amount / 100, 2) . "\n" .
                        "Customer Name: {$order->user->name} ({$order->user->email})\n" .
                        "Cancellation Reason: {$reason}\n\n" .
                        "Please review and initiate the refund via your PhonePe Merchant Dashboard.",
                        function ($m) use ($adminEmail, $order) {
                            $m->to($adminEmail)->subject("Action Required: Customer Cancelled Order #{$order->order_number} (Refund Pending)");
                        }
                    );
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::warning("Failed to send admin refund alert: " . $e->getMessage());
                }
            }

            $successMsg = ($order->payment_method === 'phonepe' || ($updatePayload['payment_status'] ?? '') === 'refund_pending')
                ? "Order #{$order->order_number} has been cancelled. Your refund of ₹" . number_format($order->total_amount / 100, 2) . " has been initiated and will credit within 3-5 business days."
                : "Order #{$order->order_number} has been cancelled successfully and items returned to stock.";

            return back()->with('success', $successMsg);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to cancel order: ' . $e->getMessage());
        }
    }

    public function invoice(Order $order)
    {
        if ($order->user_id !== Auth::id() && !session("guest_order_{$order->id}")) {
            abort(403);
        }
        
        $order->load(['vendor', 'items.product', 'user']);
        
        return view('orders.invoice', compact('order'));
    }
}
