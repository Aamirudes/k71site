<?php

namespace App\Console\Commands;

use App\Services\Admin\MarketplaceDiagnosticsService;
use Illuminate\Console\Command;

class ReconcileMarketplaceCommand extends Command
{
    protected $signature = 'k71:reconcile
        {--fix-deterministic : Automatically repair safe deterministic counter and expired-boost drift}
        {--json : Output full diagnostics report as JSON}';

    protected $description = 'Inspect marketplace consistency across subscriptions, listings, enquiries, shorts boosts, and scheduled jobs.';

    public function handle(MarketplaceDiagnosticsService $diagnostics): int
    {
        $fix = (bool) $this->option('fix-deterministic');
        $report = $diagnostics->inspect($fix);

        if ($this->option('json')) {
            $this->line(json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            return self::SUCCESS;
        }

        $this->info('K71 Marketplace Reconciliation & Diagnostics');
        $this->table(
            ['Check', 'Count'],
            [
                ['Overdue Active Subscriptions', $report['checks']['overdue_active_subscriptions']],
                ['Overdue Grace Subscriptions', $report['checks']['overdue_grace_subscriptions']],
                ['Mismatched Enquiry Quote Counts', $report['checks']['mismatched_enquiry_quotes_count']],
                ['Overdue Active Short Boosts', $report['checks']['overdue_active_short_boosts']],
                ['Orphaned Active Vendor Products', $report['checks']['orphaned_active_vendor_products']],
                ['Failed Scheduled Jobs (7d)', $report['checks']['failed_scheduled_jobs_7d']],
            ]
        );

        if ($fix) {
            $this->info(sprintf(
                'Deterministic repairs applied: %d enquiry count(s), %d boost expiry(ies).',
                $report['fixes_applied']['enquiry_quotes_synced'],
                $report['fixes_applied']['short_boosts_expired']
            ));
        }

        return self::SUCCESS;
    }
}

