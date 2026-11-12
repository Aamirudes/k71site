<?php

namespace App\Services\Admin;

use App\Models\AuditLog;
use App\Models\Enquiry;
use App\Models\Product;
use App\Models\ScheduledJobRun;
use App\Models\Short;
use App\Models\ShortBoost;
use App\Models\Vendor;
use App\Models\VendorSubscription;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class MarketplaceDiagnosticsService
{
    /**
     * Audit marketplace integrity across subscriptions, listings, enquiries, shorts, and scheduled jobs.
     *
     * @return array<string, mixed>
     */
    public function inspect(bool $fixDeterministic = false, ?int $actorId = null): array
    {
        $now = now();
        $fixesApplied = [
            'enquiry_quotes_synced' => 0,
            'short_boosts_expired' => 0,
        ];

        // 1. Subscription status/date drift (overdue active or grace subscriptions awaiting cron sweep)
        $overdueActiveSubs = VendorSubscription::where('status', VendorSubscription::STATUS_ACTIVE)
            ->whereNotNull('ends_at')
            ->where('ends_at', '<', $now)
            ->count();

        $overdueGraceSubs = VendorSubscription::where('status', VendorSubscription::STATUS_GRACE_PERIOD)
            ->whereNotNull('grace_ends_at')
            ->where('grace_ends_at', '<', $now)
            ->count();

        // 2. Enquiries with mismatched quotes_count
        $enquiryDrifts = DB::table('enquiries as e')
            ->leftJoin(DB::raw('(SELECT enquiry_id, COUNT(*) as actual_count FROM quotes GROUP BY enquiry_id) as q'), 'e.id', '=', 'q.enquiry_id')
            ->whereRaw('COALESCE(e.quotes_count, 0) != COALESCE(q.actual_count, 0)')
            ->get(['e.id', 'e.enquiry_number', DB::raw('COALESCE(e.quotes_count, 0) as stored_count'), DB::raw('COALESCE(q.actual_count, 0) as actual_count')]);

        if ($fixDeterministic && $enquiryDrifts->isNotEmpty()) {
            foreach ($enquiryDrifts as $drift) {
                Enquiry::whereKey($drift->id)->update(['quotes_count' => (int) $drift->actual_count]);
                $fixesApplied['enquiry_quotes_synced']++;
            }
            $enquiryDrifts = collect();
        }

        // 3. Overdue Short Boosts (backed by short_boosts.ends_at + stale Short.is_boosted flags)
        $overdueBoosts = ShortBoost::where('status', 'active')
            ->whereNotNull('ends_at')
            ->where('ends_at', '<', $now)
            ->get();

        $staleBoostedShortIds = Short::where('is_boosted', true)
            ->whereHas('boosts')
            ->whereDoesntHave('boosts', function ($bq) use ($now) {
                $bq->where('status', 'active')
                    ->where(fn ($dt) => $dt->whereNull('ends_at')->orWhere('ends_at', '>', $now));
            })
            ->pluck('id');

        if ($fixDeterministic && ($overdueBoosts->isNotEmpty() || $staleBoostedShortIds->isNotEmpty())) {
            $touchedShortIds = collect();
            foreach ($overdueBoosts as $boost) {
                $boost->update(['status' => 'expired']);
                $touchedShortIds->push($boost->short_id);
                $fixesApplied['short_boosts_expired']++;
            }

            $allCandidateIds = $touchedShortIds->merge($staleBoostedShortIds)->unique()->values();
            foreach ($allCandidateIds as $shortId) {
                $hasActiveBoost = ShortBoost::where('short_id', $shortId)
                    ->where('status', 'active')
                    ->where(fn ($dt) => $dt->whereNull('ends_at')->orWhere('ends_at', '>', $now))
                    ->exists();

                if (!$hasActiveBoost) {
                    $updated = Short::whereKey($shortId)
                        ->where('is_boosted', true)
                        ->update([
                            'is_boosted' => false,
                            'boost_type' => null,
                        ]);
                    if ($updated > 0 && !$touchedShortIds->contains($shortId)) {
                        $fixesApplied['short_boosts_expired'] += $updated;
                    }
                }
            }

            $overdueBoosts = collect();
            $staleBoostedShortIds = collect();
        }
        $overdueBoostFlags = $staleBoostedShortIds->count();

        // 4. Orphaned or inconsistent active listings (pointing to missing/inactive/rejected vendors)
        $orphanedVendorProducts = Product::withoutGlobalScopes()
            ->where('status', 'active')
            ->whereNotNull('vendor_id')
            ->whereDoesntHave('vendor', fn ($vq) => $vq->whereIn('status', ['approved', 'active'])->whereNull('deleted_at'))
            ->count();

        // 5. Recent Scheduled Job Runs
        $recentJobRuns = Schema::hasTable('scheduled_job_runs')
            ? ScheduledJobRun::latest('id')->take(10)->get()
            : collect();

        $failedJobsLast7d = Schema::hasTable('scheduled_job_runs')
            ? ScheduledJobRun::where('status', 'failed')->where('created_at', '>=', now()->subDays(7))->count()
            : 0;

        // 6. System & Storage checks
        $dbHealthy = false;
        try {
            DB::select('SELECT 1');
            $dbHealthy = true;
        } catch (\Throwable) {
            $dbHealthy = false;
        }

        $storageWritable = is_writable(storage_path('app/public'));

        if ($fixDeterministic && ($fixesApplied['enquiry_quotes_synced'] > 0 || $fixesApplied['short_boosts_expired'] > 0)) {
            if (Schema::hasTable('scheduled_job_runs')) {
                ScheduledJobRun::create([
                    'job_name' => 'marketplace_reconciliation',
                    'status' => 'completed',
                    'processed_count' => $fixesApplied['enquiry_quotes_synced'] + $fixesApplied['short_boosts_expired'],
                    'expired_count' => $fixesApplied['short_boosts_expired'],
                    'summary' => sprintf(
                        'Synced %d enquiry quote count(s); expired %d overdue short boost(s) (%s).',
                        $fixesApplied['enquiry_quotes_synced'],
                        $fixesApplied['short_boosts_expired'],
                        $actorId ? 'admin_manual' : 'cli'
                    ),
                    'started_at' => $now,
                    'finished_at' => now(),
                ]);
            }

            AuditLog::create([
                'user_id' => $actorId,
                'auditable_type' => Vendor::class,
                'auditable_id' => 0,
                'action' => 'marketplace_reconcile_deterministic',
                'new_values' => $fixesApplied,
                'ip_address' => request()?->ip(),
            ]);
        }

        $issuesCount = $overdueActiveSubs
            + $overdueGraceSubs
            + $enquiryDrifts->count()
            + $overdueBoosts->count()
            + $overdueBoostFlags
            + $orphanedVendorProducts
            + $failedJobsLast7d;

        return [
            'checked_at' => $now->toIso8601String(),
            'healthy' => $dbHealthy && $storageWritable && $issuesCount === 0,
            'environment' => [
                'php_version' => PHP_VERSION,
                'laravel_version' => app()->version(),
                'app_env' => config('app.env'),
                'db_connection' => config('database.default'),
                'db_healthy' => $dbHealthy,
                'storage_writable' => $storageWritable,
                'cache_driver' => config('cache.default'),
                'queue_driver' => config('queue.default'),
            ],
            'checks' => [
                'overdue_active_subscriptions' => $overdueActiveSubs,
                'overdue_grace_subscriptions' => $overdueGraceSubs,
                'mismatched_enquiry_quotes_count' => $enquiryDrifts->count(),
                'overdue_active_short_boosts' => $overdueBoosts->count() + $overdueBoostFlags,
                'orphaned_active_vendor_products' => $orphanedVendorProducts,
                'failed_scheduled_jobs_7d' => $failedJobsLast7d,
            ],
            'issues_count' => $issuesCount,
            'fixes_applied' => $fixesApplied,
            'recent_job_runs' => $recentJobRuns,
        ];
    }
}
