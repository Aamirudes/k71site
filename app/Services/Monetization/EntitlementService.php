<?php

namespace App\Services\Monetization;

use App\Models\Coupon;
use App\Models\MonetizationSetting;
use App\Models\PortfolioProject;
use App\Models\Product;
use App\Models\Short;
use App\Models\Vendor;
use App\Models\VendorSubscription;

class EntitlementService
{
    public function __construct(
        protected SectorRegistryService $sectors
    ) {}

    public function isStrictEnforcementEnabled(): bool
    {
        return (bool) MonetizationSetting::getValue('enforce_subscription_visibility', true);
    }

    /**
     * Resolve effective active/grace subscription for a vendor in a specific sector.
     * Enforces verification check AND request-time expiry synchronization.
     */
    public function resolveEffectiveSubscription(Vendor $vendor, string $sectorKey): ?VendorSubscription
    {
        $sectorKey = $this->sectors->normalizeSectorKey($sectorKey);

        // Hard account/verification blocks
        if (in_array($vendor->status, ['suspended', 'closed'], true)) {
            return null;
        }
        if (in_array($vendor->verification_status, ['rejected', 'suspended'], true)) {
            return null;
        }
        if (!$vendor->isVerifiedMerchant()) {
            return null;
        }

        // First perform lightweight just-in-time expiry transition if any active sub has crossed ends_at
        $this->syncJustInTimeExpiry($vendor);

        $candidates = VendorSubscription::query()
            ->where('vendor_id', $vendor->id)
            ->whereIn('status', [VendorSubscription::STATUS_ACTIVE, VendorSubscription::STATUS_GRACE_PERIOD])
            ->orderByRaw("CASE WHEN status = 'active' THEN 0 ELSE 1 END")
            ->orderByDesc('ends_at')
            ->get();

        foreach ($candidates as $sub) {
            if (!$sub->coversSector($sectorKey)) {
                continue;
            }
            if ($sub->status === VendorSubscription::STATUS_ACTIVE && ($sub->ends_at === null || $sub->ends_at->isFuture())) {
                return $sub;
            }
            if ($sub->status === VendorSubscription::STATUS_GRACE_PERIOD && $sub->grace_keep_features && ($sub->grace_ends_at === null || $sub->grace_ends_at->isFuture())) {
                return $sub;
            }
        }

        return null;
    }

    /**
     * Resolve latest subscription record for visibility (allows grace_keep_visible even if features disabled).
     */
    public function resolveVisibilitySubscription(Vendor $vendor, string $sectorKey): ?VendorSubscription
    {
        $sectorKey = $this->sectors->normalizeSectorKey($sectorKey);

        if (in_array($vendor->status, ['suspended', 'closed'], true) || in_array($vendor->verification_status, ['rejected', 'suspended'], true)) {
            return null;
        }
        if (!$vendor->isVerifiedMerchant()) {
            return null;
        }

        $this->syncJustInTimeExpiry($vendor);

        $candidates = VendorSubscription::query()
            ->where('vendor_id', $vendor->id)
            ->whereIn('status', [
                VendorSubscription::STATUS_ACTIVE,
                VendorSubscription::STATUS_GRACE_PERIOD,
                VendorSubscription::STATUS_EXPIRED,
            ])
            ->orderByRaw("CASE status WHEN 'active' THEN 0 WHEN 'grace_period' THEN 1 ELSE 2 END")
            ->orderByDesc('ends_at')
            ->get();

        foreach ($candidates as $sub) {
            if ($sub->coversSector($sectorKey) && $sub->isPubliclyVisible()) {
                return $sub;
            }
        }

        return null;
    }

    /**
     * Request-time expiry guard so an overdue active subscription transitions cleanly even before cron runs.
     */
    public function syncJustInTimeExpiry(Vendor $vendor): void
    {
        $overdue = VendorSubscription::query()
            ->where('vendor_id', $vendor->id)
            ->whereIn('status', [VendorSubscription::STATUS_ACTIVE, VendorSubscription::STATUS_GRACE_PERIOD])
            ->where(function ($q) {
                $q->where(function ($q1) {
                    $q1->where('status', VendorSubscription::STATUS_ACTIVE)
                       ->whereNotNull('ends_at')
                       ->where('ends_at', '<=', now());
                })->orWhere(function ($q2) {
                    $q2->where('status', VendorSubscription::STATUS_GRACE_PERIOD)
                       ->where(function ($qg) {
                           $qg->whereNull('grace_ends_at')->orWhere('grace_ends_at', '<=', now());
                       });
                });
            })
            ->get();

        if ($overdue->isEmpty()) {
            return;
        }

        app(SubscriptionLifecycleService::class)->transitionOverdueSubscriptions($overdue, 'request_time_guard');
    }

    public function hasFeature(Vendor $vendor, string $sectorKey, string $featureKey, bool $defaultIfStrictDisabled = true): bool
    {
        if (!$this->isStrictEnforcementEnabled()) {
            $sub = $this->resolveEffectiveSubscription($vendor, $sectorKey);
            if (!$sub) {
                return $defaultIfStrictDisabled;
            }
            return (bool) data_get($sub->entitlements_snapshot, $featureKey, false);
        }

        $sub = $this->resolveEffectiveSubscription($vendor, $sectorKey);
        if (!$sub) {
            return false;
        }

        return (bool) data_get($sub->entitlements_snapshot, $featureKey, false);
    }

    /**
     * Returns null when unlimited, 0 when denied/blocked, or positive integer limit.
     */
    public function getNumericLimit(Vendor $vendor, string $sectorKey, string $limitKey): ?int
    {
        $sub = $this->resolveEffectiveSubscription($vendor, $sectorKey);
        if (!$sub) {
            return $this->isStrictEnforcementEnabled() ? 0 : null;
        }

        $snapshot = $sub->entitlements_snapshot ?? [];
        if (!array_key_exists($limitKey, $snapshot)) {
            return null;
        }

        $val = $snapshot[$limitKey];
        if ($val === null || $val === '') {
            return null; // Unlimited
        }

        return max(0, (int) $val);
    }

    public function countCurrentUsage(Vendor $vendor, string $sectorKey, string $limitKey): int
    {
        return match ($limitKey) {
            'max_active_products' => Product::withoutGlobalScopes()
                ->where('vendor_id', $vendor->id)
                ->where('status', 'active')
                ->where(fn($q) => $q->whereNull('vertical')->orWhereNotIn('vertical', ['food', 'feasto', 'services', 'used', 'enquiry', 'b2b']))
                ->count(),
            'max_active_menu_items' => Product::withoutGlobalScopes()
                ->where('vendor_id', $vendor->id)
                ->where('vertical', 'food')
                ->where('status', 'active')
                ->count(),
            'max_active_services' => Product::withoutGlobalScopes()
                ->where('vendor_id', $vendor->id)
                ->where('vertical', 'services')
                ->where('status', 'active')
                ->count(),
            'max_portfolio_projects' => PortfolioProject::where('vendor_id', $vendor->id)
                ->where('status', 'published')
                ->count(),
            'max_active_used_listings' => Product::withoutGlobalScopes()
                ->where('vendor_id', $vendor->id)
                ->where('vertical', 'used')
                ->where('status', 'active')
                ->count(),
            'max_b2b_listings' => Product::withoutGlobalScopes()
                ->where('vendor_id', $vendor->id)
                ->where('vertical', 'enquiry')
                ->where('status', 'active')
                ->count(),
            default => 0,
        };
    }

    /**
     * Check if vendor can create/publish a new item under a numeric quota (excluding an existing record if editing).
     * Returns ['allowed' => bool, 'reason' => ?string, 'limit' => ?int, 'used' => int].
     */
    public function checkNumericQuota(Vendor $vendor, string $sectorKey, string $limitKey, int $increment = 1, ?int $excludeProductId = null): array
    {
        if (!$vendor->isVerifiedMerchant()) {
            return ['allowed' => false, 'reason' => 'Your merchant verification is pending or suspended.', 'limit' => 0, 'used' => 0];
        }

        $sub = $this->resolveEffectiveSubscription($vendor, $sectorKey);
        if (!$sub && $this->isStrictEnforcementEnabled()) {
            return [
                'allowed' => false,
                'reason' => "An active K71 {$sectorKey} subscription is required to publish or manage listings. Please contact K71 admin on WhatsApp.",
                'limit' => 0,
                'used' => 0,
            ];
        }

        $limit = $this->getNumericLimit($vendor, $sectorKey, $limitKey);
        $used = $this->countCurrentUsage($vendor, $sectorKey, $limitKey);
        if ($excludeProductId !== null) {
            $existingActive = Product::withoutGlobalScopes()
                ->where('id', $excludeProductId)
                ->where('vendor_id', $vendor->id)
                ->where('status', 'active')
                ->exists();
            if ($existingActive && $used > 0) {
                $used--;
            }
        }

        if ($limit !== null && ($used + $increment) > $limit) {
            return [
                'allowed' => false,
                'reason' => "Your current plan allows up to {$limit} active items in {$sectorKey} (currently using {$used}). Upgrade or contact K71 admin to expand your limit.",
                'limit' => $limit,
                'used' => $used,
            ];
        }

        return ['allowed' => true, 'reason' => null, 'limit' => $limit, 'used' => $used];
    }

    /**
     * Ensure vendor has an active subscription (or write permission) in the given sector before writing/editing.
     */
    public function authorizeSectorWrite(Vendor $vendor, string $sectorKey, ?string $featureKey = null): array
    {
        $sectorKey = $this->sectors->normalizeSectorKey($sectorKey);

        if (!$vendor->isVerifiedMerchant()) {
            return [
                'allowed' => false,
                'reason' => 'Merchant verification is pending or suspended by admin.',
            ];
        }

        if ($this->isStrictEnforcementEnabled()) {
            $sub = $this->resolveEffectiveSubscription($vendor, $sectorKey);
            if (!$sub) {
                return [
                    'allowed' => false,
                    'reason' => "No active subscription found for sector '{$sectorKey}'. Please contact K71 admin on WhatsApp to activate or renew your plan.",
                ];
            }
            if ($featureKey !== null && !$this->hasFeature($vendor, $sectorKey, $featureKey)) {
                return [
                    'allowed' => false,
                    'reason' => "Your active plan ('{$sub->plan_name_snapshot}') does not include '{$featureKey}'. Contact K71 admin to upgrade.",
                ];
            }
        } elseif ($featureKey !== null && !$this->hasFeature($vendor, $sectorKey, $featureKey, true)) {
            return [
                'allowed' => false,
                'reason' => "Your current plan does not include '{$featureKey}'.",
            ];
        }

        return ['allowed' => true, 'reason' => null];
    }

    /**
     * Unified write/publish/media/feature entitlement validation for products, food dishes, services, used & B2B listings.
     * Returns ['allowed' => bool, 'reason' => ?string].
     */
    public function validateListingWrite(
        Vendor $vendor,
        string $rawVertical,
        bool $isPublishingActive,
        int $newImagesCount = 0,
        int $existingImagesCount = 0,
        ?int $excludeProductId = null,
        array $requestedFeatures = []
    ): array {
        $sectorKey = $this->sectors->normalizeSectorKey($rawVertical);

        $writeAuth = $this->authorizeSectorWrite($vendor, $sectorKey);
        if (!$writeAuth['allowed']) {
            return $writeAuth;
        }

        // Check numeric active listing limit when creating/activating an active listing
        if ($isPublishingActive) {
            $limitKey = match ($sectorKey) {
                'food' => 'max_active_menu_items',
                'services' => 'max_active_services',
                'used' => 'max_active_used_listings',
                'enquiry' => 'max_b2b_listings',
                default => 'max_active_products',
            };
            $quotaCheck = $this->checkNumericQuota($vendor, $sectorKey, $limitKey, 1, $excludeProductId);
            if (!$quotaCheck['allowed']) {
                return ['allowed' => false, 'reason' => $quotaCheck['reason']];
            }
        }

        // Check image upload limits per item
        if ($newImagesCount > 0) {
            $imgLimitKey = match ($sectorKey) {
                'food' => 'max_menu_images',
                'used' => 'max_images_per_used_listing',
                default => 'max_images_per_product',
            };
            $imgLimit = $this->getNumericLimit($vendor, $sectorKey, $imgLimitKey);
            if ($imgLimit !== null && ($existingImagesCount + $newImagesCount) > $imgLimit) {
                return [
                    'allowed' => false,
                    'reason' => "Your plan allows up to {$imgLimit} image(s) per item in {$sectorKey}.",
                ];
            }
        }

        // Check feature toggles requested in payload
        foreach ($requestedFeatures as $featureKey) {
            if (!$this->hasFeature($vendor, $sectorKey, $featureKey, true)) {
                return [
                    'allowed' => false,
                    'reason' => "Your current {$sectorKey} subscription plan does not include '{$featureKey}'.",
                ];
            }
        }

        return ['allowed' => true, 'reason' => null];
    }

    public function getUsageSummaryForVendor(Vendor $vendor, string $sectorKey): array
    {
        $sub = $this->resolveEffectiveSubscription($vendor, $sectorKey);
        $catalog = $this->sectors->getEntitlementsForSector($sectorKey);
        $snapshot = $sub?->entitlements_snapshot ?? [];

        $items = [];
        foreach ($catalog as $key => $meta) {
            $val = array_key_exists($key, $snapshot) ? $snapshot[$key] : null;
            if ($meta['type'] === 'bool') {
                $items[] = [
                    'key' => $key,
                    'label' => $meta['label'],
                    'type' => 'bool',
                    'allowed' => $sub ? (bool) $val : false,
                    'limit' => null,
                    'used' => null,
                ];
            } else {
                $used = $this->countCurrentUsage($vendor, $sectorKey, $key);
                $items[] = [
                    'key' => $key,
                    'label' => $meta['label'],
                    'type' => 'quota',
                    'allowed' => $sub !== null,
                    'limit' => ($sub && $val !== null && $val !== '') ? (int) $val : ($sub ? null : 0),
                    'used' => $used,
                ];
            }
        }

        return $items;
    }
}
