<?php

namespace App\Console\Commands;

use App\Services\StockManagerService;
use Illuminate\Console\Command;

class ReleaseUnpaidStockCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:release-unpaid-stock {--timeout=45 : Timeout in minutes for unpaid orders}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically cancel abandoned/timed-out online payment orders and restore stock back to inventory';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $timeout = (int) $this->option('timeout');
        $this->info("Scanning for abandoned pending orders older than {$timeout} minutes...");

        $released = StockManagerService::releaseExpiredOrders($timeout);

        if ($released > 0) {
            $this->info("Successfully cancelled {$released} expired order(s) and restored stock to inventory.");
        } else {
            $this->info("No expired pending orders found. All inventory is current.");
        }

        return Command::SUCCESS;
    }
}
