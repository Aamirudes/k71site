<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);
            $table->string('slug', 50)->unique();
            $table->text('description')->nullable();
            $table->integer('price_monthly'); // paise
            $table->integer('price_yearly')->nullable(); // paise
            $table->integer('max_products')->nullable();
            $table->integer('max_images_per_product')->default(10);
            $table->json('features')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->integer('trial_days')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('vendor_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained('subscription_plans')->restrictOnDelete();
            $table->string('status', 20); // trial, active, expired, paused, cancelled
            $table->timestamp('starts_at');
            $table->timestamp('ends_at');
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('grace_ends_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('ends_at');
        });

        Schema::create('subscription_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subscription_id')->constrained('vendor_subscriptions')->cascadeOnDelete();
            $table->string('invoice_number', 30)->unique();
            $table->integer('amount'); // paise
            $table->integer('tax_amount'); // paise
            $table->integer('total_amount'); // paise
            $table->string('status', 20); // pending, paid, overdue
            $table->date('due_date');
            $table->timestamp('paid_at')->nullable();
            $table->string('payment_reference', 100)->nullable();
            $table->string('pdf_path', 500)->nullable();
            $table->timestamps();

            $table->index('status');
        });

        Schema::create('commission_ledger', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('order_id')->nullable(); // Can't constrain yet, orders not created
            $table->string('type', 20); // commission, adjustment, payment
            $table->integer('amount'); // paise
            $table->integer('balance');
            $table->string('description', 500);
            $table->string('period_month', 7)->nullable(); // YYYY-MM
            $table->timestamps();

            $table->index('period_month');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commission_ledger');
        Schema::dropIfExists('subscription_invoices');
        Schema::dropIfExists('vendor_subscriptions');
        Schema::dropIfExists('subscription_plans');
    }
};
