<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderStatusLog;
use App\Notifications\OrderStatusUpdated;
use App\Services\PhonePeService;
use App\Services\SettlementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PhonePeWebhookController extends Controller
{
    /**
     * Handle incoming asynchronous webhook callbacks from PhonePe.
     * Supports both real-time Payment Success and Settlement Clearance notifications.
     */
    public function handle(Request $request, SettlementService $settlementService, PhonePeService $phonePeService): JsonResponse
    {
        Log::info('PhonePe Webhook Received', [
            'headers' => $request->headers->all(),
            'body' => $request->all(),
        ]);

        // Security: Verify webhook signature before processing
        if (!$phonePeService->verifyWebhookSignature($request)) {
            Log::warning('PhonePe Webhook: Signature verification failed.', [
                'ip' => $request->ip(),
                'x_verify' => $request->header('X-VERIFY'),
            ]);
            return response()->json(['success' => false, 'message' => 'Invalid signature'], 403);
        }

        $payload = $request->input('response');

        // Decode base64 payload if present
        $data = [];
        if (!empty($payload)) {
            $decoded = base64_decode($payload);
            if ($decoded) {
                $data = json_decode($decoded, true) ?: [];
            }
        } else {
            $data = $request->all();
        }

        if (empty($data)) {
            Log::warning('PhonePe Webhook: Empty or unreadable payload.');
            return response()->json(['success' => false, 'message' => 'Empty payload'], 400);
        }

        $merchantTransactionId = $data['payload']['merchantOrderId']
            ?? $data['payload']['merchantTransactionId']
            ?? $data['data']['merchantTransactionId']
            ?? $data['data']['merchantOrderId']
            ?? $data['merchantTransactionId']
            ?? $data['merchantOrderId']
            ?? null;

        $code = $data['code'] ?? '';
        $event = $data['event'] ?? $data['type'] ?? '';

        Log::info('PhonePe Webhook Parsed Event', [
            'merchantTransactionId' => $merchantTransactionId,
            'code' => $code,
            'event' => $event,
        ]);

        if (empty($merchantTransactionId)) {
            return response()->json(['success' => true, 'message' => 'Acknowledged, no transaction ID']);
        }

        // Find the corresponding order
        $order = Order::where('order_number', $merchantTransactionId)
            ->orWhere('id', (int) str_replace('ORD-', '', $merchantTransactionId))
            ->orWhere('tracking_number', $merchantTransactionId)
            ->first();

        if (!$order) {
            // Also search by paymentClaim
            $order = Order::whereHas('paymentClaim', function ($q) use ($merchantTransactionId) {
                $q->where('utr_number', $merchantTransactionId);
            })->first();
        }

        if (!$order) {
            Log::warning("PhonePe Webhook: Order not found for Transaction ID: {$merchantTransactionId}");
            return response()->json(['success' => true, 'message' => 'Order not found, acknowledged']);
        }

        // 1. Payment Success Event (atomic lock + commission + settlement schedule + notification)
        $isPaymentSuccess = $code === 'PAYMENT_SUCCESS' 
            || $event === 'PAYMENT_SUCCESS' 
            || str_contains($event, 'order.completed') 
            || str_contains($event, 'checkout.order.completed')
            || str_contains($event, 'pg.order.completed');

        $newlyPaidOrder = null;

        if ($isPaymentSuccess) {
            DB::transaction(function () use ($order, $settlementService, $merchantTransactionId, $event, $request, &$newlyPaidOrder) {
                $lockedOrder = Order::whereKey($order->id)->lockForUpdate()->with(['user', 'vendor'])->firstOrFail();

                if ($lockedOrder->payment_status === 'paid' || $lockedOrder->payment_status === 'refund_pending') {
                    return;
                }

                if ($lockedOrder->status === 'cancelled') {
                    $lockedOrder->update([
                        'payment_status' => 'refund_pending',
                        'payment_method' => 'phonepe',
                    ]);
                    OrderStatusLog::create([
                        'order_id' => $lockedOrder->id,
                        'from_status' => 'cancelled',
                        'to_status' => 'cancelled',
                        'actor_type' => 'webhook_phonepe',
                        'note' => "PhonePe S2S Webhook ({$event}): Late payment after cancellation flagged for refund.",
                        'ip_address' => $request->ip(),
                    ]);
                    return;
                }

                $commissionService = app(\App\Services\Monetization\CommissionService::class);
                $commissionRecord = $commissionService->recordOrderCommission($lockedOrder);
                $vendorEarnings = (int) $commissionRecord->vendor_net_paise;
                $expectedSettlementAt = $settlementService->calculateExpectedSettlementTime();
                $previousStatus = $lockedOrder->status;

                $lockedOrder->update([
                    'status' => $previousStatus === 'pending' ? 'processing' : $previousStatus,
                    'payment_status' => 'paid',
                    'payment_method' => 'phonepe',
                    'settlement_status' => $lockedOrder->settlement_status === 'cleared' ? 'cleared' : 'pending',
                    'settlement_expected_at' => $lockedOrder->settlement_expected_at ?? $expectedSettlementAt,
                    'vendor_payout_status' => $lockedOrder->vendor_payout_status === 'paid' ? 'paid' : 'unsettled',
                    'vendor_payout_amount' => $vendorEarnings,
                ]);

                $lockedOrder->paymentClaim()->updateOrCreate(
                    ['order_id' => $lockedOrder->id],
                    [
                        'user_id' => $lockedOrder->user_id,
                        'utr_number' => $merchantTransactionId,
                        'amount_claimed' => $lockedOrder->total_amount,
                        'status' => 'verified',
                        'verified_at' => now(),
                    ]
                );

                if ($lockedOrder->user_id) {
                    $cart = Cart::where('user_id', $lockedOrder->user_id)->first();
                    if ($cart) {
                        $cart->items()->where('vendor_id', $lockedOrder->vendor_id)->delete();
                    }
                }

                OrderStatusLog::create([
                    'order_id' => $lockedOrder->id,
                    'from_status' => $previousStatus,
                    'to_status' => $lockedOrder->status,
                    'actor_type' => 'webhook_phonepe',
                    'note' => "PhonePe S2S Webhook ({$event}): Payment Verified & Commission Recorded",
                    'ip_address' => $request->ip(),
                ]);

                $newlyPaidOrder = $lockedOrder->fresh(['user', 'vendor']);
            });

            if ($newlyPaidOrder) {
                try {
                    $newlyPaidOrder->user?->notify(new OrderStatusUpdated($newlyPaidOrder));
                    app(\App\Services\NotificationCenterService::class)->notifyUser(
                        $newlyPaidOrder->vendor?->user_id,
                        '💳',
                        "Payment Confirmed: #{$newlyPaidOrder->order_number}",
                        'PhonePe S2S payment verified for ₹' . number_format($newlyPaidOrder->total_amount / 100, 2) . '.',
                        route('vendor.orders.show', $newlyPaidOrder->id),
                        'payment_verified',
                        'phonepe_paid_' . $newlyPaidOrder->id
                    );
                } catch (\Throwable $e) {}
            }
        }

        // 2. Settlement Clearance Event
        $isSettlementEvent = str_contains(strtolower($event), 'settle') 
            || str_contains(strtolower($code), 'settle');

        if ($isSettlementEvent) {
            $batchRef = $data['payload']['settlementId'] 
                ?? $data['data']['settlementId'] 
                ?? $data['data']['utr'] 
                ?? ('PHONEPE_WH_' . date('Ymd'));

            $freshOrder = $order->fresh();
            if ($freshOrder && $freshOrder->payment_status === 'paid' && $freshOrder->status !== 'cancelled') {
                $settlementService->markSettled($freshOrder, $batchRef, 'webhook_phonepe_settlement');
                Log::info("PhonePe Webhook: Order #{$freshOrder->order_number} settlement marked as CLEARED via webhook. Batch: {$batchRef}");
            }
        }

        $order->refresh();

        return response()->json([
            'success' => true,
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'status' => $order->status,
            'settlement_status' => $order->settlement_status,
        ]);
    }
}
