<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderStatusLog;
use App\Models\Shipment;
use App\Notifications\OrderStatusUpdated;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ShiprocketWebhookController extends Controller
{
    /**
     * Handle incoming tracking webhook updates from Shiprocket.
     */
    public function handle(Request $request): JsonResponse
    {
        $configuredToken = (string) config('shipping.shiprocket.webhook_token', '');
        if ($configuredToken !== '') {
            $providedToken = (string) ($request->header('x-api-key') ?? $request->header('X-Api-Key') ?? '');
            if ($providedToken === '' || !hash_equals($configuredToken, $providedToken)) {
                return response()->json(['success' => false, 'message' => 'Unauthorized webhook request'], 403);
            }
        }

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

        if (!$shipment->order_id) {
            return response()->json(['message' => 'Associated order not found'], 200);
        }

        // Map Shiprocket courier statuses to K71 platform states
        $mappedShipmentStatus = match ($rawStatus) {
            'PICKED UP', 'IN TRANSIT', 'REACHED AT DESTINATION HUB', 'OUT FOR PICKUP' => 'in_transit',
            'OUT FOR DELIVERY' => 'out_for_delivery',
            'DELIVERED' => 'delivered',
            'RTO INITIATED', 'RTO DELIVERED', 'RTO ACKNOWLEDGED' => 'rto',
            'CANCELED', 'CANCELLED' => 'cancelled',
            default => 'in_transit',
        };

        $transitionResult = DB::transaction(function () use ($shipment, $mappedShipmentStatus, $rawStatus, $request) {
            $lockedShipment = Shipment::whereKey($shipment->id)->lockForUpdate()->firstOrFail();
            $lockedOrder = Order::whereKey($lockedShipment->order_id)->lockForUpdate()->with(['user', 'vendor'])->first();

            if (!$lockedOrder) {
                return null;
            }

            $lockedShipment->update([
                'status' => $mappedShipmentStatus,
                'metadata' => array_merge($lockedShipment->metadata ?? [], [
                    'last_webhook_status' => $rawStatus,
                    'last_webhook_at' => now()->toIso8601String(),
                ]),
            ]);

            $oldOrderStatus = $lockedOrder->status;
            $newOrderStatus = null;

            // Never resurrect cancelled orders on late courier webhook scans
            if ($oldOrderStatus !== 'cancelled') {
                if ($mappedShipmentStatus === 'delivered' && $oldOrderStatus !== 'delivered') {
                    $newOrderStatus = 'delivered';
                    $lockedOrder->update([
                        'status' => 'delivered',
                        'delivered_at' => now(),
                    ]);
                } elseif (in_array($mappedShipmentStatus, ['in_transit', 'out_for_delivery'], true) && !in_array($oldOrderStatus, ['shipped', 'delivered'], true)) {
                    $newOrderStatus = 'shipped';
                    $lockedOrder->update([
                        'status' => 'shipped',
                        'shipped_at' => $lockedOrder->shipped_at ?? now(),
                    ]);
                }
            }

            OrderStatusLog::create([
                'order_id' => $lockedOrder->id,
                'from_status' => $oldOrderStatus,
                'to_status' => $newOrderStatus ?? $oldOrderStatus,
                'actor_type' => 'system',
                'actor_id' => null,
                'note' => "Shiprocket Live Scan: {$rawStatus} (AWB: {$lockedShipment->awb_code})",
                'ip_address' => $request->ip(),
            ]);

            return [
                'order' => $lockedOrder->fresh(['user', 'vendor']),
                'new_status' => $newOrderStatus,
                'awb' => $lockedShipment->awb_code,
            ];
        });

        if (!$transitionResult) {
            return response()->json(['message' => 'Associated order not found'], 200);
        }

        $order = $transitionResult['order'];
        $newOrderStatus = $transitionResult['new_status'];

        if (in_array($newOrderStatus, ['shipped', 'delivered'], true)) {
            try {
                $order->user?->notify(new OrderStatusUpdated($order));
            } catch (\Throwable $e) {}
        }

        return response()->json([
            'success' => true,
            'order_id' => $order->order_number,
            'awb' => $transitionResult['awb'],
            'status' => $mappedShipmentStatus,
        ]);
    }
}
