<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('storefront_themes')) {
            Schema::create('storefront_themes', function (Blueprint $table) {
                $table->id();
                $table->string('theme_id', 80)->unique();
                $table->string('slug', 80)->unique();
                $table->string('name', 120);
                $table->string('category', 60)->default('general');
                $table->string('tagline', 220)->nullable();
                $table->text('description')->nullable();
                $table->string('version', 20)->default('1.0.0');
                $table->boolean('is_legacy')->default(false);
                $table->boolean('is_free')->default(false);
                $table->string('implementation_status', 30)->default('ready'); // ready, qa_pending, draft, deprecated
                $table->boolean('is_visible_in_catalogue')->default(true);
                $table->boolean('is_available_for_request')->default(true);
                $table->unsignedBigInteger('price_paise')->nullable(); // Admin-controlled individual price
                $table->string('currency', 10)->default('INR');
                $table->string('preview_thumbnail', 255)->nullable();
                $table->unsignedInteger('sort_order')->default(0);
                $table->json('manifest_overrides')->nullable();
                $table->timestamps();

                $table->index(['is_visible_in_catalogue', 'implementation_status'], 'sf_themes_vis_status_idx');
                $table->index(['category', 'sort_order'], 'sf_themes_cat_sort_idx');
            });
        }

        if (!Schema::hasTable('vendor_theme_requests')) {
            Schema::create('vendor_theme_requests', function (Blueprint $table) {
                $table->id();
                $table->string('reference_code', 40)->unique();
                $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();
                $table->string('theme_id', 80);
                $table->unsignedBigInteger('price_snapshot_paise')->nullable();
                $table->string('currency', 10)->default('INR');
                $table->string('status', 40)->default('requested');
                $table->string('payment_verification_status', 40)->default('unverified');
                $table->foreignId('payment_verified_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('payment_verified_at')->nullable();
                $table->string('payment_reference', 120)->nullable();
                $table->text('payment_notes')->nullable();
                $table->text('vendor_note')->nullable();
                $table->text('admin_notes')->nullable();
                $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('reviewed_at')->nullable();
                $table->json('status_history')->nullable();
                $table->timestamps();

                $table->index(['vendor_id', 'theme_id', 'status'], 'vtr_vendor_theme_status_idx');
                $table->index(['status', 'created_at'], 'vtr_status_created_idx');
            });
        }

        if (!Schema::hasTable('vendor_theme_entitlements')) {
            Schema::create('vendor_theme_entitlements', function (Blueprint $table) {
                $table->id();
                $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();
                $table->string('theme_id', 80);
                $table->string('status', 20)->default('granted'); // granted, revoked
                $table->foreignId('request_id')->nullable()->constrained('vendor_theme_requests')->nullOnDelete();
                $table->foreignId('granted_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('granted_at')->nullable();
                $table->foreignId('revoked_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('revoked_at')->nullable();
                $table->text('revocation_reason')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->unique(['vendor_id', 'theme_id'], 'vte_vendor_theme_unique');
                $table->index(['theme_id', 'status'], 'vte_theme_status_idx');
            });
        }

        if (!Schema::hasTable('vendor_theme_drafts')) {
            Schema::create('vendor_theme_drafts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();
                $table->string('theme_id', 80);
                $table->string('theme_version', 20)->default('1.0.0');
                $table->json('config_data')->nullable();
                $table->timestamps();

                $table->unique(['vendor_id', 'theme_id'], 'vtd_vendor_theme_unique');
            });
        }

        if (!Schema::hasTable('theme_audit_logs')) {
            Schema::create('theme_audit_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('actor_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('actor_role', 30)->nullable();
                $table->string('action', 80);
                $table->unsignedBigInteger('vendor_id')->nullable();
                $table->string('theme_id', 80)->nullable();
                $table->unsignedBigInteger('request_id')->nullable();
                $table->unsignedBigInteger('entitlement_id')->nullable();
                $table->json('before_state')->nullable();
                $table->json('after_state')->nullable();
                $table->text('notes')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->timestamps();

                $table->index(['action', 'created_at'], 'tal_action_created_idx');
                $table->index(['vendor_id', 'theme_id'], 'tal_vendor_theme_idx');
            });
        }

        // Additive separation columns on existing vendor_storefront_configs
        if (Schema::hasTable('vendor_storefront_configs')) {
            Schema::table('vendor_storefront_configs', function (Blueprint $table) {
                if (!Schema::hasColumn('vendor_storefront_configs', 'draft_theme_id')) {
                    $table->string('draft_theme_id', 80)->nullable()->after('preset_key');
                }
                if (!Schema::hasColumn('vendor_storefront_configs', 'published_theme_id')) {
                    $table->string('published_theme_id', 80)->nullable()->after('draft_theme_id');
                }
                if (!Schema::hasColumn('vendor_storefront_configs', 'published_theme_version')) {
                    $table->string('published_theme_version', 20)->nullable()->after('published_theme_id');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('vendor_storefront_configs')) {
            Schema::table('vendor_storefront_configs', function (Blueprint $table) {
                foreach (['published_theme_version', 'published_theme_id', 'draft_theme_id'] as $col) {
                    if (Schema::hasColumn('vendor_storefront_configs', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }

        Schema::dropIfExists('theme_audit_logs');
        Schema::dropIfExists('vendor_theme_drafts');
        Schema::dropIfExists('vendor_theme_entitlements');
        Schema::dropIfExists('vendor_theme_requests');
        Schema::dropIfExists('storefront_themes');
    }
};
