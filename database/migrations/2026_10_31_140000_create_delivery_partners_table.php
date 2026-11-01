<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('delivery_partners')) {
            Schema::create('delivery_partners', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->string('name');
                $table->string('phone')->index();
                $table->string('email')->nullable();
                $table->string('vehicle_type')->default('bike'); // bike, scooter, ev, bicycle
                $table->string('vehicle_number')->nullable();
                $table->string('driving_license')->nullable();
                $table->string('aadhaar_number')->nullable();
                $table->string('city')->default('Makrana');
                $table->string('state')->default('Rajasthan');
                $table->decimal('current_latitude', 10, 7)->nullable();
                $table->decimal('current_longitude', 10, 7)->nullable();
                $table->timestamp('last_active_at')->nullable();
                $table->boolean('is_online')->default(true);
                $table->boolean('is_verified')->default(true);
                $table->string('payout_upi_id')->nullable();
                $table->unsignedInteger('total_deliveries')->default(0);
                $table->decimal('rating', 3, 2)->default(5.00);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('delivery_assignments')) {
            Schema::create('delivery_assignments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('order_id')->constrained()->cascadeOnDelete();
                $table->foreignId('delivery_partner_id')->constrained('delivery_partners')->cascadeOnDelete();
                $table->foreignId('vendor_id')->nullable()->constrained('vendors')->nullOnDelete();
                $table->string('status')->default('assigned'); // assigned, accepted, picked_up, delivered, cancelled
                $table->decimal('pickup_distance_km', 5, 2)->nullable();
                $table->decimal('payout_amount', 8, 2)->default(45.00);
                $table->timestamp('assigned_at')->nullable();
                $table->timestamp('accepted_at')->nullable();
                $table->timestamp('picked_up_at')->nullable();
                $table->timestamp('delivered_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_assignments');
        Schema::dropIfExists('delivery_partners');
    }
};

