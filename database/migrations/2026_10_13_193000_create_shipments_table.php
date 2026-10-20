<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();
            
            // Shiprocket identifiers
            $table->string('provider', 50)->default('shiprocket'); // shiprocket, manual
            $table->string('shiprocket_order_id', 100)->nullable();
            $table->string('shiprocket_shipment_id', 100)->nullable();
            $table->string('awb_code', 100)->nullable()->index();
            $table->unsignedBigInteger('courier_company_id')->nullable();
            $table->string('courier_name', 100)->nullable();
            
            // Status lifecycle
            $table->string('status', 50)->default('created'); // created, pickup_scheduled, in_transit, out_for_delivery, delivered, rto, cancelled
            
            // Pickup details
            $table->string('pickup_location', 100)->nullable();
            $table->string('pickup_token', 100)->nullable();
            $table->timestamp('pickup_scheduled_at')->nullable();
            
            // URLs
            $table->string('label_url', 1000)->nullable();
            $table->string('manifest_url', 1000)->nullable();
            $table->string('tracking_url', 1000)->nullable();
            
            // Financials & Margins
            $table->decimal('package_weight_kg', 8, 3)->default(0.500);
            $table->integer('actual_cost_paise')->default(0); // Cost charged by courier
            $table->integer('customer_shipping_paise')->default(0); // Shipping fee collected from customer
            $table->integer('platform_margin_paise')->default(0); // Platform profit earned
            
            // Extra metadata
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->index(['order_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipments');
    }
};
