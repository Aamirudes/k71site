<?php

namespace App\Console\Commands;

use App\Services\Monetization\SubscriptionLifecycleService;
use Illuminate\Console\Command;

class ProcessSubscriptionExpiriesCommand extends Command
{
    protected $signature = 'subscriptions:process-expiry {--trigger=scheduler : Trigger source (scheduler or manual)}';
    protected $description = 'Process overdue vendor subscriptions, grace periods, visibility expiry, and deduplicated expiry reminders.';

    public function handle(SubscriptionLifecycleService $lifecycle): int
    {
        $trigger = (string) $this->option('trigger');
        $this->info("Running K71 subscription expiry & reminder processor ({$trigger})...");

        $run = $lifecycle->runScheduledExpirySweep($trigger);

        $this->info("Completed: {$run->summary}");
        return self::SUCCESS;
    }
}
