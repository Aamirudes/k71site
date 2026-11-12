<?php

namespace App\Console\Commands;

use App\Models\SubscriptionPlan;
use App\Models\Vendor;
use App\Models\VendorSubscription;
use App\Services\Monetization\SectorRegistryService;
use App\Services\Monetization\SubscriptionLifecycleService;
use Illuminate\Console\Command;

class GrantTemporaryBridgeAccessCommand extends Command
{
    protected $signature = 'subscriptions:grant-temp-bridge
                            {--vendor=* : Vendor IDs to grant temporary access}
                            {--days=3 : Number of days for temporary bridge access}
                            {--admin-id=1 : Admin user ID authorizing this grant}';

    protected $description = 'Admin-authorized temporary full-access bridge subscription for existing legacy vendors without fabricating payments.';

    public function handle(SubscriptionLifecycleService $lifecycle, SectorRegistryService $sectors): int
    {
        $vendorIds = array_values(array_unique(array_filter(array_map('intval', (array) $this->option('vendor')))));
        if (empty($vendorIds)) {
            $this->warn('No vendor IDs provided via --vendor.');
            return self::INVALID;
        }

        $days = max(1, (int) $this->option('days'));
        $adminId = (int) $this->option('admin-id') ?: null;

        // Build full-access entitlement matrix matching pre-monetization capabilities
        $allCatalog = $sectors->getEntitlementsForSector('all');
        $baseEntitlements = [];
        foreach ($allCatalog as $key => $meta) {
            if ($meta['type'] === 'bool') {
                $baseEntitlements[$key] = $key === 'allow_kinetic_scroll' ? false : true;
            } elseif ($meta['type'] === 'nullable_int') {
                $baseEntitlements[$key] = null; // Unlimited
            } else {
                $baseEntitlements[$key] = max((int) ($meta['default'] ?? 10), 20);
            }
        }

        // Reuse or create internal-only temporary bridge plan (never displayed publicly as checkout)
        $plan = SubscriptionPlan::query()->where('slug', 'temp-legacy-full-access-bridge')->first();
        if (!$plan) {
            $plan = $lifecycle->createOrUpdatePlan([
                'code' => 'LEGACY_BRIDGE_TEMP',
                'name' => 'Temporary Full-Access Bridge (3 Days)',
                'slug' => 'temp-legacy-full-access-bridge',
                'description' => 'Internal admin-only temporary bridge granting existing merchants uninterrupted multi-sector access while WhatsApp plan onboarding is completed.',
                'sector_key' => 'all',
                'applicable_sectors' => ['all'],
                'is_cross_sector' => true,
                'is_free' => true,
                'price_amount' => 0,
                'duration_days' => $days,
                'custom_duration_allowed' => true,
                'status' => 'active',
                'is_public_display' => false,
                'entitlements' => $baseEntitlements,
                'admin_notes' => 'System-created internal bridge plan with zero payment fabrication.',
            ], null, $adminId, 'Initial temporary legacy bridge plan created by admin command');
        }

        $processed = 0;
        foreach ($vendorIds as $vendorId) {
            $vendor = Vendor::find($vendorId);
            if (!$vendor) {
                $this->warn("Vendor #{$vendorId} not found — skipped.");
                continue;
            }

            // Skip idempotently if vendor already has an active/grace subscription in 'all' or primary sector
            $alreadyActive = VendorSubscription::query()
                ->where('vendor_id', $vendor->id)
                ->whereIn('status', [VendorSubscription::STATUS_ACTIVE, VendorSubscription::STATUS_GRACE_PERIOD])
                ->exists();

            if ($alreadyActive) {
                $this->info("Vendor #{$vendor->id} ({$vendor->shop_name}) already has an active/grace subscription — skipped.");
                continue;
            }

            if (!$vendor->isVerifiedMerchant()) {
                $vendor = $lifecycle->updateVerificationStatus(
                    $vendor,
                    'verified',
                    $adminId,
                    'Verified as existing pre-monetization vendor for temporary bridge access'
                );
            }

            $kineticAllowed = (bool) ($vendor->can_use_kinetic_scroll ?? false);

            $assigned = $lifecycle->assignSubscription($vendor, $plan, 'all', [
                'duration_days' => $days,
                'entitlements_override' => ['allow_kinetic_scroll' => $kineticAllowed],
                'internal_notes' => "Admin temporary {$days}-day full-access bridge assigned so vendor retains uninterrupted prior access until manual WhatsApp plan finalization.",
                'admin_reason' => "Temporary {$days}-day full-access bridge authorized by admin",
            ], $adminId);

            $activated = $lifecycle->activateSubscription($assigned, [
                'duration_days' => $days,
                'starts_at' => now(),
                'ends_at' => now()->addDays($days),
                'replace_existing' => false,
                'payment_method' => 'manual_admin_grant',
                'admin_reason' => "Explicitly activated {$days}-day temporary full-access bridge (no external payment fabricated)",
            ], $adminId);

            $processed++;
            $this->info("Granted {$days}-day full-access bridge (Sub #{$activated->id}, ends {$activated->ends_at?->toDateTimeString()}) to Vendor #{$vendor->id} ({$vendor->shop_name}).");
        }

        $this->info("Completed. Activated temporary access for {$processed} vendor(s).");
        return self::SUCCESS;
    }
}
