<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderStatusLog;
use App\Notifications\OrderStatusUpdated;
use App\Services\PhonePeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    public function initiate(Order $order, PhonePeService $phonePeService)
    {
        if ($order->user_id !== Auth::id() && !session("guest_order_{$order->id}")) {
            abort(403);
        }

        if ($order->payment_status === 'paid') {
            return redirect()->route('orders.success', $order->id);
        }

        $callbackUrl = route('payment.phonepe.callback', ['order' => $order->id]);
        $response = $phonePeService->initiatePayment($order, $callbackUrl);

        if ($response['success'] && !empty($response['redirect_url'])) {
            return redirect()->away($response['redirect_url']);
        }

        // Graceful fallback to Direct UPI QR payment so the customer never gets stranded
        $order->update(['payment_method' => 'upi']);
        return redirect()->route('checkout.payment', $order->id)
            ->with('info', 'PhonePe Gateway is temporarily busy. Please scan the QR code below or enter UTR to complete your order!');
    }

    public function callback(Request $request, Order $order, PhonePeService $phonePeService)
    {
        // Restore user session if this was a cross-site POST callback without session cookie
        if (!Auth::check() && $order->user) {
            Auth::login($order->user, true);
        }

        // Idempotency: if already paid, avoid duplicate deduction/logging
        if ($order->payment_status === 'paid') {
            if ($request->header('referer') && Str::contains($request->header('referer'), '/vendor/orders')) {
                return redirect()->route('vendor.orders.show', $order)->with('success', 'Payment already verified!');
            }
            return redirect()->route('orders.success', $order->id)->with('success', 'Payment already verified!');
        }

        // Decode base64 payload from PhonePe if present (S2S webhook / redirect POST)
        $decodedPayload = null;
        if ($request->has('response')) {
            try {
                $decodedPayload = json_decode(base64_decode($request->input('response')), true);
            } catch (\Throwable $e) {
                Log::warning('PhonePe callback base64 decode failed', ['error' => $e->getMessage()]);
            }
        }

        $transactionId = $order->idempotency_key
            ?: ($decodedPayload['data']['merchantTransactionId'] ?? null)
            ?: ($request->input('transactionId') 
            ?: $request->input('merchantTransactionId'))
            ?: ($order->tracking_number && Str::startsWith($order->tracking_number, 'TXN_') ? $order->tracking_number : null);

        $code = $request->input('code') 
            ?? ($decodedPayload['code'] ?? null)
            ?? ($decodedPayload['data']['responseCode'] ?? null);

        Log::info('PhonePe Callback Received', [
            'order_id' => $order->id,
            'request_data' => $request->all(),
            'decoded_payload' => $decodedPayload,
            'transaction_id' => $transactionId,
            'code' => $code,
        ]);

        // Check verification with PhonePe API
        $check = $transactionId ? $phonePeService->checkStatus($transactionId) : ['success' => false];
        $isSuccess = ($check['success'] && ($check['status'] ?? null) === 'SUCCESS') 
            || ($code === 'PAYMENT_SUCCESS');

        if ($isSuccess) {
            DB::beginTransaction();
            try {
                // Calculate 5% marketplace commission STRICTLY on Product Subtotal (excluding shipping)
                $productSubtotal = max(0, $order->subtotal - $order->discount_amount);
                $platformCommission = (int) round($productSubtotal * 0.05);
                $vendorEarnings = $productSubtotal - $platformCommission;
                $platformShippingRetained = (int) ($order->shipping_amount ?? 0);

                // Keep tracking_number clean for courier AWB tracking
                $cleanTracking = Str::startsWith($order->tracking_number, 'TXN_') ? null : $order->tracking_number;

                // Calculate expected settlement time (T+1 Indian Banking day, ~11:00 AM)
                $settlementService = app(\App\Services\SettlementService::class);
                $expectedSettlementAt = $settlementService->calculateExpectedSettlementTime();

                $order->update([
                    'status' => 'processing',
                    'payment_status' => 'paid',
                    'payment_method' => 'phonepe',
                    'tracking_number' => $cleanTracking,
                    'settlement_status' => 'pending',
                    'settlement_expected_at' => $expectedSettlementAt,
                    'vendor_payout_status' => 'unsettled',
                    'vendor_payout_amount' => $vendorEarnings,
                ]);

                // Create verified payment claim record
                $order->paymentClaim()->updateOrCreate(
                    ['order_id' => $order->id],
                    [
                        'user_id' => $order->user_id,
                        'utr_number' => $check['data']['transactionId'] ?? $transactionId,
                        'amount_claimed' => $order->total_amount,
                        'status' => 'verified',
                        'verified_at' => now(),
                    ]
                );

                // Audit log with commission and shipping retention breakdown
                OrderStatusLog::create([
                    'order_id' => $order->id,
                    'from_status' => 'pending',
                    'to_status' => 'processing',
                    'actor_type' => 'system',
                    'note' => "PhonePe Auto-Verified. Product: ₹" . number_format($productSubtotal / 100, 2) . " | 5% Fee: ₹" . number_format($platformCommission / 100, 2) . " | Vendor Payout: ₹" . number_format($vendorEarnings / 100, 2) . " | Settlement Scheduled: " . $expectedSettlementAt->format('M d, Y h:i A') . " | Shipping Retained: ₹" . number_format($platformShippingRetained / 100, 2),
                    'ip_address' => $request->ip(),
                ]);

                // Clear customer's cart items for this vendor
                $cart = Cart::where('user_id', $order->user_id)->first();
                if ($cart) {
                    $cart->items()->where('vendor_id', $order->vendor_id)->delete();
                }

                DB::commit();

                // Send confirmation notification
                try {
                    $order->user->notify(new OrderStatusUpdated($order));
                } catch (\Throwable $e) {}

                if ($request->header('referer') && Str::contains($request->header('referer'), '/vendor/orders')) {
                    return redirect()->route('vendor.orders.show', $order)->with('success', 'Payment verified successfully!');
                }

                return redirect()->route('orders.success', $order->id)->with('success', 'Payment successful! Your order has been placed.');
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Order update failed after PhonePe success: ' . $e->getMessage());
                return redirect()->route('orders.index')->with('error', 'Payment confirmed, but an issue occurred updating the order record.');
            }
        }

        // If payment failed or was cancelled
        return redirect()->route('orders.index')->with('error', 'Payment was cancelled or could not be completed. You can retry paying anytime.');
    }
}
