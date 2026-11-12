<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('vendors', 'can_use_kinetic_scroll')) {
            Schema::table('vendors', function (Blueprint $table) {
                $table->boolean('can_use_kinetic_scroll')->default(false)->after('layout_preset');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('vendors', 'can_use_kinetic_scroll')) {
            Schema::table('vendors', function (Blueprint $table) {
                $table->dropColumn('can_use_kinetic_scroll');
            });
        }
    }
};

