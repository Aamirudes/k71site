<?php

namespace Tests;

use App\Models\SubscriptionPlan;
use App\Models\Vendor;
use App\Models\VendorSubscription;
use App\Services\Monetization\SectorRegistryService;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Activate a verified merchant subscription for test fixtures so strict monetization rules
     * are satisfied without weakening production enforcement.
     */
    protected function grantVendorTestSubscription(
        Vendor $vendor,
        string $sectorKey = 'all',
        array $entitlementOverrides = [],
        array $subscriptionOverrides = []
    ): VendorSubscription {
        if ($vendor->verification_status !== 'verified' || $vendor->status !== 'approved') {
            $vendor->forceFill([
                'status' => 'approved',
                'verification_status' => 'verified',
                'verified_at' => $vendor->verified_at ?? now(),
                'approved_at' => $vendor->approved_at ?? now(),
            ])->saveQuietly();
        }
        if (auth()->check() && (int) auth()->id() === (int) $vendor->user_id && method_exists(auth()->user(), 'unsetRelation')) {
            auth()->user()->unsetRelation('vendor');
        }

        $catalog = app(SectorRegistryService::class)->getEntitlementsForSector($sectorKey);
        $defaults = [];
        foreach ($catalog as $key => $meta) {
            if ($meta['type'] === 'bool') {
                $defaults[$key] = $key === 'allow_kinetic_scroll' ? false : true;
            } else {
                $defaults[$key] = null; // Unlimited for baseline functional tests unless explicitly capped
            }
        }
        $entitlements = array_merge($defaults, $entitlementOverrides);

        $plan = SubscriptionPlan::firstOrCreate(
            ['slug' => 'test-' . $sectorKey . '-plan'],
            [
                'code' => 'TEST_' . strtoupper($sectorKey),
                'name' => 'Test ' . ucfirst($sectorKey) . ' Plan',
                'sector_key' => $sectorKey,
                'applicable_sectors' => $sectorKey === 'all' ? ['all'] : [$sectorKey],
                'is_cross_sector' => $sectorKey === 'all',
                'is_free' => false,
                'price_amount' => 99900,
                'price_monthly' => 99900,
                'currency' => 'INR',
                'duration_days' => 365,
                'status' => 'active',
                'is_active' => true,
                'features' => $entitlements,
                'current_version' => 1,
            ]
        );

        return VendorSubscription::create(array_merge([
            'vendor_id' => $vendor->id,
            'plan_id' => $plan->id,
            'sector_key' => $sectorKey,
            'applicable_sectors_snapshot' => $sectorKey === 'all' ? ['all'] : [$sectorKey],
            'plan_version_number' => 1,
            'plan_name_snapshot' => $plan->name,
            'price_amount_snapshot' => $plan->price_amount,
            'is_free_snapshot' => false,
            'currency' => 'INR',
            'duration_days' => 365,
            'entitlements_snapshot' => $entitlements,
            'expiry_policy_snapshot' => [
                'hide_storefront' => true,
                'hide_listings' => true,
                'block_new_listings' => true,
                'block_editing' => true,
                'grace_days' => 3,
                'grace_keep_visible' => true,
                'grace_keep_features' => false,
            ],
            'status' => VendorSubscription::STATUS_ACTIVE,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDays(364),
            'grace_keep_visible' => true,
            'grace_keep_features' => false,
            'auto_reminder_enabled' => true,
            'external_payment_received' => true,
            'external_payment_date' => now()->subDay(),
            'external_payment_amount' => $plan->price_amount,
            'external_payment_method' => 'test_fixture',
            'activated_at' => now()->subDay(),
        ], $subscriptionOverrides));
    }
}
