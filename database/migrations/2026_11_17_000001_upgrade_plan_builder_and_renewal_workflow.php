<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('subscription_plans')) {
            Schema::table('subscription_plans', function (Blueprint $table) {
                if (!Schema::hasColumn('subscription_plans', 'tier_key')) {
                    $table->string('tier_key', 40)->nullable()->index()->after('sector_key');
                }
                if (!Schema::hasColumn('subscription_plans', 'badge_text')) {
                    $table->string('badge_text', 60)->nullable()->after('name');
                }
                if (!Schema::hasColumn('subscription_plans', 'duration_unit')) {
                    $table->string('duration_unit', 16)->default('days')->after('duration_days');
                }
                if (!Schema::hasColumn('subscription_plans', 'duration_value')) {
                    $table->unsignedInteger('duration_value')->default(30)->after('duration_unit');
                }
                if (!Schema::hasColumn('subscription_plans', 'benefits_json')) {
                    $table->json('benefits_json')->nullable()->after('features');
                }
                if (!Schema::hasColumn('subscription_plans', 'commission_type')) {
                    $table->string('commission_type', 24)->nullable()->after('default_commission_rule_id');
                }
                if (!Schema::hasColumn('subscription_plans', 'commission_rate')) {
                    $table->decimal('commission_rate', 5, 2)->nullable()->after('commission_type');
                }
                if (!Schema::hasColumn('subscription_plans', 'commission_fixed_paise')) {
                    $table->unsignedInteger('commission_fixed_paise')->nullable()->after('commission_rate');
                }
                if (!Schema::hasColumn('subscription_plans', 'auto_apply_to_existing')) {
                    $table->boolean('auto_apply_to_existing')->default(false)->after('current_version');
                }
            });
        }

        if (Schema::hasTable('subscription_plan_versions')) {
            Schema::table('subscription_plan_versions', function (Blueprint $table) {
                if (!Schema::hasColumn('subscription_plan_versions', 'badge_text')) {
                    $table->string('badge_text', 60)->nullable()->after('name');
                }
                if (!Schema::hasColumn('subscription_plan_versions', 'benefits_json')) {
                    $table->json('benefits_json')->nullable()->after('promotional_benefits_json');
                }
                if (!Schema::hasColumn('subscription_plan_versions', 'commission_type')) {
                    $table->string('commission_type', 24)->nullable()->after('default_commission_rule_id');
                }
                if (!Schema::hasColumn('subscription_plan_versions', 'commission_rate')) {
                    $table->decimal('commission_rate', 5, 2)->nullable()->after('commission_type');
                }
                if (!Schema::hasColumn('subscription_plan_versions', 'commission_fixed_paise')) {
                    $table->unsignedInteger('commission_fixed_paise')->nullable()->after('commission_rate');
                }
            });
        }

        if (Schema::hasTable('vendor_subscriptions')) {
            Schema::table('vendor_subscriptions', function (Blueprint $table) {
                if (!Schema::hasColumn('vendor_subscriptions', 'commission_type_snapshot')) {
                    $table->string('commission_type_snapshot', 24)->nullable()->after('commission_fixed_override');
                }
                if (!Schema::hasColumn('vendor_subscriptions', 'commission_rate_snapshot')) {
                    $table->decimal('commission_rate_snapshot', 5, 2)->nullable()->after('commission_type_snapshot');
                }
                if (!Schema::hasColumn('vendor_subscriptions', 'commission_fixed_snapshot')) {
                    $table->unsignedInteger('commission_fixed_snapshot')->nullable()->after('commission_rate_snapshot');
                }
            });
        }

        if (Schema::hasTable('subscription_renewal_requests')) {
            Schema::table('subscription_renewal_requests', function (Blueprint $table) {
                if (!Schema::hasColumn('subscription_renewal_requests', 'admin_reply')) {
                    $table->text('admin_reply')->nullable()->after('admin_notes');
                }
                if (!Schema::hasColumn('subscription_renewal_requests', 'replied_at')) {
                    $table->timestamp('replied_at')->nullable()->after('admin_reply');
                }
                if (!Schema::hasColumn('subscription_renewal_requests', 'replied_by')) {
                    $table->unsignedBigInteger('replied_by')->nullable()->after('replied_at');
                }
                if (!Schema::hasColumn('subscription_renewal_requests', 'rejection_reason')) {
                    $table->text('rejection_reason')->nullable()->after('replied_by');
                }
                if (!Schema::hasColumn('subscription_renewal_requests', 'renewed_subscription_id')) {
                    $table->unsignedBigInteger('renewed_subscription_id')->nullable()->after('rejection_reason');
                }
            });
        }

        // Idempotently ensure the 3 configurable tiers exist per core sector without overwriting any existing plans or subscriptions
        try {
            if (Schema::hasTable('k71_sectors') && Schema::hasTable('subscription_plans')) {
                app(\App\Services\Monetization\SectorRegistryService::class)->ensureDefaultThreeTierPlans();
            }
        } catch (\Throwable $e) {
            // Do not fail migrations if called before base seeds exist
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('subscription_renewal_requests')) {
            Schema::table('subscription_renewal_requests', function (Blueprint $table) {
                $cols = array_filter(['admin_reply', 'replied_at', 'replied_by', 'rejection_reason', 'renewed_subscription_id'], fn ($c) => Schema::hasColumn('subscription_renewal_requests', $c));
                if ($cols) $table->dropColumn($cols);
            });
        }
        if (Schema::hasTable('vendor_subscriptions')) {
            Schema::table('vendor_subscriptions', function (Blueprint $table) {
                $cols = array_filter(['commission_type_snapshot', 'commission_rate_snapshot', 'commission_fixed_snapshot'], fn ($c) => Schema::hasColumn('vendor_subscriptions', $c));
                if ($cols) $table->dropColumn($cols);
            });
        }
        if (Schema::hasTable('subscription_plan_versions')) {
            Schema::table('subscription_plan_versions', function (Blueprint $table) {
                $cols = array_filter(['badge_text', 'benefits_json', 'commission_type', 'commission_rate', 'commission_fixed_paise'], fn ($c) => Schema::hasColumn('subscription_plan_versions', $c));
                if ($cols) $table->dropColumn($cols);
            });
        }
        if (Schema::hasTable('subscription_plans')) {
            Schema::table('subscription_plans', function (Blueprint $table) {
                $cols = array_filter(['tier_key', 'badge_text', 'duration_unit', 'duration_value', 'benefits_json', 'commission_type', 'commission_rate', 'commission_fixed_paise', 'auto_apply_to_existing'], fn ($c) => Schema::hasColumn('subscription_plans', $c));
                if ($cols) $table->dropColumn($cols);
            });
        }
    }
};

