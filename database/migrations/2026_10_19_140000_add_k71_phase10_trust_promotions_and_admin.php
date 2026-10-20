<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run migrations for K71 Phase 10: Trust, Moderation, Reviews, Promotions, and Admin Control.
     */
    public function up(): void
    {
        // 1. Add vendor_reply and vendor_replied_at to reviews table
        if (Schema::hasTable('reviews')) {
            Schema::table('reviews', function (Blueprint $table) {
                if (!Schema::hasColumn('reviews', 'vendor_reply')) {
                    $table->text('vendor_reply')->nullable()->after('is_approved');
                }
                if (!Schema::hasColumn('reviews', 'vendor_replied_at')) {
                    $table->timestamp('vendor_replied_at')->nullable()->after('vendor_reply');
                }
            });
        }

        // 2. Create reports table for user reports & trust moderation
        if (!Schema::hasTable('reports')) {
            Schema::create('reports', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('reportable_type', 100);
                $table->unsignedBigInteger('reportable_id');
                $table->string('reason', 50); // fake_item, abusive, spam, inappropriate, copyright, other
                $table->text('details')->nullable();
                $table->string('status', 20)->default('pending')->index(); // pending, dismissed, action_taken
                $table->text('admin_notes')->nullable();
                $table->string('action_taken', 50)->nullable(); // blocked_product, warned_vendor, deleted_review, dismissed
                $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('resolved_at')->nullable();
                $table->timestamps();

                $table->index(['reportable_type', 'reportable_id']);
            });
        }

        // 3. Create promotions table
        if (!Schema::hasTable('promotions')) {
            Schema::create('promotions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('vendor_id')->nullable()->constrained('vendors')->nullOnDelete(); // null = marketplace-wide
                $table->string('title', 120);
                $table->string('slug', 150)->unique();
                $table->string('badge', 30)->nullable(); // e.g. "FLASHSALE", "DIWALI50"
                $table->string('discount_type', 20)->default('percentage'); // percentage, flat
                $table->integer('discount_value'); // percentage number or paise
                $table->integer('min_spend')->nullable(); // paise
                $table->timestamp('starts_at');
                $table->timestamp('ends_at');
                $table->boolean('is_active')->default(true)->index();
                $table->string('banner_image', 500)->nullable();
                $table->timestamps();

                $table->index(['is_active', 'starts_at', 'ends_at']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promotions');
        Schema::dropIfExists('reports');

        if (Schema::hasTable('reviews')) {
            Schema::table('reviews', function (Blueprint $table) {
                $columns = ['vendor_reply', 'vendor_replied_at'];
                $table->dropColumn(array_intersect($columns, Schema::getColumnListing('reviews')));
            });
        }
    }
};
