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
        Schema::table('products', function (Blueprint $table) {
            $table->integer('weight_grams')->nullable()->after('compare_at_price');
            $table->boolean('is_returnable')->default(true)->after('weight_grams');
            $table->integer('return_window_days')->nullable()->after('is_returnable');
            $table->string('country_of_origin', 50)->default('IN')->after('brand');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['weight_grams', 'is_returnable', 'return_window_days', 'country_of_origin']);
        });
    }
};
