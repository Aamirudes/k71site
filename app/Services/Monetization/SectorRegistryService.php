<?php

namespace App\Services\Monetization;

use App\Models\K71Sector;
use App\Models\Order;
use App\Models\Product;
use App\Models\SubscriptionPlan;
use App\Models\SubscriptionPlanVersion;
use App\Models\Vendor;
use Illuminate\Support\Collection;

class SectorRegistryService
{
    public const CORE_SECTORS = ['products', 'food', 'services', 'used', 'enquiry'];
    public const TIER_KEYS = ['starter', 'professional', 'elite'];

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
                'max_active_products' => ['label' => 'Max Active Products', 'group' => 'Quotas & Limits', 'type' => 'nullable_int', 'default' => 25],
                'max_images_per_product' => ['label' => 'Max Images per Product', 'group' => 'Quotas & Limits', 'type' => 'int', 'default' => 8],
                'max_active_shorts' => ['label' => 'Max Active Video Shorts', 'group' => 'Quotas & Limits', 'type' => 'nullable_int', 'default' => 10],
                'allow_variants' => ['label' => 'Product Variants & Option Matrix', 'group' => 'Core Features', 'type' => 'bool', 'default' => true],
                'allow_storefront_customization' => ['label' => 'Store Design 2.0 Customization', 'group' => 'Storefront & Design', 'type' => 'bool', 'default' => true],
                'allow_kinetic_scroll' => ['label' => 'Kinetic Video Scroll Preset', 'group' => 'Storefront & Design', 'type' => 'bool', 'default' => false],
                'allow_coupons' => ['label' => 'Promotional Coupons', 'group' => 'Promotions & Growth', 'type' => 'bool', 'default' => true],
                'allow_shorts' => ['label' => 'Shorts Studio Uploads', 'group' => 'Promotions & Growth', 'type' => 'bool', 'default' => true],
                'allow_short_boosts' => ['label' => 'Short Reel Coin Boosts', 'group' => 'Promotions & Growth', 'type' => 'bool', 'default' => true],
                'allow_featured_products' => ['label' => 'Featured Product Flag', 'group' => 'Promotions & Growth', 'type' => 'bool', 'default' => true],
                'allow_analytics' => ['label' => 'Vendor Analytics Access', 'group' => 'Insights & Visibility', 'type' => 'bool', 'default' => true],
                'priority_discovery' => ['label' => 'Priority Marketplace Discovery', 'group' => 'Insights & Visibility', 'type' => 'bool', 'default' => false],
            ],
            'food' => [
                'max_active_menu_items' => ['label' => 'Max Active Menu Dishes', 'group' => 'Quotas & Limits', 'type' => 'nullable_int', 'default' => 40],
                'max_menu_images' => ['label' => 'Max Images per Dish', 'group' => 'Quotas & Limits', 'type' => 'int', 'default' => 6],
                'max_active_shorts' => ['label' => 'Max Food Reels / Shorts', 'group' => 'Quotas & Limits', 'type' => 'nullable_int', 'default' => 10],
                'allow_menu_addons' => ['label' => 'Dish Portions, Option Groups & Add-ons', 'group' => 'Core Features', 'type' => 'bool', 'default' => true],
                'allow_food_orders' => ['label' => 'Accept Live Food Orders', 'group' => 'Core Features', 'type' => 'bool', 'default' => true],
                'allow_storefront_customization' => ['label' => 'Restaurant Storefront Customization', 'group' => 'Storefront & Design', 'type' => 'bool', 'default' => true],
                'allow_coupons' => ['label' => 'Restaurant Promo Coupons', 'group' => 'Promotions & Growth', 'type' => 'bool', 'default' => true],
                'allow_shorts' => ['label' => 'Shorts Studio Uploads', 'group' => 'Promotions & Growth', 'type' => 'bool', 'default' => true],
                'allow_short_boosts' => ['label' => 'Short Reel Coin Boosts', 'group' => 'Promotions & Growth', 'type' => 'bool', 'default' => true],
                'allow_analytics' => ['label' => 'Kitchen Analytics Access', 'group' => 'Insights & Visibility', 'type' => 'bool', 'default' => true],
                'priority_discovery' => ['label' => 'Priority Food Discovery', 'group' => 'Insights & Visibility', 'type' => 'bool', 'default' => false],
            ],
            'services' => [
                'allow_service_profile' => ['label' => 'Public Personal Website Publishing', 'group' => 'Core Features', 'type' => 'bool', 'default' => true],
                'max_active_services' => ['label' => 'Max Active Service Gigs', 'group' => 'Quotas & Limits', 'type' => 'nullable_int', 'default' => 10],
                'max_portfolio_projects' => ['label' => 'Max Published Portfolio Case Studies', 'group' => 'Quotas & Limits', 'type' => 'nullable_int', 'default' => 12],
                'max_portfolio_media' => ['label' => 'Max Gallery Photos per Case Study', 'group' => 'Quotas & Limits', 'type' => 'int', 'default' => 6],
                'allow_service_presets' => ['label' => 'Website Presets & Design Hub', 'group' => 'Storefront & Design', 'type' => 'bool', 'default' => true],
                'allow_enquiry_management' => ['label' => 'Project Bids & Enquiry Responses', 'group' => 'Core Features', 'type' => 'bool', 'default' => true],
                'allow_coupons' => ['label' => 'Promotional Service Coupons', 'group' => 'Promotions & Growth', 'type' => 'bool', 'default' => true],
                'allow_analytics' => ['label' => 'Service Website Analytics', 'group' => 'Insights & Visibility', 'type' => 'bool', 'default' => true],
                'priority_discovery' => ['label' => 'Priority Directory Placement', 'group' => 'Insights & Visibility', 'type' => 'bool', 'default' => false],
            ],
            'used' => [
                'max_active_used_listings' => ['label' => 'Max Active Used Listings', 'group' => 'Quotas & Limits', 'type' => 'nullable_int', 'default' => 20],
                'max_images_per_used_listing' => ['label' => 'Max Photos per Listing', 'group' => 'Quotas & Limits', 'type' => 'int', 'default' => 8],
                'max_active_shorts' => ['label' => 'Max Inspection Video Shorts', 'group' => 'Quotas & Limits', 'type' => 'nullable_int', 'default' => 8],
                'allow_offer_negotiation' => ['label' => 'Buyer Offer Negotiation Inbox', 'group' => 'Core Features', 'type' => 'bool', 'default' => true],
                'allow_used_renewal' => ['label' => 'Listing Refresh / Duplicate Bump', 'group' => 'Promotions & Growth', 'type' => 'bool', 'default' => true],
                'allow_used_featured' => ['label' => 'Featured Dealer Placement', 'group' => 'Promotions & Growth', 'type' => 'bool', 'default' => false],
                'allow_storefront_customization' => ['label' => 'Dealer Storefront Customization', 'group' => 'Storefront & Design', 'type' => 'bool', 'default' => true],
                'allow_shorts' => ['label' => 'Shorts Studio Uploads', 'group' => 'Promotions & Growth', 'type' => 'bool', 'default' => true],
                'allow_analytics' => ['label' => 'Dealer Analytics Access', 'group' => 'Insights & Visibility', 'type' => 'bool', 'default' => true],
            ],
            'enquiry' => [
                'allow_b2b_profile' => ['label' => 'Public B2B Storefront Profile', 'group' => 'Core Features', 'type' => 'bool', 'default' => true],
                'max_b2b_listings' => ['label' => 'Max Active B2B / RFQ Catalog Items', 'group' => 'Quotas & Limits', 'type' => 'nullable_int', 'default' => 30],
                'max_images_per_product' => ['label' => 'Max Photos per Catalog Item', 'group' => 'Quotas & Limits', 'type' => 'int', 'default' => 8],
                'max_active_shorts' => ['label' => 'Max Product Demo Shorts', 'group' => 'Quotas & Limits', 'type' => 'nullable_int', 'default' => 10],
                'allow_rfq_responses' => ['label' => 'Submit Official RFQ Quotes', 'group' => 'Core Features', 'type' => 'bool', 'default' => true],
                'allow_b2b_tiers' => ['label' => 'Bulk Quantity Pricing Tiers', 'group' => 'Core Features', 'type' => 'bool', 'default' => true],
                'allow_storefront_customization' => ['label' => 'B2B Storefront Customization', 'group' => 'Storefront & Design', 'type' => 'bool', 'default' => true],
                'allow_shorts' => ['label' => 'Shorts Studio Uploads', 'group' => 'Promotions & Growth', 'type' => 'bool', 'default' => true],
                'allow_analytics' => ['label' => 'B2B Enquiry Analytics', 'group' => 'Insights & Visibility', 'type' => 'bool', 'default' => true],
                'priority_discovery' => ['label' => 'Priority B2B Directory Placement', 'group' => 'Insights & Visibility', 'type' => 'bool', 'default' => false],
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

    /**
     * Ensure every core sector has the 3 configurable plan tiers (Starter, Professional, Elite) idempotently.
     * Existing customized plans or live vendor subscriptions are NEVER overwritten.
     */
    public function ensureDefaultThreeTierPlans(): void
    {
        $tierDefinitions = [
            'starter' => [
                'name' => 'Starter',
                'badge_text' => 'Essential',
                'sort_order' => 1,
                'price_map' => ['products' => 49900, 'food' => 49900, 'services' => 39900, 'used' => 29900, 'enquiry' => 79900],
                'commission_rate' => 8.00,
                'duration_days' => 30,
                'description' => 'Essential storefront & catalog starter pack for new merchants.',
                'benefits' => ['Dedicated vendor storefront URL', 'WhatsApp & offline verified onboarding', 'Starter catalog & order notifications'],
            ],
            'professional' => [
                'name' => 'Professional',
                'badge_text' => 'Most Popular',
                'sort_order' => 2,
                'price_map' => ['products' => 149900, 'food' => 149900, 'services' => 99900, 'used' => 79900, 'enquiry' => 199900],
                'commission_rate' => 5.00,
                'duration_days' => 30,
                'description' => 'Growth toolkit with higher catalog quotas, promos, shorts & analytics.',
                'benefits' => ['Expanded listing & media quotas', 'Coupons, Shorts Studio & analytics unlocked', 'Lower platform commission tier'],
            ],
            'elite' => [
                'name' => 'Elite',
                'badge_text' => 'Flagship',
                'sort_order' => 3,
                'price_map' => ['products' => 299900, 'food' => 299900, 'services' => 199900, 'used' => 149900, 'enquiry' => 399900],
                'commission_rate' => 2.50,
                'duration_days' => 30,
                'description' => 'Flagship unlimited scale, priority discovery & lowest commission.',
                'benefits' => ['Unlimited catalog & shorts capacity', 'Priority marketplace discovery placement', 'Lowest platform commission rate'],
            ],
        ];

        $sectorEntitlementPresets = $this->buildDefaultTierEntitlements();
        $defaultExpiry = $this->getDefaultExpiryPolicy();

        foreach (self::CORE_SECTORS as $sectorKey) {
            foreach (self::TIER_KEYS as $tierKey) {
                $exists = SubscriptionPlan::withTrashed()
                    ->where('sector_key', $sectorKey)
                    ->where('tier_key', $tierKey)
                    ->first();

                if ($exists) {
                    continue;
                }

                $slug = "{$sectorKey}-{$tierKey}";
                $bySlug = SubscriptionPlan::withTrashed()->where('slug', $slug)->first();
                if ($bySlug) {
                    $bySlug->forceFill([
                        'tier_key' => $bySlug->tier_key ?: $tierKey,
                        'sort_order' => $bySlug->sort_order ?: $tierDefinitions[$tierKey]['sort_order'],
                    ])->saveQuietly();
                    continue;
                }

                $def = $tierDefinitions[$tierKey];
                $priceAmount = $def['price_map'][$sectorKey] ?? 99900;
                $entitlements = $sectorEntitlementPresets[$sectorKey][$tierKey] ?? [];

                $plan = SubscriptionPlan::create([
                    'code' => strtoupper("{$sectorKey}_{$tierKey}"),
                    'name' => $def['name'],
                    'badge_text' => $def['badge_text'],
                    'slug' => $slug,
                    'description' => $def['description'],
                    'sector_key' => $sectorKey,
                    'tier_key' => $tierKey,
                    'applicable_sectors' => [$sectorKey],
                    'is_cross_sector' => false,
                    'is_free' => false,
                    'price_amount' => $priceAmount,
                    'price_monthly' => $priceAmount,
                    'currency' => 'INR',
                    'duration_days' => $def['duration_days'],
                    'duration_unit' => 'days',
                    'duration_value' => $def['duration_days'],
                    'custom_duration_allowed' => true,
                    'commission_type' => 'percentage',
                    'commission_rate' => $def['commission_rate'],
                    'commission_fixed_paise' => 0,
                    'features' => $entitlements,
                    'benefits_json' => $def['benefits'],
                    'status' => 'active',
                    'is_active' => true,
                    'is_public_display' => true,
                    'sort_order' => $def['sort_order'],
                    'current_version' => 1,
                    'auto_apply_to_existing' => false,
                ]);

                SubscriptionPlanVersion::create([
                    'plan_id' => $plan->id,
                    'version_number' => 1,
                    'name' => $plan->name,
                    'badge_text' => $plan->badge_text,
                    'description' => $plan->description,
                    'is_free' => false,
                    'price_amount' => $priceAmount,
                    'currency' => 'INR',
                    'duration_days' => $def['duration_days'],
                    'commission_type' => 'percentage',
                    'commission_rate' => $def['commission_rate'],
                    'commission_fixed_paise' => 0,
                    'entitlements_json' => $entitlements,
                    'expiry_policy_json' => $defaultExpiry,
                    'benefits_json' => $def['benefits'],
                    'change_notes' => 'Seeded default ' . ucfirst($tierKey) . ' tier for ' . $sectorKey,
                ]);
            }
        }
    }

    protected function buildDefaultTierEntitlements(): array
    {
        return [
            'products' => [
                'starter' => [
                    'max_active_products' => 15, 'max_images_per_product' => 5, 'max_active_shorts' => 3,
                    'allow_variants' => true, 'allow_storefront_customization' => true, 'allow_kinetic_scroll' => false,
                    'allow_coupons' => false, 'allow_shorts' => true, 'allow_short_boosts' => false,
                    'allow_featured_products' => false, 'allow_analytics' => false, 'priority_discovery' => false,
                ],
                'professional' => [
                    'max_active_products' => 100, 'max_images_per_product' => 10, 'max_active_shorts' => 25,
                    'allow_variants' => true, 'allow_storefront_customization' => true, 'allow_kinetic_scroll' => false,
                    'allow_coupons' => true, 'allow_shorts' => true, 'allow_short_boosts' => true,
                    'allow_featured_products' => true, 'allow_analytics' => true, 'priority_discovery' => false,
                ],
                'elite' => [
                    'max_active_products' => null, 'max_images_per_product' => 15, 'max_active_shorts' => null,
                    'allow_variants' => true, 'allow_storefront_customization' => true, 'allow_kinetic_scroll' => true,
                    'allow_coupons' => true, 'allow_shorts' => true, 'allow_short_boosts' => true,
                    'allow_featured_products' => true, 'allow_analytics' => true, 'priority_discovery' => true,
                ],
            ],
            'food' => [
                'starter' => [
                    'max_active_menu_items' => 25, 'max_menu_images' => 4, 'max_active_shorts' => 3,
                    'allow_menu_addons' => true, 'allow_food_orders' => true, 'allow_storefront_customization' => true,
                    'allow_coupons' => false, 'allow_shorts' => true, 'allow_short_boosts' => false,
                    'allow_analytics' => false, 'priority_discovery' => false,
                ],
                'professional' => [
                    'max_active_menu_items' => 120, 'max_menu_images' => 8, 'max_active_shorts' => 20,
                    'allow_menu_addons' => true, 'allow_food_orders' => true, 'allow_storefront_customization' => true,
                    'allow_coupons' => true, 'allow_shorts' => true, 'allow_short_boosts' => true,
                    'allow_analytics' => true, 'priority_discovery' => false,
                ],
                'elite' => [
                    'max_active_menu_items' => null, 'max_menu_images' => 12, 'max_active_shorts' => null,
                    'allow_menu_addons' => true, 'allow_food_orders' => true, 'allow_storefront_customization' => true,
                    'allow_coupons' => true, 'allow_shorts' => true, 'allow_short_boosts' => true,
                    'allow_analytics' => true, 'priority_discovery' => true,
                ],
            ],
            'services' => [
                'starter' => [
                    'allow_service_profile' => true, 'max_active_services' => 5, 'max_portfolio_projects' => 6, 'max_portfolio_media' => 4,
                    'allow_service_presets' => true, 'allow_enquiry_management' => true, 'allow_coupons' => false,
                    'allow_analytics' => false, 'priority_discovery' => false,
                ],
                'professional' => [
                    'allow_service_profile' => true, 'max_active_services' => 25, 'max_portfolio_projects' => 25, 'max_portfolio_media' => 10,
                    'allow_service_presets' => true, 'allow_enquiry_management' => true, 'allow_coupons' => true,
                    'allow_analytics' => true, 'priority_discovery' => false,
                ],
                'elite' => [
                    'allow_service_profile' => true, 'max_active_services' => null, 'max_portfolio_projects' => null, 'max_portfolio_media' => 20,
                    'allow_service_presets' => true, 'allow_enquiry_management' => true, 'allow_coupons' => true,
                    'allow_analytics' => true, 'priority_discovery' => true,
                ],
            ],
            'used' => [
                'starter' => [
                    'max_active_used_listings' => 15, 'max_images_per_used_listing' => 6, 'max_active_shorts' => 3,
                    'allow_offer_negotiation' => true, 'allow_used_renewal' => true, 'allow_used_featured' => false,
                    'allow_storefront_customization' => true, 'allow_shorts' => false, 'allow_analytics' => false,
                ],
                'professional' => [
                    'max_active_used_listings' => 75, 'max_images_per_used_listing' => 10, 'max_active_shorts' => 15,
                    'allow_offer_negotiation' => true, 'allow_used_renewal' => true, 'allow_used_featured' => true,
                    'allow_storefront_customization' => true, 'allow_shorts' => true, 'allow_analytics' => true,
                ],
                'elite' => [
                    'max_active_used_listings' => null, 'max_images_per_used_listing' => 15, 'max_active_shorts' => null,
                    'allow_offer_negotiation' => true, 'allow_used_renewal' => true, 'allow_used_featured' => true,
                    'allow_storefront_customization' => true, 'allow_shorts' => true, 'allow_analytics' => true,
                ],
            ],
            'enquiry' => [
                'starter' => [
                    'allow_b2b_profile' => true, 'max_b2b_listings' => 15, 'max_images_per_product' => 6, 'max_active_shorts' => 3,
                    'allow_rfq_responses' => true, 'allow_b2b_tiers' => false, 'allow_storefront_customization' => true,
                    'allow_shorts' => false, 'allow_analytics' => false, 'priority_discovery' => false,
                ],
                'professional' => [
                    'allow_b2b_profile' => true, 'max_b2b_listings' => 100, 'max_images_per_product' => 10, 'max_active_shorts' => 20,
                    'allow_rfq_responses' => true, 'allow_b2b_tiers' => true, 'allow_storefront_customization' => true,
                    'allow_shorts' => true, 'allow_analytics' => true, 'priority_discovery' => false,
                ],
                'elite' => [
                    'allow_b2b_profile' => true, 'max_b2b_listings' => null, 'max_images_per_product' => 15, 'max_active_shorts' => null,
                    'allow_rfq_responses' => true, 'allow_b2b_tiers' => true, 'allow_storefront_customization' => true,
                    'allow_shorts' => true, 'allow_analytics' => true, 'priority_discovery' => true,
                ],
            ],
        ];
    }
}
