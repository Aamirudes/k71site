<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->bigInteger('base_price')->change();
            $table->bigInteger('compare_at_price')->nullable()->change();
            $table->string('sku', 191)->nullable()->change();
        });

        Schema::table('product_variants', function (Blueprint $table) {
            $table->bigInteger('price_override')->nullable()->change();
            $table->string('sku', 191)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->integer('base_price')->change();
            $table->integer('compare_at_price')->nullable()->change();
            $table->string('sku', 50)->nullable()->change();
        });

        Schema::table('product_variants', function (Blueprint $table) {
            $table->integer('price_override')->nullable()->change();
            $table->string('sku', 50)->nullable()->change();
        });
    }
};
