<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Vendor;
use App\Models\SubscriptionPlan;
use App\Models\VendorSubscription;

return new class extends Migration
{
    public function up(): void
    {
        $taj = Vendor::where('slug', 'taj-empire')->first();
        if ($taj) {
            $taj->update([
                'vendor_type' => 'gift_store',
                'operating_sectors' => ['products'],
            ]);

            $plan = SubscriptionPlan::firstOrCreate(
                ['code' => 'gift-store-pro'],
                [
                    'name' => 'Pro Gift Store',
                    'slug' => 'gift-store-pro',
                    'sector_key' => 'products',
                    'tier_key' => 'pro',
                    'is_free' => true,
                    'is_active' => true,
                    'price_paise' => 0,
                    'billing_interval' => 'monthly',
                    'features' => [
                        'themes_allowed' => ['*'],
                        'max_products' => 1000,
                        'max_active_products' => 1000,
                        'custom_domain' => true,
                    ],
                ]
            );

            VendorSubscription::firstOrCreate(
                ['vendor_id' => $taj->id, 'plan_id' => $plan->id],
                [
                    'sector_key' => 'products',
                    'plan_name_snapshot' => 'Pro Gift Store',
                    'price_amount_snapshot' => 0,
                    'status' => 'active',
                    'starts_at' => now(),
                    'current_period_end' => now()->addYear(),
                ]
            );
        }
    }

    public function down(): void
    {
        $taj = Vendor::where('slug', 'taj-empire')->first();
        if ($taj) {
            $taj->update([
                'vendor_type' => 'home_living',
            ]);
        }
    }
};
