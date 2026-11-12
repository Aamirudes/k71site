<?php

namespace App\Services\Monetization;

use App\Models\MonetizationSetting;
use App\Models\Product;
use App\Models\Vendor;
use App\Models\VendorSubscription;
use Illuminate\Database\Eloquent\Builder;

class VisibilityService
{
    public function __construct(
        protected SectorRegistryService $sectors,
        protected EntitlementService $entitlements
    ) {}

    public function isStrictVisibilityEnabled(): bool
    {
        return (bool) MonetizationSetting::getValue('enforce_subscription_visibility', true);
    }

    /**
     * Check if a vendor's storefront / profile should be visible publicly (overall or in a specific sector).
     */
    public function isVendorPubliclyVisible(Vendor $vendor, ?string $sectorKey = null): bool
    {
        if (!in_array($vendor->status, ['approved', 'active'], true)) {
            return false;
        }
        if (in_array($vendor->verification_status, ['rejected', 'suspended'], true)) {
            return false;
        }
        if ($vendor->verification_status && $vendor->verification_status !== 'verified') {
            return false;
        }

        if (!$this->isStrictVisibilityEnabled()) {
            return true;
        }

        $targetSector = $sectorKey ? $this->sectors->normalizeSectorKey($sectorKey) : $this->sectors->resolveVendorPrimarySector($vendor);
        return $this->entitlements->resolveVisibilitySubscription($vendor, $targetSector) !== null;
    }

    /**
     * Check if a vendor is publicly visible in AT LEAST ONE sector (for global directory or storefront middleware).
     */
    public function isVendorVisibleInAnySector(Vendor $vendor): bool
    {
        if (!in_array($vendor->status, ['approved', 'active'], true)) {
            return false;
        }
        if (in_array($vendor->verification_status, ['rejected', 'suspended'], true)) {
            return false;
        }
        if ($vendor->verification_status && $vendor->verification_status !== 'verified') {
            return false;
        }

        if (!$this->isStrictVisibilityEnabled()) {
            return true;
        }

        $this->entitlements->syncJustInTimeExpiry($vendor);

        $subs = VendorSubscription::query()
            ->where('vendor_id', $vendor->id)
            ->whereIn('status', [
                VendorSubscription::STATUS_ACTIVE,
                VendorSubscription::STATUS_GRACE_PERIOD,
                VendorSubscription::STATUS_EXPIRED,
            ])
            ->get();

        foreach ($subs as $sub) {
            if ($sub->isPubliclyVisible()) {
                return true;
            }
        }

        return false;
    }

    public function isProductPubliclyVisible(Product $product): bool
    {
        if ($product->status !== 'active') {
            return false;
        }

        // Individual C2C used items without a commercial vendor account follow C2C rules
        if (!$product->vendor_id) {
            return true;
        }

        $vendor = $product->relationLoaded('vendor') ? $product->vendor : $product->vendor()->first();
        if (!$vendor) {
            return false;
        }

        $sector = $this->sectors->resolveProductSector($product);
        return $this->isVendorPubliclyVisible($vendor, $sector);
    }

    /**
     * Scope a Vendor query to publicly visible merchants in the optional sector.
     */
    public function scopeVisibleVendors(Builder $query, ?string $sectorKey = null): Builder
    {
        $query->whereIn('vendors.status', ['approved', 'active'])
            ->where(function ($vq) {
                $vq->whereNull('vendors.verification_status')
                   ->orWhere('vendors.verification_status', 'verified');
            });

        if (!$this->isStrictVisibilityEnabled()) {
            return $query;
        }

        $now = now();
        return $query->whereHas('subscriptions', function ($sq) use ($sectorKey, $now) {
            $this->applyVisibleSubscriptionFilter($sq, $sectorKey, $now);
        });
    }

    /**
     * Scope a Product query so items belonging to unverified, unsubscribed, suspended, or expired vendors stay hidden.
     * Standalone C2C used listings (`vendor_id IS NULL`) remain visible unless flagged otherwise.
     */
    public function scopeVisibleProducts(Builder $query, ?string $sectorKey = null, bool $allowNullVendor = false): Builder
    {
        $query->where('products.status', 'active');

        $applyVendorCheck = function (Builder $vq) use ($sectorKey) {
            $this->scopeVisibleVendors($vq, $sectorKey);
        };

        if ($allowNullVendor) {
            return $query->where(function ($outer) use ($applyVendorCheck) {
                $outer->whereNull('products.vendor_id')
                      ->orWhereHas('vendor', $applyVendorCheck);
            });
        }

        return $query->whereHas('vendor', $applyVendorCheck);
    }

    protected function applyVisibleSubscriptionFilter(Builder $sq, ?string $sectorKey, $now): void
    {
        if ($sectorKey !== null) {
            $sectorKey = $this->sectors->normalizeSectorKey($sectorKey);
            $sq->where(function ($secQ) use ($sectorKey) {
                $secQ->where('sector_key', $sectorKey)
                     ->orWhere('sector_key', 'all')
                     ->orWhereJsonContains('applicable_sectors_snapshot', $sectorKey)
                     ->orWhereJsonContains('applicable_sectors_snapshot', 'all');
            });
        }

        $sq->where(function ($stQ) use ($now) {
            $stQ->where(function ($activeQ) use ($now) {
                $activeQ->where('status', VendorSubscription::STATUS_ACTIVE)
                        ->where(function ($e) use ($now) {
                            $e->whereNull('ends_at')->orWhere('ends_at', '>', $now);
                        });
            })->orWhere(function ($graceQ) use ($now) {
                $graceQ->where('status', VendorSubscription::STATUS_GRACE_PERIOD)
                       ->where('grace_keep_visible', true)
                       ->where(function ($e) use ($now) {
                           $e->whereNull('grace_ends_at')->orWhere('grace_ends_at', '>', $now);
                       });
            });
        });
    }
}
