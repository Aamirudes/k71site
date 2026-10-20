<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderStatusLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SettlementService
{
    /**
     * Calculate the expected settlement time according to Indian Banking / PhonePe T+1 schedule.
     * Default: 11:00 AM on the next banking business day.
     * (Excludes Sundays, 2nd & 4th Saturdays, and major national holidays)
     */
    public function calculateExpectedSettlementTime(?Carbon $from = null): Carbon
    {
        $date = ($from ? $from->copy() : now())->setTimezone('Asia/Kolkata');
        
        // Start checking from the next calendar day
        $date->addDay()->setTime(11, 0, 0);

        while (!$this->isIndianBankingDay($date)) {
            $date->addDay();
        }

        return $date;
    }

    /**
     * Determine if a given date is a working day for Indian banks.
     */
    public function isIndianBankingDay(Carbon $date): bool
    {
        // Sundays are non-banking days
        if ($date->isSunday()) {
            return false;
        }

        // Saturdays: 2nd and 4th Saturdays of the month are bank holidays in India
        if ($date->isSaturday()) {
            $dayOfMonth = $date->day;
            $saturdayNumber = (int) ceil($dayOfMonth / 7);
            if ($saturdayNumber === 2 || $saturdayNumber === 4) {
                return false;
            }
        }

        // Standard Indian National Bank Holidays (Month-Day format)
        $nationalHolidays = [
            '01-26', // Republic Day
            '08-15', // Independence Day
            '10-02', // Gandhi Jayanti
            '05-01', // May Day
            '12-25', // Christmas
        ];

        if (in_array($date->format('m-d'), $nationalHolidays, true)) {
            return false;
        }

        return true;
    }

    /**
     * Mark an order's payment as settled into the master Current Account.
     * Unlocks the order for the vendor to accept and receive instant payout.
     */
    public function markSettled(Order $order, ?string $batchRef = null, ?string $actor = 'system'): bool
    {
        if ($order->settlement_status === 'cleared') {
            return true;
        }

        $batch = $batchRef ?: ('SETTLE_BATCH_' . date('Ymd') . '_' . strtoupper(substr(md5($order->id . time()), 0, 8)));

        $order->update([
            'settlement_status' => 'cleared',
            'settled_at' => now(),
            'settlement_batch_ref' => $batch,
            'vendor_payout_status' => $order->vendor_payout_status === 'paid' ? 'paid' : 'ready',
        ]);

        OrderStatusLog::create([
            'order_id' => $order->id,
            'from_status' => $order->status,
            'to_status' => $order->status,
            'actor_type' => $actor,
            'note' => "PhonePe Payment Settlement Cleared & Credited to K71 Current Account (Batch Ref: {$batch}). Order unlocked for vendor acceptance & instant payout.",
            'ip_address' => request()->ip(),
        ]);

        Log::info("Order #{$order->order_number} settlement marked as cleared. Batch: {$batch}");

        return true;
    }

    /**
     * Check if the order's customer payment has settled into the platform account.
     */
    public function isSettled(Order $order): bool
    {
        return $order->settlement_status === 'cleared';
    }

    /**
     * Automatically scan and clear all orders whose settlement window has arrived.
     * Runs just-in-time on vendor and admin page loads and background jobs.
     */
    public function autoClearDueOrders(): int
    {
        $dueOrders = Order::where('payment_status', 'paid')
            ->where('settlement_status', 'pending')
            ->where(function ($q) {
                $q->whereNull('settlement_expected_at')
                  ->orWhere('settlement_expected_at', '<=', now());
            })
            ->get();

        $clearedCount = 0;
        foreach ($dueOrders as $order) {
            $this->markSettled($order, null, 'automated_system');
            $clearedCount++;
        }

        return $clearedCount;
    }
}
