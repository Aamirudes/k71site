<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('delivery_partners', function (Blueprint $table) {
            if (!Schema::hasColumn('delivery_partners', 'base_delivery_fee')) {
                $table->decimal('base_delivery_fee', 8, 2)->default(30.00)->after('rating');
            }
            if (!Schema::hasColumn('delivery_partners', 'per_km_rate')) {
                $table->decimal('per_km_rate', 8, 2)->default(15.00)->after('base_delivery_fee');
            }
            if (!Schema::hasColumn('delivery_partners', 'max_delivery_radius_km')) {
                $table->decimal('max_delivery_radius_km', 5, 2)->default(15.00)->after('per_km_rate');
            }
        });
    }

    public function down(): void
    {
        Schema::table('delivery_partners', function (Blueprint $table) {
            $table->dropColumn(['base_delivery_fee', 'per_km_rate', 'max_delivery_radius_km']);
        });
    }
};

