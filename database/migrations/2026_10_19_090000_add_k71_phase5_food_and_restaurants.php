<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run migrations for K71 Phase 5: Food & Restaurants.
     */
    public function up(): void
    {
        // 1. Add restaurant settings to vendor_settings
        Schema::table('vendor_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('vendor_settings', 'is_restaurant')) {
                $table->boolean('is_restaurant')->default(false)->after('cod_enabled')->index();
            }
            if (!Schema::hasColumn('vendor_settings', 'is_pure_veg')) {
                $table->boolean('is_pure_veg')->default(false)->after('is_restaurant');
            }
            if (!Schema::hasColumn('vendor_settings', 'cuisine_types')) {
                $table->json('cuisine_types')->nullable()->after('is_pure_veg');
            }
            if (!Schema::hasColumn('vendor_settings', 'opening_time')) {
                $table->string('opening_time', 10)->default('10:00')->after('cuisine_types');
            }
            if (!Schema::hasColumn('vendor_settings', 'closing_time')) {
                $table->string('closing_time', 10)->default('23:00')->after('opening_time');
            }
            if (!Schema::hasColumn('vendor_settings', 'avg_prep_time_mins')) {
                $table->integer('avg_prep_time_mins')->default(25)->after('closing_time');
            }
            if (!Schema::hasColumn('vendor_settings', 'min_food_order_amount')) {
                $table->integer('min_food_order_amount')->default(10000)->after('avg_prep_time_mins'); // ₹100
            }
            if (!Schema::hasColumn('vendor_settings', 'food_delivery_fee')) {
                $table->integer('food_delivery_fee')->default(4000)->after('min_food_order_amount'); // ₹40
            }
            if (!Schema::hasColumn('vendor_settings', 'food_delivery_mode')) {
                $table->string('food_delivery_mode', 20)->default('both')->after('food_delivery_fee'); // delivery, takeaway, both
            }
        });

        // 2. Add food order attributes to orders table
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'order_type')) {
                $table->string('order_type', 30)->default('standard')->after('order_number')->index();
            }
            if (!Schema::hasColumn('orders', 'estimated_delivery_time')) {
                $table->timestamp('estimated_delivery_time')->nullable()->after('tracking_url');
            }
            if (!Schema::hasColumn('orders', 'kitchen_notes')) {
                $table->text('kitchen_notes')->nullable()->after('customer_notes');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['order_type', 'estimated_delivery_time', 'kitchen_notes']);
        });

        Schema::table('vendor_settings', function (Blueprint $table) {
            $table->dropColumn([
                'is_restaurant',
                'is_pure_veg',
                'cuisine_types',
                'opening_time',
                'closing_time',
                'avg_prep_time_mins',
                'min_food_order_amount',
                'food_delivery_fee',
                'food_delivery_mode',
            ]);
        });
    }
};
