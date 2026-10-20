<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Services\SettlementService;
use Illuminate\Console\Command;

class ProcessDailySettlementsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'settlements:process {--force : Force settlement of all pending orders regardless of expected time}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process clearing of settled customer funds into master Current Account based on PhonePe T+1 schedule';

    /**
     * Execute the console command.
     */
    public function handle(SettlementService $settlementService): int
    {
        $force = (bool) $this->option('force');
        $this->info("Scanning for eligible orders to settle...");

        $query = Order::where('payment_status', 'paid')
            ->where('settlement_status', 'pending');

        if (!$force) {
            $query->where(function ($q) {
                $q->whereNull('settlement_expected_at')
                  ->orWhere('settlement_expected_at', '<=', now());
            });
        }

        $orders = $query->get();

        if ($orders->isEmpty()) {
            $this->info("No pending settlements ready for clearing at this time.");
            return Command::SUCCESS;
        }

        $clearedCount = 0;
        foreach ($orders as $order) {
            $settlementService->markSettled($order, null, 'automated_settlement_cron');
            $clearedCount++;
            $this->line(" - Order #{$order->order_number}: Settled & cleared (Vendor payout ready).");
        }

        $this->info("Successfully cleared {$clearedCount} order settlement(s). Funds confirmed in Current Account.");
        return Command::SUCCESS;
    }
}
