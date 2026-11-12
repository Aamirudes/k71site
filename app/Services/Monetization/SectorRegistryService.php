<?php

namespace App\Services\Monetization;

use App\Models\K71Sector;
use App\Models\Order;
use App\Models\Product;
use App\Models\Vendor;
use Illuminate\Support\Collection;

class SectorRegistryService
{
    public const CORE_SECTORS = ['products', 'food', 'services', 'used', 'enquiry'];

    public function getAllSectors(bool $onlyActive = true): Collection
    {
        $query = K71Sector::query()->orderBy('sort_order')->orderBy('id');
        if ($onlyActive) {
            $query->where('is_active', true);
        }
        return $query->get();
    }

    public function getSectorMap(): array
    {
        return $this->getAllSectors(false)->keyBy('key')->all();
    }

    public function normalizeSectorKey(?string $raw): string
    {
        $val = strtolower(trim((string) $raw));
        return match (true) {
            in_array($val, ['food', 'feasto', 'feato', 'restaurant', 'kirana'], true) => 'food',
            in_array($val, ['services', 'service', 'digital_services', 'local_services'], true) => 'services',
            $val === 'used' => 'used',
            in_array($val, ['enquiry', 'b2b', 'machinery', 'real_estate', 'wholesale', 'industrial', 'manufacturer', 'quote_only'], true) => 'enquiry',
            default => 'products',
        };
    }

    public function resolveVendorPrimarySector(Vendor $vendor): string
    {
        $operating = $vendor->operating_sectors;
        if (is_array($operating) && !empty($operating[0])) {
            return $this->normalizeSectorKey((string) $operating[0]);
        }
        return $this->normalizeSectorKey($vendor->vendor_type);
    }

    public function resolveVendorSectors(Vendor $vendor): array
    {
        $operating = $vendor->operating_sectors;
        if (is_array($operating) && !empty($operating)) {
            $normalized = array_values(array_unique(array_map(fn($s) => $this->normalizeSectorKey((string) $s), $operating)));
            if (!empty($normalized)) {
                return $normalized;
            }
        }
        return [$this->normalizeSectorKey($vendor->vendor_type)];
    }

    public function resolveProductSector(Product $product): string
    {
        if (!empty($product->vertical)) {
            return $this->normalizeSectorKey($product->vertical);
        }
        if ($product->listing_type === 'classified') {
            return 'used';
        }
        if (in_array($product->listing_type, ['gig', 'service', 'booking'], true)) {
            return 'services';
        }
        if (in_array($product->listing_type, ['enquiry', 'quote_only'], true)) {
            return 'enquiry';
        }
        if ($product->vendor) {
            return $this->resolveVendorPrimarySector($product->vendor);
        }
        return 'products';
    }

    public function resolveOrderSector(Order $order): string
    {
        if ($order->isFoodOrder()) {
            return 'food';
        }
        if ($order->vendor) {
            return $this->resolveVendorPrimarySector($order->vendor);
        }
        return 'products';
    }

    public function getVendorTypesForSector(string $sectorKey): array
    {
        return match ($sectorKey) {
            'food' => ['food', 'feasto', 'restaurant', 'kirana'],
            'services' => ['services', 'service', 'digital_services', 'local_services'],
            'used' => ['used'],
            'enquiry' => ['enquiry', 'b2b', 'machinery', 'real_estate', 'wholesale', 'industrial', 'manufacturer'],
            default => ['marketplace', 'ecommerce', 'retail', 'clothing', 'fashion', 'footwear', 'electronics', 'home_living', 'beauty', 'automotive', 'marble', 'groceries', 'toys_sports', 'mens'],
        };
    }

    /**
     * Exact entitlement catalog mapped to real implemented features in K71.
     */
    public function getEntitlementCatalog(): array
    {
        return [
            'products' => [
                'max_active_products' => ['label' => 'Max Active Products (blank = unlimited)', 'type' => 'nullable_int', 'default' => 25],
                'max_images_per_product' => ['label' => 'Max Images per Product', 'type' => 'int', 'default' => 8],
                'allow_variants' => ['label' => 'Allow Product Variants & Option Matrix', 'type' => 'bool', 'default' => true],
                'allow_storefront_customization' => ['label' => 'Allow Store Design 2.0 Customization', 'type' => 'bool', 'default' => true],
                'allow_kinetic_scroll' => ['label' => 'Allow Kinetic Video Scroll Preset', 'type' => 'bool', 'default' => false],
                'allow_coupons' => ['label' => 'Allow Promotional Coupons', 'type' => 'bool', 'default' => true],
                'allow_shorts' => ['label' => 'Allow Shorts Studio Uploads', 'type' => 'bool', 'default' => true],
                'allow_analytics' => ['label' => 'Allow Vendor Analytics Access', 'type' => 'bool', 'default' => true],
                'priority_discovery' => ['label' => 'Discovery Ranking Eligibility', 'type' => 'bool', 'default' => false],
            ],
            'food' => [
                'max_active_menu_items' => ['label' => 'Max Active Menu Dishes (blank = unlimited)', 'type' => 'nullable_int', 'default' => 40],
                'max_menu_images' => ['label' => 'Max Images per Dish', 'type' => 'int', 'default' => 6],
                'allow_menu_addons' => ['label' => 'Allow Dish Portions, Option Groups & Add-ons', 'type' => 'bool', 'default' => true],
                'allow_food_orders' => ['label' => 'Allow Accepting Online Food Orders', 'type' => 'bool', 'default' => true],
                'allow_storefront_customization' => ['label' => 'Allow Storefront Customization', 'type' => 'bool', 'default' => true],
                'allow_coupons' => ['label' => 'Allow Restaurant Coupons', 'type' => 'bool', 'default' => true],
                'allow_analytics' => ['label' => 'Allow Kitchen Analytics Access', 'type' => 'bool', 'default' => true],
            ],
            'services' => [
                'allow_service_profile' => ['label' => 'Allow Public Personal Website Publishing', 'type' => 'bool', 'default' => true],
                'max_active_services' => ['label' => 'Max Active Service Gigs (blank = unlimited)', 'type' => 'nullable_int', 'default' => 10],
                'max_portfolio_projects' => ['label' => 'Max Published Portfolio Case Studies', 'type' => 'nullable_int', 'default' => 12],
                'allow_service_presets' => ['label' => 'Allow Website Presets & Design Hub', 'type' => 'bool', 'default' => true],
                'allow_enquiry_management' => ['label' => 'Allow Project Bids & Enquiry Responses', 'type' => 'bool', 'default' => true],
                'allow_analytics' => ['label' => 'Allow Service Analytics Access', 'type' => 'bool', 'default' => true],
            ],
            'used' => [
                'max_active_used_listings' => ['label' => 'Max Active Pre-Owned Dealer Listings', 'type' => 'nullable_int', 'default' => 20],
                'max_images_per_used_listing' => ['label' => 'Max Photos per Listing', 'type' => 'int', 'default' => 8],
                'allow_used_featured' => ['label' => 'Eligible for Featured Dealer Placement', 'type' => 'bool', 'default' => false],
                'allow_used_renewal' => ['label' => 'Allow Listing Refresh / Bump', 'type' => 'bool', 'default' => true],
            ],
            'enquiry' => [
                'allow_b2b_profile' => ['label' => 'Allow Public B2B Storefront Profile', 'type' => 'bool', 'default' => true],
                'max_b2b_listings' => ['label' => 'Max Active Wholesale / RFQ Catalog Listings', 'type' => 'nullable_int', 'default' => 30],
                'allow_rfq_responses' => ['label' => 'Allow Submitting RFQ Quotes to Buyers', 'type' => 'bool', 'default' => true],
                'allow_b2b_tiers' => ['label' => 'Allow Bulk Quantity Pricing Tiers', 'type' => 'bool', 'default' => true],
            ],
        ];
    }

    public function getEntitlementsForSector(string $sectorKey): array
    {
        $catalog = $this->getEntitlementCatalog();
        if ($sectorKey === 'all') {
            $merged = [];
            foreach ($catalog as $fields) {
                $merged = array_merge($merged, $fields);
            }
            return $merged;
        }
        return $catalog[$sectorKey] ?? $catalog['products'];
    }

    public function getDefaultExpiryPolicy(): array
    {
        return [
            'hide_storefront' => true,
            'hide_listings' => true,
            'block_new_listings' => true,
            'block_editing' => true,
            'grace_days' => (int) \App\Models\MonetizationSetting::getValue('default_grace_days', 3),
            'grace_keep_visible' => (bool) \App\Models\MonetizationSetting::getValue('default_grace_keep_visible', true),
            'grace_keep_features' => (bool) \App\Models\MonetizationSetting::getValue('default_grace_keep_features', false),
        ];
    }
}
