<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations for Universal Dynamic Variant, Attribute & UX Engine.
     */
    public function up(): void
    {
        // 1. Classify Category Vertical Types
        if (!Schema::hasColumn('categories', 'vertical_type')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->string('vertical_type', 32)->default('standard_retail')->after('name')->index();
            });
        }

        // 2. Dynamic Attribute Blueprints
        if (!Schema::hasTable('category_attributes')) {
            Schema::create('category_attributes', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedBigInteger('category_id');
                $table->string('attribute_code', 64);
                $table->string('label', 128);
                $table->string('input_type', 32)->default('text'); // text, number, select, multiselect, color_swatch, image_swatch, boolean, radio_pill
                $table->boolean('is_variant_driver')->default(false); // 1 generates SKU/price combinations; 0 is pure static spec
                $table->boolean('is_filterable')->default(false);
                $table->boolean('is_required')->default(false);
                $table->json('options_json')->nullable(); // e.g., ["S", "M", "L", "XL"] or [{"name": "Red", "hex": "#FF0000"}]
                $table->unsignedSmallInteger('sort_order')->default(0);
                $table->timestamps();

                $table->index('category_id', 'idx_category_attributes_category');
                $table->index('attribute_code', 'idx_category_attributes_code');
            });
        }

        // 3. Base Products Table Updates
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'has_variants')) {
                $table->boolean('has_variants')->default(false)->after('base_price');
            }
            if (!Schema::hasColumn('products', 'specifications_json')) {
                $table->json('specifications_json')->nullable()->after('has_variants');
            }
            if (!Schema::hasColumn('products', 'b2b_tiers_json')) {
                $table->json('b2b_tiers_json')->nullable()->after('specifications_json');
            }
            if (!Schema::hasColumn('products', 'service_config_json')) {
                $table->json('service_config_json')->nullable()->after('b2b_tiers_json');
            }
        });

        // 4. Variant Combination Matrix (Child SKUs) updates to product_variants
        Schema::table('product_variants', function (Blueprint $table) {
            if (!Schema::hasColumn('product_variants', 'attributes_hash')) {
                $table->json('attributes_hash')->nullable()->after('sku');
            }
            if (!Schema::hasColumn('product_variants', 'price')) {
                $table->decimal('price', 12, 2)->nullable()->after('attributes_hash');
            }
            if (!Schema::hasColumn('product_variants', 'compare_at_price')) {
                $table->decimal('compare_at_price', 12, 2)->nullable()->after('price');
            }
            if (!Schema::hasColumn('product_variants', 'stock_quantity')) {
                $table->integer('stock_quantity')->default(0)->after('compare_at_price');
            }
            if (!Schema::hasColumn('product_variants', 'image_url')) {
                $table->string('image_url', 255)->nullable()->after('stock_quantity');
            }
            if (!Schema::hasColumn('product_variants', 'status')) {
                $table->string('status', 20)->default('active')->after('image_url');
            }
        });

        // 5. Product Modifiers & Add-ons (Food Portions & Customizations)
        if (!Schema::hasTable('product_modifiers')) {
            Schema::create('product_modifiers', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedBigInteger('product_id');
                $table->string('group_name', 100); // e.g., "Add-ons", "Portion Upgrades"
                $table->string('modifier_name', 100); // e.g., "Extra Bhatura", "Special Chutney"
                $table->decimal('price_delta', 10, 2)->default(0.00);
                $table->boolean('is_required')->default(false);
                $table->timestamps();

                $table->index('product_id', 'idx_product_modifiers_product');
            });
        }

        // 6. Tiered Service / Freelance Packages (Fiverr Model)
        if (!Schema::hasTable('product_gig_packages')) {
            Schema::create('product_gig_packages', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedBigInteger('product_id');
                $table->string('tier', 20); // 'basic', 'standard', 'premium'
                $table->string('title', 150);
                $table->text('description');
                $table->unsignedSmallInteger('delivery_days');
                $table->integer('revisions')->default(0);
                $table->decimal('price', 12, 2);
                $table->json('features_json'); // e.g., {"source_file": true, "commercial_rights": true}
                $table->timestamps();

                $table->unique(['product_id', 'tier'], 'idx_product_tier');
            });
        }

        // 7. Universal Cart Table Upgrades
        Schema::table('cart_items', function (Blueprint $table) {
            if (!Schema::hasColumn('cart_items', 'gig_package_id')) {
                $table->unsignedInteger('gig_package_id')->nullable()->after('variant_id');
            }
            if (!Schema::hasColumn('cart_items', 'selected_modifiers_json')) {
                $table->json('selected_modifiers_json')->nullable()->after('gig_package_id');
            }
            if (!Schema::hasColumn('cart_items', 'booking_date_time')) {
                $table->dateTime('booking_date_time')->nullable()->after('selected_modifiers_json');
            }
        });

        // 8. Universal Order Items Upgrades (frozen snapshot of selected attributes / modifiers)
        Schema::table('order_items', function (Blueprint $table) {
            if (!Schema::hasColumn('order_items', 'meta_json')) {
                $table->json('meta_json')->nullable()->after('snapshot');
            }
            if (!Schema::hasColumn('order_items', 'gig_package_id')) {
                $table->unsignedInteger('gig_package_id')->nullable()->after('meta_json');
            }
            if (!Schema::hasColumn('order_items', 'selected_modifiers_json')) {
                $table->json('selected_modifiers_json')->nullable()->after('gig_package_id');
            }
            if (!Schema::hasColumn('order_items', 'booking_date_time')) {
                $table->dateTime('booking_date_time')->nullable()->after('selected_modifiers_json');
            }
        });

        // 9. Sync existing product variants (fill stock_quantity and price if empty)
        DB::table('product_variants')->whereNull('price')->whereNotNull('price_override')->update([
            'price' => DB::raw('price_override / 100.0'),
        ]);
        DB::table('product_variants')->update([
            'stock_quantity' => DB::raw('stock'),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['meta_json', 'gig_package_id', 'selected_modifiers_json', 'booking_date_time']);
        });

        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropColumn(['gig_package_id', 'selected_modifiers_json', 'booking_date_time']);
        });

        Schema::dropIfExists('product_gig_packages');
        Schema::dropIfExists('product_modifiers');

        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropColumn(['attributes_hash', 'price', 'compare_at_price', 'stock_quantity', 'image_url', 'status']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['has_variants', 'specifications_json', 'b2b_tiers_json', 'service_config_json']);
        });

        Schema::dropIfExists('category_attributes');

        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('vertical_type');
        });
    }
};
