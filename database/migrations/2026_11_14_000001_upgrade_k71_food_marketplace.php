<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Extend vendor_settings for real-world Food & Restaurant operations
        if (Schema::hasTable('vendor_settings')) {
            Schema::table('vendor_settings', function (Blueprint $table) {
                if (!Schema::hasColumn('vendor_settings', 'is_accepting_food_orders')) {
                    $table->boolean('is_accepting_food_orders')->default(true)->after('is_restaurant')->index();
                }
                if (!Schema::hasColumn('vendor_settings', 'paused_reason')) {
                    $table->string('paused_reason', 255)->nullable()->after('is_accepting_food_orders');
                }
                if (!Schema::hasColumn('vendor_settings', 'enforce_operating_hours')) {
                    $table->boolean('enforce_operating_hours')->default(false)->after('closing_time');
                }
                if (!Schema::hasColumn('vendor_settings', 'weekly_hours_json')) {
                    $table->json('weekly_hours_json')->nullable()->after('enforce_operating_hours');
                }
                if (!Schema::hasColumn('vendor_settings', 'service_cities_json')) {
                    $table->json('service_cities_json')->nullable()->after('serviceable_pincodes');
                }
                if (!Schema::hasColumn('vendor_settings', 'food_packaging_fee')) {
                    $table->integer('food_packaging_fee')->default(0)->after('food_delivery_fee');
                }
                if (!Schema::hasColumn('vendor_settings', 'food_free_delivery_above')) {
                    $table->integer('food_free_delivery_above')->nullable()->after('food_packaging_fee');
                }
                if (!Schema::hasColumn('vendor_settings', 'fssai_license_number')) {
                    $table->string('fssai_license_number', 30)->nullable()->after('food_delivery_mode');
                }
                if (!Schema::hasColumn('vendor_settings', 'fssai_verified')) {
                    $table->boolean('fssai_verified')->default(false)->after('fssai_license_number');
                }
                if (!Schema::hasColumn('vendor_settings', 'fssai_verified_at')) {
                    $table->timestamp('fssai_verified_at')->nullable()->after('fssai_verified');
                }
                if (!Schema::hasColumn('vendor_settings', 'food_tax_percent')) {
                    $table->decimal('food_tax_percent', 5, 2)->nullable()->default(5.00)->after('fssai_verified_at');
                }
                if (!Schema::hasColumn('vendor_settings', 'food_avg_rating')) {
                    $table->decimal('food_avg_rating', 3, 2)->nullable()->after('food_tax_percent');
                }
                if (!Schema::hasColumn('vendor_settings', 'food_review_count')) {
                    $table->unsignedInteger('food_review_count')->default(0)->after('food_avg_rating');
                }
            });
        }

        // 2. Per-restaurant Food Menu Categories (Starters, Mains, Combos, Desserts, etc.)
        if (!Schema::hasTable('food_menu_categories')) {
            Schema::create('food_menu_categories', function (Blueprint $table) {
                $table->id();
                $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();
                $table->string('name', 100);
                $table->string('slug', 120);
                $table->string('description', 255)->nullable();
                $table->integer('sort_order')->default(0);
                $table->boolean('is_active')->default(true)->index();
                $table->timestamps();

                $table->unique(['vendor_id', 'slug'], 'food_menu_cats_vendor_slug_unique');
                $table->index(['vendor_id', 'is_active', 'sort_order'], 'food_menu_cats_vendor_sort_idx');
            });
        }

        // 3. Structured Dish Option Groups (Single/Multi-select, Required, Min/Max)
        if (!Schema::hasTable('product_option_groups')) {
            Schema::create('product_option_groups', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
                $table->string('name', 100);
                $table->string('selection_type', 16)->default('multi'); // single, multi
                $table->boolean('is_required')->default(false);
                $table->unsignedSmallInteger('min_selections')->default(0);
                $table->unsignedSmallInteger('max_selections')->nullable();
                $table->unsignedSmallInteger('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->index(['product_id', 'sort_order'], 'prod_opt_groups_prod_sort_idx');
            });
        }

        // 4. Extend product_modifiers to link optionally into product_option_groups + availability
        if (Schema::hasTable('product_modifiers')) {
            Schema::table('product_modifiers', function (Blueprint $table) {
                if (!Schema::hasColumn('product_modifiers', 'option_group_id')) {
                    $table->unsignedBigInteger('option_group_id')->nullable()->after('product_id')->index();
                }
                if (!Schema::hasColumn('product_modifiers', 'is_available')) {
                    $table->boolean('is_available')->default(true)->after('is_required');
                }
                if (!Schema::hasColumn('product_modifiers', 'is_default')) {
                    $table->boolean('is_default')->default(false)->after('is_available');
                }
                if (!Schema::hasColumn('product_modifiers', 'sort_order')) {
                    $table->integer('sort_order')->default(0)->after('is_default');
                }
            });
        }

        // 5. Extend products for rich restaurant menu control
        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                if (!Schema::hasColumn('products', 'food_menu_category_id')) {
                    $table->unsignedBigInteger('food_menu_category_id')->nullable()->after('category_id')->index();
                }
                if (!Schema::hasColumn('products', 'dietary_label')) {
                    $table->string('dietary_label', 20)->nullable()->after('food_menu_category_id')->index();
                }
                if (!Schema::hasColumn('products', 'prep_time_mins')) {
                    $table->unsignedSmallInteger('prep_time_mins')->nullable()->after('dietary_label');
                }
                if (!Schema::hasColumn('products', 'menu_sort_order')) {
                    $table->integer('menu_sort_order')->default(0)->after('prep_time_mins');
                }
                if (!Schema::hasColumn('products', 'is_dish_available')) {
                    $table->boolean('is_dish_available')->default(true)->after('menu_sort_order');
                }
                if (!Schema::hasColumn('products', 'available_from')) {
                    $table->string('available_from', 10)->nullable()->after('is_dish_available');
                }
                if (!Schema::hasColumn('products', 'available_until')) {
                    $table->string('available_until', 10)->nullable()->after('available_from');
                }
            });
        }

        // 6. Extend cart_items for kitchen instructions & option selections snapshot
        if (Schema::hasTable('cart_items')) {
            Schema::table('cart_items', function (Blueprint $table) {
                if (!Schema::hasColumn('cart_items', 'special_instructions')) {
                    $table->text('special_instructions')->nullable()->after('booking_date_time');
                }
                if (!Schema::hasColumn('cart_items', 'option_selections_json')) {
                    $table->json('option_selections_json')->nullable()->after('special_instructions');
                }
            });
        }

        // 7. Extend orders for food packaging, takeaway pickup codes & kitchen stage timestamps
        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                if (!Schema::hasColumn('orders', 'packaging_amount')) {
                    $table->integer('packaging_amount')->default(0)->after('shipping_amount');
                }
                if (!Schema::hasColumn('orders', 'pickup_code')) {
                    $table->string('pickup_code', 12)->nullable()->after('order_type')->index();
                }
                if (!Schema::hasColumn('orders', 'accepted_at')) {
                    $table->timestamp('accepted_at')->nullable()->after('vendor_accepted_at');
                }
                if (!Schema::hasColumn('orders', 'preparing_at')) {
                    $table->timestamp('preparing_at')->nullable()->after('accepted_at');
                }
                if (!Schema::hasColumn('orders', 'ready_at')) {
                    $table->timestamp('ready_at')->nullable()->after('preparing_at');
                }
                if (!Schema::hasColumn('orders', 'out_for_delivery_at')) {
                    $table->timestamp('out_for_delivery_at')->nullable()->after('ready_at');
                }
                if (!Schema::hasColumn('orders', 'food_fulfillment_stage')) {
                    $table->string('food_fulfillment_stage', 30)->nullable()->after('status');
                }
            });
        }

        // 8. Extend order_items for dish special instructions & add-on breakdown
        if (Schema::hasTable('order_items')) {
            Schema::table('order_items', function (Blueprint $table) {
                if (!Schema::hasColumn('order_items', 'special_instructions')) {
                    $table->text('special_instructions')->nullable()->after('booking_date_time');
                }
                if (!Schema::hasColumn('order_items', 'addon_amount')) {
                    $table->integer('addon_amount')->default(0)->after('unit_price');
                }
            });
        }

        // 9. Safely make reviews.product_id nullable & ensure restaurant/food review columns exist
        if (Schema::hasTable('reviews')) {
            $this->makeForeignColumnNullable('reviews', 'product_id', 'products');

            Schema::table('reviews', function (Blueprint $table) {
                if (!Schema::hasColumn('reviews', 'vendor_id')) {
                    $table->unsignedBigInteger('vendor_id')->nullable()->after('user_id')->index();
                }
                if (!Schema::hasColumn('reviews', 'review_type')) {
                    $table->string('review_type', 25)->default('product')->after('vendor_id')->index();
                }
                if (!Schema::hasColumn('reviews', 'vendor_reply')) {
                    $table->text('vendor_reply')->nullable()->after('is_approved');
                }
                if (!Schema::hasColumn('reviews', 'vendor_replied_at')) {
                    $table->timestamp('vendor_replied_at')->nullable()->after('vendor_reply');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('order_items')) {
            Schema::table('order_items', function (Blueprint $table) {
                $cols = array_intersect(['special_instructions', 'addon_amount'], Schema::getColumnListing('order_items'));
                if (!empty($cols)) {
                    $table->dropColumn($cols);
                }
            });
        }

        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                $cols = array_intersect(['packaging_amount', 'pickup_code', 'accepted_at', 'preparing_at', 'ready_at', 'out_for_delivery_at', 'food_fulfillment_stage'], Schema::getColumnListing('orders'));
                if (!empty($cols)) {
                    $table->dropColumn($cols);
                }
            });
        }

        if (Schema::hasTable('cart_items')) {
            Schema::table('cart_items', function (Blueprint $table) {
                $cols = array_intersect(['special_instructions', 'option_selections_json'], Schema::getColumnListing('cart_items'));
                if (!empty($cols)) {
                    $table->dropColumn($cols);
                }
            });
        }

        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                $cols = array_intersect(['food_menu_category_id', 'dietary_label', 'prep_time_mins', 'menu_sort_order', 'is_dish_available', 'available_from', 'available_until'], Schema::getColumnListing('products'));
                if (!empty($cols)) {
                    $table->dropColumn($cols);
                }
            });
        }

        if (Schema::hasTable('product_modifiers')) {
            Schema::table('product_modifiers', function (Blueprint $table) {
                $cols = array_intersect(['option_group_id', 'is_available', 'is_default', 'sort_order'], Schema::getColumnListing('product_modifiers'));
                if (!empty($cols)) {
                    $table->dropColumn($cols);
                }
            });
        }

        Schema::dropIfExists('product_option_groups');
        Schema::dropIfExists('food_menu_categories');

        if (Schema::hasTable('vendor_settings')) {
            Schema::table('vendor_settings', function (Blueprint $table) {
                $cols = array_intersect([
                    'is_accepting_food_orders',
                    'paused_reason',
                    'enforce_operating_hours',
                    'weekly_hours_json',
                    'service_cities_json',
                    'food_packaging_fee',
                    'food_free_delivery_above',
                    'fssai_license_number',
                    'fssai_verified',
                    'fssai_verified_at',
                    'food_tax_percent',
                    'food_avg_rating',
                    'food_review_count',
                ], Schema::getColumnListing('vendor_settings'));
                if (!empty($cols)) {
                    $table->dropColumn($cols);
                }
            });
        }
    }

    private function makeForeignColumnNullable(string $tableName, string $column, string $referencedTable): void
    {
        if (!Schema::hasTable($tableName) || !Schema::hasColumn($tableName, $column)) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            $dbName = Schema::getConnection()->getDatabaseName();
            $fkNames = DB::table('information_schema.KEY_COLUMN_USAGE')
                ->where('TABLE_SCHEMA', $dbName)
                ->where('TABLE_NAME', $tableName)
                ->where('COLUMN_NAME', $column)
                ->whereNotNull('REFERENCED_TABLE_NAME')
                ->pluck('CONSTRAINT_NAME')
                ->unique();

            foreach ($fkNames as $fkName) {
                DB::statement("ALTER TABLE `{$tableName}` DROP FOREIGN KEY `{$fkName}`");
            }

            DB::statement("ALTER TABLE `{$tableName}` MODIFY `{$column}` BIGINT UNSIGNED NULL");

            Schema::table($tableName, function (Blueprint $table) use ($column, $referencedTable) {
                $table->foreign($column)->references('id')->on($referencedTable)->nullOnDelete();
            });

            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($column) {
            $table->unsignedBigInteger($column)->nullable()->change();
        });
    }
};

