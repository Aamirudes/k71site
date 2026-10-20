<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Add upi_enabled to vendor_settings
        if (Schema::hasTable('vendor_settings')) {
            Schema::table('vendor_settings', function (Blueprint $table) {
                if (!Schema::hasColumn('vendor_settings', 'upi_enabled')) {
                    $table->boolean('upi_enabled')->default(true)->after('cod_enabled');
                }
            });

            // Backfill: existing vendors have UPI enabled
            DB::table('vendor_settings')->update(['upi_enabled' => true]);
        }

        // 2. Add product_preset to vendors table
        if (Schema::hasTable('vendors')) {
            Schema::table('vendors', function (Blueprint $table) {
                if (!Schema::hasColumn('vendors', 'product_preset')) {
                    $table->string('product_preset', 30)->default('gallery')->after('layout_preset');
                }
            });
        }

        // 3. Create store_presets table
        if (!Schema::hasTable('store_presets')) {
            Schema::create('store_presets', function (Blueprint $table) {
                $table->id();
                $table->string('type', 30); // store_layout, colour_preset, product_layout
                $table->string('key', 50)->unique();
                $table->string('name', 100);
                $table->text('description')->nullable();
                $table->json('tokens')->nullable();
                $table->boolean('is_active')->default(true);
                $table->boolean('is_default')->default(false);
                $table->timestamps();

                $table->index(['type', 'is_active']);
            });

            // Seed default 3x3x3 presets
            $now = now();
            $presets = [
                // 3 Store Layouts
                [
                    'type' => 'store_layout',
                    'key' => 'classic',
                    'name' => 'Classic Marketplace',
                    'description' => 'Hero banner, featured category pills, and high-density product grid.',
                    'tokens' => json_encode(['grid_cols' => 4, 'banner_aspect' => 'wide', 'show_filters' => true]),
                    'is_active' => true,
                    'is_default' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'type' => 'store_layout',
                    'key' => 'editorial',
                    'name' => 'Editorial Boutique',
                    'description' => 'Story-driven layout with large typography, curated lookbooks, and atelier heritage.',
                    'tokens' => json_encode(['grid_cols' => 3, 'banner_aspect' => 'tall', 'show_story' => true]),
                    'is_active' => true,
                    'is_default' => false,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'type' => 'store_layout',
                    'key' => 'minimal',
                    'name' => 'Modern Minimalist',
                    'description' => 'Clean whitespace, borderless product cards, and sleek focus on product photography.',
                    'tokens' => json_encode(['grid_cols' => 4, 'banner_aspect' => 'compact', 'borderless' => true]),
                    'is_active' => true,
                    'is_default' => false,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],

                // 3 Colour Presets
                [
                    'type' => 'colour_preset',
                    'key' => 'sage',
                    'name' => 'Earthy Sage',
                    'description' => 'Calm natural sage tones (#2D4A3E), ivory surfaces, and refined botanical balance.',
                    'tokens' => json_encode(['primary' => '#2D4A3E', 'accent' => '#D8E2DC', 'surface' => '#F8FAF8', 'text' => '#1F2A26']),
                    'is_active' => true,
                    'is_default' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'type' => 'colour_preset',
                    'key' => 'midnight',
                    'name' => 'Midnight Indigo',
                    'description' => 'Modern tech-luxury indigo (#1E293B) with cyan highlights and sharp contrast.',
                    'tokens' => json_encode(['primary' => '#1E293B', 'accent' => '#38BDF8', 'surface' => '#F8FAFC', 'text' => '#0F172A']),
                    'is_active' => true,
                    'is_default' => false,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'type' => 'colour_preset',
                    'key' => 'sunset',
                    'name' => 'Warm Terracotta / Sunset',
                    'description' => 'Warm Mediterranean terracotta (#C85A32) with gold accents and artisanal warmth.',
                    'tokens' => json_encode(['primary' => '#C85A32', 'accent' => '#FDE047', 'surface' => '#FFFDF9', 'text' => '#451A03']),
                    'is_active' => true,
                    'is_default' => false,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],

                // 3 Product Page Layouts
                [
                    'type' => 'product_layout',
                    'key' => 'gallery',
                    'name' => 'Thumbnails & Gallery',
                    'description' => 'Interactive carousel with thumbnail strip, variant pills, and quick cart drawer.',
                    'tokens' => json_encode(['gallery_type' => 'thumbnails', 'sticky_cta' => true]),
                    'is_active' => true,
                    'is_default' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'type' => 'product_layout',
                    'key' => 'immersive',
                    'name' => 'Full-bleed Immersive',
                    'description' => 'Edge-to-edge media cards with floating sticky checkout bar and rich narrative.',
                    'tokens' => json_encode(['gallery_type' => 'fullbleed', 'sticky_cta' => true, 'floating_bar' => true]),
                    'is_active' => true,
                    'is_default' => false,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'type' => 'product_layout',
                    'key' => 'showcase',
                    'name' => 'Specification Showcase',
                    'description' => 'Split-screen layout emphasizing technical specs, fabric care, dimensions, and trust seals.',
                    'tokens' => json_encode(['gallery_type' => 'split', 'specs_accordion' => true, 'trust_badges' => true]),
                    'is_active' => true,
                    'is_default' => false,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            ];

            DB::table('store_presets')->insert($presets);
        }

        // 4. Seed COD marketplace settings
        if (Schema::hasTable('settings')) {
            $codSettings = [
                ['group' => 'payments', 'key' => 'cod_marketplace_enabled', 'value' => '1', 'type' => 'boolean'],
                ['group' => 'payments', 'key' => 'cod_max_amount', 'value' => '500000', 'type' => 'integer'],
                ['group' => 'payments', 'key' => 'cod_fee', 'value' => '0', 'type' => 'integer'],
            ];

            foreach ($codSettings as $setting) {
                DB::table('settings')->updateOrInsert(
                    ['group' => $setting['group'], 'key' => $setting['key']],
                    ['value' => $setting['value'], 'type' => $setting['type'], 'updated_at' => now(), 'created_at' => now()]
                );
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('vendor_settings')) {
            Schema::table('vendor_settings', function (Blueprint $table) {
                if (Schema::hasColumn('vendor_settings', 'upi_enabled')) {
                    $table->dropColumn('upi_enabled');
                }
            });
        }

        if (Schema::hasTable('vendors')) {
            Schema::table('vendors', function (Blueprint $table) {
                if (Schema::hasColumn('vendors', 'product_preset')) {
                    $table->dropColumn('product_preset');
                }
            });
        }

        Schema::dropIfExists('store_presets');
    }
};
