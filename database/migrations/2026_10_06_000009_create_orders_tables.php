<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number', 20)->unique();
            $table->foreignId('checkout_group_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('vendor_id')->constrained()->restrictOnDelete();
            $table->json('address_snapshot');
            $table->integer('subtotal');
            $table->integer('discount_amount')->default(0);
            $table->string('coupon_code', 30)->nullable();
            $table->integer('shipping_amount');
            $table->integer('tax_amount');
            $table->integer('cgst_amount')->default(0);
            $table->integer('sgst_amount')->default(0);
            $table->integer('igst_amount')->default(0);
            $table->integer('total_amount');
            $table->tinyInteger('item_count');
            $table->string('status', 30);
            $table->string('payment_method', 20);
            $table->string('payment_status', 20);
            $table->string('vendor_upi_id_at_order', 100)->nullable();
            $table->string('tracking_number', 100)->nullable();
            $table->string('tracking_url', 500)->nullable();
            $table->string('courier_name', 100)->nullable();
            $table->text('notes')->nullable();
            $table->text('customer_notes')->nullable();
            $table->timestamp('packed_at')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->string('cancelled_by', 20)->nullable();
            $table->string('cancel_reason', 500)->nullable();
            $table->string('invoice_number', 30)->nullable();
            $table->timestamp('invoice_generated_at')->nullable();
            $table->timestamp('payment_deadline')->nullable();
            $table->timestamp('verification_deadline')->nullable();
            $table->string('idempotency_key', 64)->nullable()->unique();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['vendor_id', 'status']);
            $table->index('payment_status');
            $table->index('created_at');
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
            $table->unsignedInteger('quantity');
            $table->integer('unit_price');
            $table->integer('total_price');
            $table->integer('tax_amount');
            $table->decimal('gst_rate', 5, 2);
            $table->string('hsn_code', 8)->nullable();
            $table->json('snapshot');
            $table->timestamps();
        });

        Schema::create('order_status_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('from_status', 30)->nullable();
            $table->string('to_status', 30);
            $table->string('actor_type', 20); // customer, vendor, admin, system
            $table->unsignedBigInteger('actor_id')->nullable();
            $table->string('note', 500)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
        });

        Schema::create('stock_reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('variant_id')->constrained('product_variants')->cascadeOnDelete();
            $table->unsignedInteger('quantity');
            $table->timestamp('reserved_at');
            $table->timestamp('expires_at');
            $table->timestamp('released_at')->nullable();
            $table->timestamps();

            $table->index('expires_at');
        });

        Schema::create('payment_claims', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('utr_number', 30)->nullable()->unique();
            $table->string('screenshot', 500)->nullable();
            $table->integer('amount_claimed');
            $table->string('status', 20); // submitted, verified, rejected
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->string('rejection_reason', 500)->nullable();
            $table->boolean('admin_override')->default(false);
            $table->timestamps();

            $table->unique('order_id');
            $table->index('status');
        });

        Schema::create('return_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('reason', 500);
            $table->json('photos')->nullable();
            $table->string('status', 20); // requested, approved, rejected, completed, refunded
            $table->string('refund_utr', 30)->nullable();
            $table->integer('refund_amount')->nullable();
            $table->string('vendor_response', 500)->nullable();
            $table->string('admin_note', 500)->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('return_requests');
        Schema::dropIfExists('payment_claims');
        Schema::dropIfExists('stock_reservations');
        Schema::dropIfExists('order_status_logs');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
