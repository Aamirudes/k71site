<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('storefront_themes')) {
            Schema::table('storefront_themes', function (Blueprint $table) {
                if (!Schema::hasColumn('storefront_themes', 'is_featured')) {
                    $table->boolean('is_featured')->default(false)->after('is_available_for_request');
                }
                if (!Schema::hasColumn('storefront_themes', 'is_preview_enabled')) {
                    $table->boolean('is_preview_enabled')->default(true)->after('is_featured');
                }
                if (!Schema::hasColumn('storefront_themes', 'qa_verified')) {
                    $table->boolean('qa_verified')->default(true)->after('implementation_status');
                }
                if (!Schema::hasColumn('storefront_themes', 'compatible_categories')) {
                    $table->json('compatible_categories')->nullable()->after('category');
                }
                if (!Schema::hasColumn('storefront_themes', 'design_attributes')) {
                    $table->json('design_attributes')->nullable()->after('compatible_categories');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('storefront_themes')) {
            Schema::table('storefront_themes', function (Blueprint $table) {
                foreach (['design_attributes', 'compatible_categories', 'qa_verified', 'is_preview_enabled', 'is_featured'] as $col) {
                    if (Schema::hasColumn('storefront_themes', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};
