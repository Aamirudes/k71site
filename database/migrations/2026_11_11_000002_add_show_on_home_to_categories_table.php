<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('categories', 'show_on_home')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->boolean('show_on_home')->default(true)->after('is_active');
                $table->index(['parent_id', 'show_on_home', 'is_active', 'sort_order'], 'categories_home_ribbon_idx');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('categories', 'show_on_home')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->dropIndex('categories_home_ribbon_idx');
                $table->dropColumn('show_on_home');
            });
        }
    }
};

