<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderStatusLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StockManagerService
{
    /**
     * Release stock for orders that were initiated via PhonePe/UPI but never completed/paid
     * and have timed out (default 45 minutes).
     */
    public static function releaseExpiredOrders(int $timeoutMinutes = 45): int
    {
        $cutoffTime = now()->subMinutes($timeoutMinutes);

        $expiredOrders = Order::with('items.variant', 'items.product')
            ->where('status', 'pending')
            ->where('payment_status', 'pending')
            ->whereIn('payment_method', ['phonepe', 'upi'])
            ->where('created_at', '<=', $cutoffTime)
            ->get();

        $releasedCount = 0;

        foreach ($expiredOrders as $order) {
            DB::beginTransaction();
            try {
                $order->update([
                    'status' => 'cancelled',
                    'cancelled_at' => now(),
                    'cancelled_by' => 'system',
                    'cancel_reason' => 'Payment session expired / abandoned (Stock released back to inventory)',
                ]);

                // Restore stock for both variants and products
                foreach ($order->items as $item) {
                    if ($item->variant) {
                        $item->variant->increment('stock', $item->quantity);
                    }
                    if ($item->product) {
                        $item->product->increment('total_stock', $item->quantity);
                    }
                }

                OrderStatusLog::create([
                    'order_id' => $order->id,
                    'from_status' => 'pending',
                    'to_status' => 'cancelled',
                    'actor_type' => 'system',
                    'note' => 'Payment session timed out after ' . $timeoutMinutes . ' minutes. Inventory restored.',
                ]);

                DB::commit();
                $releasedCount++;
            } catch (\Throwable $e) {
                DB::rollBack();
                Log::error("Failed to release expired order #{$order->id}: " . $e->getMessage());
            }
        }

        return $releasedCount;
    }

    /**
     * Restore stock when an order is cancelled.
     */
    public static function restoreOrderStock(Order $order): void
    {
        foreach ($order->items as $item) {
            if ($item->variant) {
                $item->variant->increment('stock', $item->quantity);
            }
            if ($item->product) {
                $item->product->increment('total_stock', $item->quantity);
            }
        }
    }
}
