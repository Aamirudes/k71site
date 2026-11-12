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
            ->with('info', 'Please scan the UPI QR code below or enter your UTR to complete payment!');
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

        // Verify transaction authenticity: require either PhonePe API confirmation OR a valid cryptographic X-VERIFY signature
        $check = $transactionId ? $phonePeService->checkStatus($transactionId) : ['success' => false];
        $hasValidSignature = $phonePeService->verifyWebhookSignature($request);
        $isSuccess = ($check['success'] && ($check['status'] ?? null) === 'SUCCESS')
            || ($hasValidSignature && $code === 'PAYMENT_SUCCESS');

        if ($isSuccess) {
            DB::beginTransaction();
            try {
                $lockedOrder = Order::whereKey($order->id)->lockForUpdate()->with(['user', 'vendor'])->firstOrFail();

                if ($lockedOrder->payment_status === 'paid' || $lockedOrder->payment_status === 'refund_pending') {
                    DB::commit();
                    return redirect()->route('orders.success', $lockedOrder->id)->with('success', 'Payment already verified!');
                }

                // Do not resurrect an already cancelled order into 'processing'; flag it for refund instead
                if ($lockedOrder->status === 'cancelled') {
                    $lockedOrder->update([
                        'payment_status' => 'refund_pending',
                        'payment_method' => 'phonepe',
                    ]);
                    $lockedOrder->paymentClaim()->updateOrCreate(
                        ['order_id' => $lockedOrder->id],
                        [
                            'user_id' => $lockedOrder->user_id,
                            'utr_number' => $check['data']['transactionId'] ?? $transactionId ?: ('TXN_CANC_' . $lockedOrder->id),
                            'amount_claimed' => $lockedOrder->total_amount,
                            'status' => 'verified',
                            'verified_at' => now(),
                        ]
                    );
                    OrderStatusLog::create([
                        'order_id' => $lockedOrder->id,
                        'from_status' => 'cancelled',
                        'to_status' => 'cancelled',
                        'actor_type' => 'system',
                        'note' => 'Late PhonePe confirmation received after order cancellation; flagged for customer refund.',
                        'ip_address' => $request->ip(),
                    ]);
                    DB::commit();
                    return redirect()->route('orders.show', $lockedOrder->id)
                        ->with('info', 'Payment received for your cancelled order and has been flagged for refund.');
                }

                // Calculate commission dynamically using CommissionService hierarchy & record idempotent ledger entry
                $commissionService = app(\App\Services\Monetization\CommissionService::class);
                $commissionRecord = $commissionService->recordOrderCommission($lockedOrder);
                $productSubtotal = (int) $commissionRecord->commission_base_paise;
                $platformCommission = (int) $commissionRecord->calculated_commission_paise;
                $vendorEarnings = (int) $commissionRecord->vendor_net_paise;
                $appliedRate = number_format((float) $commissionRecord->rate_percent, 2);
                $platformShippingRetained = (int) ($lockedOrder->shipping_amount ?? 0);

                // Keep tracking_number clean for courier AWB tracking
                $cleanTracking = Str::startsWith($lockedOrder->tracking_number, 'TXN_') ? null : $lockedOrder->tracking_number;

                // Calculate expected settlement time (T+1 Indian Banking day, ~11:00 AM)
                $settlementService = app(\App\Services\SettlementService::class);
                $expectedSettlementAt = $settlementService->calculateExpectedSettlementTime();
                $fromStatus = $lockedOrder->status;

                $lockedOrder->update([
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
                $lockedOrder->paymentClaim()->updateOrCreate(
                    ['order_id' => $lockedOrder->id],
                    [
                        'user_id' => $lockedOrder->user_id,
                        'utr_number' => $check['data']['transactionId'] ?? $transactionId,
                        'amount_claimed' => $lockedOrder->total_amount,
                        'status' => 'verified',
                        'verified_at' => now(),
                    ]
                );

                // Audit log with commission and shipping retention breakdown
                OrderStatusLog::create([
                    'order_id' => $lockedOrder->id,
                    'from_status' => $fromStatus,
                    'to_status' => 'processing',
                    'actor_type' => 'system',
                    'note' => "PhonePe Auto-Verified. Product: ₹" . number_format($productSubtotal / 100, 2) . " | Fee ({$appliedRate}% / {$commissionRecord->rule_source}): ₹" . number_format($platformCommission / 100, 2) . " | Vendor Payout: ₹" . number_format($vendorEarnings / 100, 2) . " | Settlement Scheduled: " . $expectedSettlementAt->format('M d, Y h:i A') . " | Shipping Retained: ₹" . number_format($platformShippingRetained / 100, 2),
                    'ip_address' => $request->ip(),
                ]);

                // Clear customer's cart items for this vendor
                if ($lockedOrder->user_id) {
                    $cart = Cart::where('user_id', $lockedOrder->user_id)->first();
                    if ($cart) {
                        $cart->items()->where('vendor_id', $lockedOrder->vendor_id)->delete();
                    }
                }

                DB::commit();
                $order = $lockedOrder->fresh(['user', 'vendor']);

                // Send confirmation notifications
                try {
                    $order->user?->notify(new OrderStatusUpdated($order));
                    app(\App\Services\NotificationCenterService::class)->notifyUser(
                        $order->vendor?->user_id,
                        '💳',
                        "Payment Confirmed: #{$order->order_number}",
                        'Online PhonePe payment verified for ₹' . number_format($order->total_amount / 100, 2) . '.',
                        route('vendor.orders.show', $order->id),
                        'payment_verified',
                        'phonepe_paid_' . $order->id
                    );
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
