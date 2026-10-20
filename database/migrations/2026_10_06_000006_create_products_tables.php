<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->string('title', 200);
            $table->string('slug', 220)->unique();
            $table->text('description');
            $table->string('short_description', 500)->nullable();
            $table->string('brand', 100)->nullable();
            $table->string('gender', 20)->nullable();
            $table->string('age_group', 20)->nullable();
            $table->string('material', 200)->nullable();
            $table->text('care_instructions')->nullable();
            $table->json('tags')->nullable();
            $table->integer('base_price'); // paise
            $table->integer('compare_at_price')->nullable(); // MRP paise
            $table->decimal('gst_rate', 5, 2);
            $table->string('hsn_code', 8)->nullable();
            $table->string('sku', 50)->nullable();
            $table->string('status', 20)->default('draft');
            $table->boolean('is_featured')->default(false);
            $table->integer('total_stock')->default(0);
            $table->string('seo_title', 70)->nullable();
            $table->string('seo_description', 160)->nullable();
            $table->string('og_image', 500)->nullable();
            $table->unsignedInteger('view_count')->default(0);
            $table->unsignedInteger('order_count')->default(0);
            $table->decimal('avg_rating', 3, 2)->nullable();
            $table->unsignedInteger('review_count')->default(0);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('vendor_id');
            $table->index('category_id');
            $table->index('status');
            $table->index(['status', 'vendor_id']);
            $table->index('is_featured');
            $table->index('created_at');
            $table->index('base_price');
            $table->index('order_count');
            $table->index('avg_rating');
        });

        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('sku', 50)->nullable();
            $table->integer('price_override')->nullable(); // paise
            $table->integer('stock')->default(0);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index('sku');
            $table->index(['product_id', 'is_active']);
        });

        Schema::create('product_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
            $table->string('path', 500);
            $table->json('paths_responsive')->nullable();
            $table->string('alt_text', 200);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index('variant_id');
            $table->index('sort_order');
        });

        Schema::create('product_variant_attributes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('variant_id')->constrained('product_variants')->cascadeOnDelete();
            $table->foreignId('attribute_id')->constrained()->cascadeOnDelete();
            $table->foreignId('attribute_value_id')->constrained()->cascadeOnDelete();
            
            $table->unique(['variant_id', 'attribute_id']);
        });

        Schema::create('size_charts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('title', 100);
            $table->string('image', 500)->nullable();
            $table->json('table_data')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('size_charts');
        Schema::dropIfExists('product_variant_attributes');
        Schema::dropIfExists('product_images');
        Schema::dropIfExists('product_variants');
        Schema::dropIfExists('products');
    }
};
