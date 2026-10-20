<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('slug', 30)->unique(); // The subdomain
            $table->string('shop_name', 100);
            $table->text('description')->nullable();
            $table->string('logo', 500)->nullable();
            $table->string('banner', 500)->nullable();
            $table->string('contact_email', 255)->nullable();
            $table->string('contact_phone', 15)->nullable();
            $table->text('pickup_address')->nullable(); // JSON
            $table->text('return_address')->nullable(); // JSON
            $table->string('gstin', 15)->nullable();
            $table->json('social_links')->nullable();
            $table->text('store_policies')->nullable();
            $table->string('seo_title', 70)->nullable();
            $table->string('seo_description', 160)->nullable();
            $table->string('og_image', 500)->nullable();
            $table->string('colour_palette', 20)->default('default');
            $table->string('layout_preset', 20)->default('classic');
            $table->string('status', 20)->default('pending'); // pending, approved, suspended, closed
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('rejection_reason', 500)->nullable();
            $table->boolean('vacation_mode')->default(false);
            $table->string('vacation_message', 500)->nullable();
            $table->timestamp('slug_reserved_at')->nullable();
            $table->timestamp('slug_released_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique('user_id'); // One store per user
            $table->index('status');
            $table->index('created_at');
        });

        Schema::create('vendor_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->string('shipping_type', 20)->default('flat'); // flat, free_above, weight_based
            $table->integer('shipping_flat_rate')->default(0); // in paise
            $table->integer('shipping_free_above')->nullable(); // in paise
            $table->json('serviceable_pincodes')->nullable();
            $table->unsignedTinyInteger('processing_days')->default(3);
            $table->unsignedTinyInteger('return_window_days')->default(7);
            $table->boolean('cod_enabled')->default(false);
            $table->string('upi_id', 100)->nullable();
            $table->string('upi_payee_name', 100)->nullable();
            $table->json('upi_apps')->default(json_encode(['phonepe', 'googlepay', 'paytm', 'generic']));
            $table->string('upi_qr_image', 500)->nullable();
            $table->timestamp('upi_id_changed_at')->nullable();
            $table->string('upi_id_previous', 100)->nullable();
            $table->integer('low_stock_threshold')->default(5);
            $table->boolean('allow_backorder')->default(false);
            $table->timestamps();

            $table->unique('vendor_id');
        });

        Schema::create('reserved_slugs', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 30)->unique();
            $table->string('reason', 100);
            $table->timestamp('released_at')->nullable();
            $table->timestamps();
            
            $table->index('released_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reserved_slugs');
        Schema::dropIfExists('vendor_settings');
        Schema::dropIfExists('vendors');
    }
};
