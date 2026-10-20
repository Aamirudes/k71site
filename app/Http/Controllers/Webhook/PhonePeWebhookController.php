<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderStatusLog;
use App\Services\PhonePeService;
use App\Services\SettlementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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

        // 1. Payment Success Event
        $isPaymentSuccess = $code === 'PAYMENT_SUCCESS' 
            || $event === 'PAYMENT_SUCCESS' 
            || str_contains($event, 'order.completed') 
            || str_contains($event, 'checkout.order.completed')
            || str_contains($event, 'pg.order.completed');

        if ($isPaymentSuccess) {
            if ($order->payment_status !== 'paid') {
                $order->update([
                    'status' => 'processing',
                    'payment_status' => 'paid',
                ]);

                OrderStatusLog::create([
                    'order_id' => $order->id,
                    'from_status' => $order->status,
                    'to_status' => 'processing',
                    'actor_type' => 'webhook_phonepe',
                    'note' => "PhonePe S2S Webhook ({$event}): Payment Verified & Confirmed",
                    'ip_address' => $request->ip(),
                ]);
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

            $settlementService->markSettled($order, $batchRef, 'webhook_phonepe_settlement');

            Log::info("PhonePe Webhook: Order #{$order->order_number} settlement marked as CLEARED via webhook. Batch: {$batchRef}");
        }

        return response()->json([
            'success' => true,
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'status' => $order->status,
            'settlement_status' => $order->settlement_status,
        ]);
    }
}
