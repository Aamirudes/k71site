<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderStatusLog;
use App\Models\Shipment;
use App\Notifications\OrderStatusUpdated;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ShiprocketWebhookController extends Controller
{
    /**
     * Handle incoming tracking webhook updates from Shiprocket.
     */
    public function handle(Request $request): JsonResponse
    {
        $payload = $request->all();
        Log::info('Shiprocket Webhook Received', ['payload' => $payload]);

        $awb = trim((string) ($request->input('awb') ?? $request->input('awb_code') ?? ''));
        $srOrderId = (string) ($request->input('order_id') ?? $request->input('sr_order_id') ?? '');
        $srShipmentId = (string) ($request->input('shipment_id') ?? '');
        $rawStatus = strtoupper(trim((string) ($request->input('current_status') ?? $request->input('shipment_status') ?? $request->input('status') ?? '')));

        if (empty($awb) && empty($srOrderId) && empty($srShipmentId)) {
            return response()->json(['message' => 'No identifier provided'], 400);
        }

        // Find matching shipment
        $shipmentQuery = Shipment::query();
        if ($awb) {
            $shipmentQuery->where('awb_code', $awb);
        } else {
            $shipmentQuery->where(function ($q) use ($srOrderId, $srShipmentId) {
                if ($srOrderId) $q->orWhere('shiprocket_order_id', $srOrderId);
                if ($srShipmentId) $q->orWhere('shiprocket_shipment_id', $srShipmentId);
            });
        }

        $shipment = $shipmentQuery->first();

        if (!$shipment) {
            Log::warning('Shiprocket Webhook: Shipment not found', ['awb' => $awb, 'sr_order_id' => $srOrderId]);
            return response()->json(['message' => 'Shipment not found'], 200);
        }

        $order = $shipment->order;
        if (!$order) {
            return response()->json(['message' => 'Associated order not found'], 200);
        }

        // Map Shiprocket courier statuses to Buyz platform states
        $mappedShipmentStatus = match ($rawStatus) {
            'PICKED UP', 'IN TRANSIT', 'REACHED AT DESTINATION HUB', 'OUT FOR PICKUP' => 'in_transit',
            'OUT FOR DELIVERY' => 'out_for_delivery',
            'DELIVERED' => 'delivered',
            'RTO INITIATED', 'RTO DELIVERED', 'RTO ACKNOWLEDGED' => 'rto',
            'CANCELED', 'CANCELLED' => 'cancelled',
            default => 'in_transit',
        };

        $shipment->update([
            'status' => $mappedShipmentStatus,
            'metadata' => array_merge($shipment->metadata ?? [], [
                'last_webhook_status' => $rawStatus,
                'last_webhook_at' => now()->toIso8601String(),
            ]),
        ]);

        $oldOrderStatus = $order->status;
        $newOrderStatus = null;

        if ($mappedShipmentStatus === 'delivered' && $oldOrderStatus !== 'delivered') {
            $newOrderStatus = 'delivered';
            $order->update([
                'status' => 'delivered',
                'delivered_at' => now(),
            ]);
        } elseif (in_array($mappedShipmentStatus, ['in_transit', 'out_for_delivery']) && !in_array($oldOrderStatus, ['shipped', 'delivered'])) {
            $newOrderStatus = 'shipped';
            $order->update([
                'status' => 'shipped',
                'shipped_at' => $order->shipped_at ?? now(),
            ]);
        }

        // Record status log
        OrderStatusLog::create([
            'order_id' => $order->id,
            'from_status' => $oldOrderStatus,
            'to_status' => $newOrderStatus ?? $oldOrderStatus,
            'actor_type' => 'system',
            'actor_id' => null,
            'note' => "Shiprocket Live Scan: {$rawStatus} (AWB: {$shipment->awb_code})",
            'ip_address' => $request->ip(),
        ]);

        // Send customer notification if order marked delivered
        if ($newOrderStatus === 'delivered') {
            try {
                $order->user->notify(new OrderStatusUpdated($order));
            } catch (\Throwable $e) {}
        }

        return response()->json([
            'success' => true,
            'order_id' => $order->order_number,
            'awb' => $shipment->awb_code,
            'status' => $mappedShipmentStatus,
        ]);
    }
}
