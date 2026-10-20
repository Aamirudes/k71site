<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('auditable_type', 100);
            $table->unsignedBigInteger('auditable_id');
            $table->string('action', 30);
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->timestamps();

            $table->index(['auditable_type', 'auditable_id']);
            $table->index('action');
        });

        Schema::create('otp_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('email', 255);
            $table->string('code_hash', 255);
            $table->string('purpose', 30);
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->timestamp('locked_until')->nullable();
            $table->timestamp('expires_at');
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            $table->index(['email', 'purpose']);
            $table->index('expires_at');
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('group', 50);
            $table->string('key', 100)->unique();
            $table->text('value')->nullable();
            $table->string('type', 20); // string, boolean, integer, json
            $table->timestamps();

            $table->index('group');
        });

        Schema::create('seo_redirects', function (Blueprint $table) {
            $table->id();
            $table->string('from_path', 500)->unique();
            $table->string('to_path', 500);
            $table->smallInteger('status_code')->default(301);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('disputes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reporter_id')->constrained('users')->cascadeOnDelete();
            $table->string('reporter_type', 20); // customer, vendor
            $table->string('type', 30);
            $table->string('subject', 200);
            $table->text('description');
            $table->json('evidence')->nullable();
            $table->string('related_type', 100)->nullable();
            $table->unsignedBigInteger('related_id')->nullable();
            $table->string('status', 20)->default('open');
            $table->text('resolution')->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index(['related_type', 'related_id']);
        });

        Schema::create('pincodes', function (Blueprint $table) {
            $table->id();
            $table->string('pincode', 6)->index();
            $table->string('city', 100);
            $table->string('state', 50);
            $table->string('state_code', 2);
        });

        Schema::create('product_collections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->string('name', 100);
            $table->string('slug', 120);
            $table->text('description')->nullable();
            $table->string('image', 500)->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['vendor_id', 'slug']);
        });

        Schema::create('collection_product', function (Blueprint $table) {
            $table->foreignId('collection_id')->constrained('product_collections')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->integer('sort_order')->default(0);

            $table->unique(['collection_id', 'product_id']);
        });

        Schema::create('vendor_customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('order_count')->default(0);
            $table->integer('total_spent')->default(0);
            $table->timestamp('first_order_at')->nullable();
            $table->timestamp('last_order_at')->nullable();
            $table->timestamps();

            $table->unique(['vendor_id', 'user_id']);
        });

        Schema::create('email_templates', function (Blueprint $table) {
            $table->id();
            $table->string('key', 50)->unique();
            $table->string('subject', 200);
            $table->longText('body_html');
            $table->text('body_text');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('failed_login_attempts', function (Blueprint $table) {
            $table->id();
            $table->string('email', 255);
            $table->string('ip_address', 45);
            $table->string('user_agent', 500)->nullable();
            $table->timestamps();

            $table->index('email');
            $table->index('ip_address');
        });

        Schema::create('user_consents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type', 30);
            $table->string('version', 10);
            $table->timestamp('accepted_at');
            $table->string('ip_address', 45);

            $table->unique(['user_id', 'type']);
        });

        Schema::create('stock_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('variant_id')->nullable()->constrained('product_variants')->cascadeOnDelete();
            $table->string('email', 255);
            $table->timestamp('notified_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'variant_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_notifications');
        Schema::dropIfExists('user_consents');
        Schema::dropIfExists('failed_login_attempts');
        Schema::dropIfExists('email_templates');
        Schema::dropIfExists('vendor_customers');
        Schema::dropIfExists('collection_product');
        Schema::dropIfExists('product_collections');
        Schema::dropIfExists('pincodes');
        Schema::dropIfExists('disputes');
        Schema::dropIfExists('seo_redirects');
        Schema::dropIfExists('settings');
        Schema::dropIfExists('otp_codes');
        Schema::dropIfExists('audit_logs');
    }
};
