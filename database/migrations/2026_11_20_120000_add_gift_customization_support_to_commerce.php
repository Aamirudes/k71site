<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('products') && !Schema::hasColumn('products', 'gift_customization_config')) {
            Schema::table('products', function (Blueprint $table) {
                $table->json('gift_customization_config')->nullable()->after('addons_json');
            });
        }

        if (Schema::hasTable('cart_items') && !Schema::hasColumn('cart_items', 'customization_data')) {
            Schema::table('cart_items', function (Blueprint $table) {
                $table->json('customization_data')->nullable()->after('option_selections_json');
            });
        }

        if (Schema::hasTable('order_items') && !Schema::hasColumn('order_items', 'customization_data')) {
            Schema::table('order_items', function (Blueprint $table) {
                $table->json('customization_data')->nullable()->after('meta_json');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('products') && Schema::hasColumn('products', 'gift_customization_config')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('gift_customization_config');
            });
        }

        if (Schema::hasTable('cart_items') && Schema::hasColumn('cart_items', 'customization_data')) {
            Schema::table('cart_items', function (Blueprint $table) {
                $table->dropColumn('customization_data');
            });
        }

        if (Schema::hasTable('order_items') && Schema::hasColumn('order_items', 'customization_data')) {
            Schema::table('order_items', function (Blueprint $table) {
                $table->dropColumn('customization_data');
            });
        }
    }
};
