<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Extend vendors with separated verification workflow, contact details & operating sectors
        if (Schema::hasTable('vendors')) {
            Schema::table('vendors', function (Blueprint $table) {
                if (!Schema::hasColumn('vendors', 'verification_status')) {
                    $table->string('verification_status', 30)->default('unverified')->after('status');
                    $table->index('verification_status');
                }
                if (!Schema::hasColumn('vendors', 'verification_notes')) {
                    $table->text('verification_notes')->nullable()->after('verification_status');
                }
                if (!Schema::hasColumn('vendors', 'verified_at')) {
                    $table->timestamp('verified_at')->nullable()->after('verification_notes');
                }
                if (!Schema::hasColumn('vendors', 'verified_by')) {
                    $table->unsignedBigInteger('verified_by')->nullable()->after('verified_at');
                }
                if (!Schema::hasColumn('vendors', 'whatsapp_number')) {
                    $table->string('whatsapp_number', 25)->nullable()->after('contact_phone');
                }
                if (!Schema::hasColumn('vendors', 'contact_person')) {
                    $table->string('contact_person', 120)->nullable()->after('shop_name');
                }
                if (!Schema::hasColumn('vendors', 'operating_sectors')) {
                    $table->json('operating_sectors')->nullable()->after('vendor_type');
                }
                if (!Schema::hasColumn('vendors', 'last_admin_action')) {
                    $table->string('last_admin_action', 120)->nullable()->after('verified_by');
                }
                if (!Schema::hasColumn('vendors', 'last_admin_action_at')) {
                    $table->timestamp('last_admin_action_at')->nullable()->after('last_admin_action');
                }
                if (!Schema::hasColumn('vendors', 'last_admin_action_by')) {
                    $table->unsignedBigInteger('last_admin_action_by')->nullable()->after('last_admin_action_at');
                }
            });

            // Safe backfill of existing vendor records without altering primary account statuses
            DB::table('vendors')->orderBy('id')->chunk(100, function ($vendors) {
                foreach ($vendors as $v) {
                    $verStatus = match ($v->status) {
                        'approved', 'active' => 'verified',
                        'suspended' => 'suspended',
                        'closed' => 'rejected',
                        'pending' => 'pending_review',
                        default => 'unverified',
                    };
                    $primarySector = self::inferSectorKey((string) ($v->vendor_type ?? ''));
                    DB::table('vendors')->where('id', $v->id)->update([
                        'verification_status' => $verStatus,
                        'verified_at' => in_array($v->status, ['approved', 'active'], true) ? ($v->approved_at ?? $v->created_at) : null,
                        'verified_by' => in_array($v->status, ['approved', 'active'], true) ? $v->approved_by : null,
                        'operating_sectors' => json_encode([$primarySector]),
                    ]);
                }
            });
        }

        // 2. Dynamic Sectors Registry
        if (!Schema::hasTable('k71_sectors')) {
            Schema::create('k71_sectors', function (Blueprint $table) {
                $table->id();
                $table->string('key', 40)->unique();
                $table->string('name', 80);
                $table->string('description', 255)->nullable();
                $table->boolean('is_active')->default(true);
                $table->boolean('supports_commissions')->default(false);
                $table->integer('sort_order')->default(0);
                $table->json('metadata')->nullable();
                $table->timestamps();
            });

            $now = now();
            DB::table('k71_sectors')->insert([
                [
                    'key' => 'products',
                    'name' => 'Products & Retail Marketplace',
                    'description' => 'Physical retail goods, fashion, marble, electronics, cart & online checkout orders.',
                    'is_active' => true,
                    'supports_commissions' => true,
                    'sort_order' => 1,
                    'metadata' => json_encode(['vendor_types' => ['marketplace', 'ecommerce', 'retail', 'clothing', 'fashion', 'footwear', 'electronics', 'home_living', 'beauty', 'automotive', 'marble', 'groceries', 'toys_sports', 'mens']]),
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'key' => 'food',
                    'name' => 'Food & Restaurants',
                    'description' => 'Restaurants, cloud kitchens, menus, add-ons, delivery & takeaway orders.',
                    'is_active' => true,
                    'supports_commissions' => true,
                    'sort_order' => 2,
                    'metadata' => json_encode(['vendor_types' => ['food', 'feasto', 'restaurant', 'kirana']]),
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'key' => 'services',
                    'name' => 'Services & Freelance Studio',
                    'description' => 'Service providers, personal websites, gig packages, portfolios, bookings & contracts.',
                    'is_active' => true,
                    'supports_commissions' => true,
                    'sort_order' => 3,
                    'metadata' => json_encode(['vendor_types' => ['services', 'service', 'digital_services', 'local_services']]),
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'key' => 'used',
                    'name' => 'Used & Pre-Owned Goods',
                    'description' => 'Pre-owned commercial dealer listings, classified ads & negotiated offers.',
                    'is_active' => true,
                    'supports_commissions' => false,
                    'sort_order' => 4,
                    'metadata' => json_encode(['vendor_types' => ['used']]),
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'key' => 'enquiry',
                    'name' => 'B2B & Bulk Enquiries',
                    'description' => 'Wholesale, industrial machinery, marble sourcing & RFQ quote responses.',
                    'is_active' => true,
                    'supports_commissions' => false,
                    'sort_order' => 5,
                    'metadata' => json_encode(['vendor_types' => ['enquiry', 'b2b', 'machinery', 'real_estate', 'wholesale', 'industrial', 'manufacturer']]),
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            ]);
        }

        // 3. WhatsApp Onboarding Leads (pre-account prospective vendors)
        if (!Schema::hasTable('onboarding_leads')) {
            Schema::create('onboarding_leads', function (Blueprint $table) {
                $table->id();
                $table->string('business_name', 150);
                $table->string('contact_person', 120)->nullable();
                $table->string('whatsapp_number', 25)->nullable();
                $table->string('phone', 25)->nullable();
                $table->string('email', 255)->nullable();
                $table->string('sector_key', 40)->nullable()->index();
                $table->string('city', 100)->nullable();
                $table->string('status', 30)->default('new')->index(); // new, contacted, negotiating, converted, archived
                $table->unsignedBigInteger('proposed_plan_id')->nullable();
                $table->unsignedBigInteger('linked_vendor_id')->nullable()->index();
                $table->text('notes')->nullable();
                $table->timestamp('follow_up_at')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();
            });
        }

        // 4. Upgrade subscription_plans & create subscription_plan_versions
        if (Schema::hasTable('subscription_plans')) {
            Schema::table('subscription_plans', function (Blueprint $table) {
                $table->integer('price_monthly')->default(0)->change();
                if (!Schema::hasColumn('subscription_plans', 'code')) {
                    $table->string('code', 60)->nullable()->unique()->after('id');
                }
                if (!Schema::hasColumn('subscription_plans', 'sector_key')) {
                    $table->string('sector_key', 40)->default('products')->after('slug');
                    $table->index('sector_key');
                }
                if (!Schema::hasColumn('subscription_plans', 'applicable_sectors')) {
                    $table->json('applicable_sectors')->nullable()->after('sector_key');
                }
                if (!Schema::hasColumn('subscription_plans', 'is_cross_sector')) {
                    $table->boolean('is_cross_sector')->default(false)->after('applicable_sectors');
                }
                if (!Schema::hasColumn('subscription_plans', 'is_free')) {
                    $table->boolean('is_free')->default(false)->after('is_cross_sector');
                }
                if (!Schema::hasColumn('subscription_plans', 'price_amount')) {
                    $table->integer('price_amount')->default(0)->after('is_free'); // paise
                }
                if (!Schema::hasColumn('subscription_plans', 'currency')) {
                    $table->string('currency', 10)->default('INR')->after('price_amount');
                }
                if (!Schema::hasColumn('subscription_plans', 'duration_days')) {
                    $table->integer('duration_days')->default(30)->after('currency');
                }
                if (!Schema::hasColumn('subscription_plans', 'custom_duration_allowed')) {
                    $table->boolean('custom_duration_allowed')->default(true)->after('duration_days');
                }
                if (!Schema::hasColumn('subscription_plans', 'default_commission_rule_id')) {
                    $table->unsignedBigInteger('default_commission_rule_id')->nullable()->after('custom_duration_allowed');
                }
                if (!Schema::hasColumn('subscription_plans', 'status')) {
                    $table->string('status', 25)->default('active')->after('default_commission_rule_id'); // draft, active, archived
                    $table->index('status');
                }
                if (!Schema::hasColumn('subscription_plans', 'is_public_display')) {
                    $table->boolean('is_public_display')->default(false)->after('status');
                }
                if (!Schema::hasColumn('subscription_plans', 'current_version')) {
                    $table->integer('current_version')->default(1)->after('is_public_display');
                }
                if (!Schema::hasColumn('subscription_plans', 'admin_notes')) {
                    $table->text('admin_notes')->nullable()->after('current_version');
                }
                if (!Schema::hasColumn('subscription_plans', 'created_by')) {
                    $table->unsignedBigInteger('created_by')->nullable()->after('admin_notes');
                }
                if (!Schema::hasColumn('subscription_plans', 'updated_by')) {
                    $table->unsignedBigInteger('updated_by')->nullable()->after('created_by');
                }
            });
        }

        if (!Schema::hasTable('subscription_plan_versions')) {
            Schema::create('subscription_plan_versions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('plan_id')->constrained('subscription_plans')->cascadeOnDelete();
                $table->integer('version_number');
                $table->string('name', 120);
                $table->text('description')->nullable();
                $table->boolean('is_free')->default(false);
                $table->integer('price_amount')->default(0); // paise
                $table->string('currency', 10)->default('INR');
                $table->integer('duration_days')->default(30);
                $table->unsignedBigInteger('default_commission_rule_id')->nullable();
                $table->json('entitlements_json');
                $table->json('expiry_policy_json');
                $table->json('promotional_benefits_json')->nullable();
                $table->text('change_notes')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();

                $table->unique(['plan_id', 'version_number']);
            });
        }

        // 5. Upgrade vendor_subscriptions with immutable snapshots, sector isolation, & manual payment tracking
        if (Schema::hasTable('vendor_subscriptions')) {
            Schema::table('vendor_subscriptions', function (Blueprint $table) {
                $table->timestamp('starts_at')->nullable()->change();
                $table->timestamp('ends_at')->nullable()->change();

                if (!Schema::hasColumn('vendor_subscriptions', 'sector_key')) {
                    $table->string('sector_key', 40)->default('products')->after('plan_id');
                }
                if (!Schema::hasColumn('vendor_subscriptions', 'applicable_sectors_snapshot')) {
                    $table->json('applicable_sectors_snapshot')->nullable()->after('sector_key');
                }
                if (!Schema::hasColumn('vendor_subscriptions', 'plan_version_id')) {
                    $table->unsignedBigInteger('plan_version_id')->nullable()->after('applicable_sectors_snapshot');
                }
                if (!Schema::hasColumn('vendor_subscriptions', 'plan_version_number')) {
                    $table->integer('plan_version_number')->default(1)->after('plan_version_id');
                }
                if (!Schema::hasColumn('vendor_subscriptions', 'plan_name_snapshot')) {
                    $table->string('plan_name_snapshot', 120)->nullable()->after('plan_version_number');
                }
                if (!Schema::hasColumn('vendor_subscriptions', 'price_amount_snapshot')) {
                    $table->integer('price_amount_snapshot')->default(0)->after('plan_name_snapshot');
                }
                if (!Schema::hasColumn('vendor_subscriptions', 'is_free_snapshot')) {
                    $table->boolean('is_free_snapshot')->default(false)->after('price_amount_snapshot');
                }
                if (!Schema::hasColumn('vendor_subscriptions', 'currency')) {
                    $table->string('currency', 10)->default('INR')->after('is_free_snapshot');
                }
                if (!Schema::hasColumn('vendor_subscriptions', 'duration_days')) {
                    $table->integer('duration_days')->default(30)->after('currency');
                }
                if (!Schema::hasColumn('vendor_subscriptions', 'entitlements_snapshot')) {
                    $table->json('entitlements_snapshot')->nullable()->after('duration_days');
                }
                if (!Schema::hasColumn('vendor_subscriptions', 'expiry_policy_snapshot')) {
                    $table->json('expiry_policy_snapshot')->nullable()->after('entitlements_snapshot');
                }
                if (!Schema::hasColumn('vendor_subscriptions', 'grace_keep_visible')) {
                    $table->boolean('grace_keep_visible')->default(true)->after('grace_ends_at');
                }
                if (!Schema::hasColumn('vendor_subscriptions', 'grace_keep_features')) {
                    $table->boolean('grace_keep_features')->default(false)->after('grace_keep_visible');
                }
                if (!Schema::hasColumn('vendor_subscriptions', 'commission_rule_id')) {
                    $table->unsignedBigInteger('commission_rule_id')->nullable()->after('grace_keep_features');
                }
                if (!Schema::hasColumn('vendor_subscriptions', 'commission_rate_override')) {
                    $table->decimal('commission_rate_override', 6, 2)->nullable()->after('commission_rule_id');
                }
                if (!Schema::hasColumn('vendor_subscriptions', 'commission_fixed_override')) {
                    $table->integer('commission_fixed_override')->nullable()->after('commission_rate_override');
                }
                if (!Schema::hasColumn('vendor_subscriptions', 'reminder_days_before')) {
                    $table->json('reminder_days_before')->nullable()->after('commission_fixed_override');
                }
                if (!Schema::hasColumn('vendor_subscriptions', 'auto_reminder_enabled')) {
                    $table->boolean('auto_reminder_enabled')->default(true)->after('reminder_days_before');
                }
                if (!Schema::hasColumn('vendor_subscriptions', 'external_payment_received')) {
                    $table->boolean('external_payment_received')->default(false)->after('auto_reminder_enabled');
                }
                if (!Schema::hasColumn('vendor_subscriptions', 'external_payment_date')) {
                    $table->timestamp('external_payment_date')->nullable()->after('external_payment_received');
                }
                if (!Schema::hasColumn('vendor_subscriptions', 'external_payment_amount')) {
                    $table->integer('external_payment_amount')->nullable()->after('external_payment_date');
                }
                if (!Schema::hasColumn('vendor_subscriptions', 'external_payment_method')) {
                    $table->string('external_payment_method', 50)->nullable()->after('external_payment_amount');
                }
                if (!Schema::hasColumn('vendor_subscriptions', 'external_payment_ref')) {
                    $table->string('external_payment_ref', 120)->nullable()->after('external_payment_method');
                }
                if (!Schema::hasColumn('vendor_subscriptions', 'external_receipt_path')) {
                    $table->string('external_receipt_path', 500)->nullable()->after('external_payment_ref');
                }
                if (!Schema::hasColumn('vendor_subscriptions', 'internal_notes')) {
                    $table->text('internal_notes')->nullable()->after('external_receipt_path');
                }
                if (!Schema::hasColumn('vendor_subscriptions', 'admin_reason')) {
                    $table->string('admin_reason', 500)->nullable()->after('internal_notes');
                }
                if (!Schema::hasColumn('vendor_subscriptions', 'assigned_by')) {
                    $table->unsignedBigInteger('assigned_by')->nullable()->after('admin_reason');
                }
                if (!Schema::hasColumn('vendor_subscriptions', 'activated_by')) {
                    $table->unsignedBigInteger('activated_by')->nullable()->after('assigned_by');
                }
                if (!Schema::hasColumn('vendor_subscriptions', 'activated_at')) {
                    $table->timestamp('activated_at')->nullable()->after('activated_by');
                }
                if (!Schema::hasColumn('vendor_subscriptions', 'expired_at')) {
                    $table->timestamp('expired_at')->nullable()->after('activated_at');
                }
                if (!Schema::hasColumn('vendor_subscriptions', 'suspended_at')) {
                    $table->timestamp('suspended_at')->nullable()->after('expired_at');
                }
                if (!Schema::hasColumn('vendor_subscriptions', 'renewed_from_id')) {
                    $table->unsignedBigInteger('renewed_from_id')->nullable()->after('suspended_at');
                }

                $table->index(['vendor_id', 'sector_key', 'status'], 'vs_vendor_sector_status_idx');
                $table->index(['status', 'ends_at'], 'vs_status_ends_idx');
            });
        }

        // 6. Subscription Lifecycle Events
        if (!Schema::hasTable('subscription_events')) {
            Schema::create('subscription_events', function (Blueprint $table) {
                $table->id();
                $table->foreignId('vendor_subscription_id')->constrained('vendor_subscriptions')->cascadeOnDelete();
                $table->unsignedBigInteger('vendor_id')->index();
                $table->string('sector_key', 40)->index();
                $table->string('event_type', 50)->index();
                $table->string('from_status', 30)->nullable();
                $table->string('to_status', 30)->nullable();
                $table->unsignedBigInteger('actor_id')->nullable();
                $table->string('actor_type', 20)->default('admin');
                $table->string('reason', 500)->nullable();
                $table->json('payload_json')->nullable();
                $table->timestamp('created_at')->nullable();
            });
        }

        // 7. Deduplicated Subscription Notifications & Reminders
        if (!Schema::hasTable('subscription_notifications')) {
            Schema::create('subscription_notifications', function (Blueprint $table) {
                $table->id();
                $table->foreignId('vendor_subscription_id')->constrained('vendor_subscriptions')->cascadeOnDelete();
                $table->unsignedBigInteger('vendor_id')->index();
                $table->string('sector_key', 40);
                $table->string('recipient_type', 20)->default('vendor'); // vendor, admin
                $table->string('event_key', 60);
                $table->integer('reminder_day')->nullable();
                $table->string('title', 180);
                $table->text('message');
                $table->text('whatsapp_message')->nullable();
                $table->string('channel', 30)->default('in_app');
                $table->string('dedupe_key', 120)->unique();
                $table->boolean('is_read')->default(false);
                $table->timestamp('sent_at')->nullable();
                $table->timestamp('read_at')->nullable();
                $table->timestamps();

                $table->index(['vendor_id', 'is_read']);
            });
        }

        // 8. Commission Rules Hierarchy
        if (!Schema::hasTable('commission_rules')) {
            Schema::create('commission_rules', function (Blueprint $table) {
                $table->id();
                $table->string('name', 120);
                $table->string('scope', 30)->index(); // platform, sector, plan, vendor, category
                $table->string('sector_key', 40)->nullable()->index();
                $table->unsignedBigInteger('plan_id')->nullable()->index();
                $table->unsignedBigInteger('vendor_id')->nullable()->index();
                $table->unsignedBigInteger('category_id')->nullable()->index();
                $table->decimal('rate_percent', 6, 2)->default(5.00);
                $table->integer('fixed_fee_paise')->default(0);
                $table->boolean('is_active')->default(true)->index();
                $table->timestamp('effective_from')->nullable();
                $table->timestamp('effective_until')->nullable();
                $table->integer('priority')->default(0);
                $table->integer('version')->default(1);
                $table->text('notes')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });

            DB::table('commission_rules')->insert([
                'name' => 'K71 Default Platform Commission (5%)',
                'scope' => 'platform',
                'rate_percent' => 5.00,
                'fixed_fee_paise' => 0,
                'is_active' => true,
                'priority' => 0,
                'version' => 1,
                'notes' => 'Baseline K71 platform commission rate matching existing marketplace settlements.',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 9. Idempotent Order & Transaction Commission Records
        if (!Schema::hasTable('order_commissions')) {
            Schema::create('order_commissions', function (Blueprint $table) {
                $table->id();
                $table->string('transaction_type', 40)->default('order'); // order, service_order
                $table->unsignedBigInteger('transaction_id');
                $table->string('order_number', 50)->nullable();
                $table->unsignedBigInteger('vendor_id')->index();
                $table->string('sector_key', 40)->index();
                $table->unsignedBigInteger('subscription_id')->nullable();
                $table->unsignedBigInteger('plan_id')->nullable();
                $table->integer('plan_version_number')->nullable();
                $table->unsignedBigInteger('commission_rule_id')->nullable();
                $table->integer('commission_rule_version')->nullable();
                $table->string('rule_source', 40)->default('platform'); // transaction_override, vendor, category, plan, sector, platform
                $table->bigInteger('commission_base_paise')->default(0);
                $table->bigInteger('tax_paise')->default(0);
                $table->bigInteger('shipping_paise')->default(0);
                $table->bigInteger('discount_paise')->default(0);
                $table->decimal('rate_percent', 6, 2)->default(0);
                $table->integer('fixed_fee_paise')->default(0);
                $table->bigInteger('calculated_commission_paise')->default(0);
                $table->bigInteger('vendor_net_paise')->default(0);
                $table->string('event_type', 20)->default('charge'); // charge, reversal
                $table->string('refund_state', 20)->default('none'); // none, partial, full
                $table->string('settlement_status', 30)->default('recorded');
                $table->boolean('is_manual_override')->default(false);
                $table->string('override_reason', 500)->nullable();
                $table->text('notes')->nullable();
                $table->json('rule_snapshot_json')->nullable();
                $table->timestamp('calculated_at')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();

                $table->unique(['transaction_type', 'transaction_id', 'event_type'], 'order_comm_idempotent_uq');
            });
        }

        // 10. Monetization & Expiry Automation Settings
        if (!Schema::hasTable('monetization_settings')) {
            Schema::create('monetization_settings', function (Blueprint $table) {
                $table->id();
                $table->string('key', 100)->unique();
                $table->text('value')->nullable();
                $table->string('type', 20)->default('string');
                $table->timestamps();
            });

            $now = now();
            DB::table('monetization_settings')->insert([
                ['key' => 'enforce_subscription_visibility', 'value' => '1', 'type' => 'boolean', 'created_at' => $now, 'updated_at' => $now],
                ['key' => 'default_grace_days', 'value' => '3', 'type' => 'integer', 'created_at' => $now, 'updated_at' => $now],
                ['key' => 'default_grace_keep_visible', 'value' => '1', 'type' => 'boolean', 'created_at' => $now, 'updated_at' => $now],
                ['key' => 'default_grace_keep_features', 'value' => '0', 'type' => 'boolean', 'created_at' => $now, 'updated_at' => $now],
                ['key' => 'default_reminder_days', 'value' => json_encode([7, 3, 1]), 'type' => 'json', 'created_at' => $now, 'updated_at' => $now],
                ['key' => 'admin_whatsapp_number', 'value' => '', 'type' => 'string', 'created_at' => $now, 'updated_at' => $now],
                ['key' => 'whatsapp_renewal_template', 'value' => 'Hello {contact_person}, your K71 {sector_name} subscription for {shop_name} (Plan: {plan_name}) expires on {expiry_date} ({days_remaining} day(s) left). Message us here on WhatsApp to renew and keep your storefront uninterrupted.', 'type' => 'string', 'created_at' => $now, 'updated_at' => $now],
                ['key' => 'scheduler_failure_alert_enabled', 'value' => '1', 'type' => 'boolean', 'created_at' => $now, 'updated_at' => $now],
                ['key' => 'last_expiry_run_at', 'value' => '', 'type' => 'string', 'created_at' => $now, 'updated_at' => $now],
            ]);
        }

        // 11. Scheduled Job Run Logs
        if (!Schema::hasTable('scheduled_job_runs')) {
            Schema::create('scheduled_job_runs', function (Blueprint $table) {
                $table->id();
                $table->string('job_name', 80)->index();
                $table->string('status', 25)->default('running'); // running, success, failed
                $table->integer('processed_count')->default(0);
                $table->integer('expired_count')->default(0);
                $table->integer('grace_count')->default(0);
                $table->integer('reminders_created_count')->default(0);
                $table->integer('failed_count')->default(0);
                $table->text('summary')->nullable();
                $table->text('error_message')->nullable();
                $table->timestamp('started_at')->nullable();
                $table->timestamp('finished_at')->nullable();
                $table->timestamps();
            });
        }

        // 12. Vendor Manual Renewal Requests (NO checkout, request only)
        if (!Schema::hasTable('subscription_renewal_requests')) {
            Schema::create('subscription_renewal_requests', function (Blueprint $table) {
                $table->id();
                $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();
                $table->unsignedBigInteger('vendor_subscription_id')->nullable();
                $table->string('sector_key', 40);
                $table->unsignedBigInteger('requested_plan_id')->nullable();
                $table->text('message')->nullable();
                $table->string('status', 25)->default('pending')->index(); // pending, reviewed, fulfilled, dismissed
                $table->text('admin_notes')->nullable();
                $table->unsignedBigInteger('reviewed_by')->nullable();
                $table->timestamp('reviewed_at')->nullable();
                $table->timestamps();
            });
        }

        // 13. Extend audit_logs safely for rich monetization actions
        if (Schema::hasTable('audit_logs')) {
            Schema::table('audit_logs', function (Blueprint $table) {
                $table->string('action', 80)->change();
                if (!Schema::hasColumn('audit_logs', 'reason')) {
                    $table->string('reason', 500)->nullable()->after('action');
                }
                if (!Schema::hasColumn('audit_logs', 'vendor_id')) {
                    $table->unsignedBigInteger('vendor_id')->nullable()->index()->after('user_id');
                }
                if (!Schema::hasColumn('audit_logs', 'sector_key')) {
                    $table->string('sector_key', 40)->nullable()->after('vendor_id');
                }
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_renewal_requests');
        Schema::dropIfExists('scheduled_job_runs');
        Schema::dropIfExists('monetization_settings');
        Schema::dropIfExists('order_commissions');
        Schema::dropIfExists('commission_rules');
        Schema::dropIfExists('subscription_notifications');
        Schema::dropIfExists('subscription_events');
        Schema::dropIfExists('subscription_plan_versions');
        Schema::dropIfExists('onboarding_leads');
        Schema::dropIfExists('k71_sectors');

        if (Schema::hasTable('vendors')) {
            Schema::table('vendors', function (Blueprint $table) {
                $cols = ['verification_status', 'verification_notes', 'verified_at', 'verified_by', 'whatsapp_number', 'contact_person', 'operating_sectors', 'last_admin_action', 'last_admin_action_at', 'last_admin_action_by'];
                $existing = array_values(array_filter($cols, fn($c) => Schema::hasColumn('vendors', $c)));
                if ($existing) {
                    $table->dropColumn($existing);
                }
            });
        }
    }

    protected static function inferSectorKey(string $vendorType): string
    {
        $type = strtolower(trim($vendorType));
        if (in_array($type, ['food', 'feasto', 'restaurant', 'kirana'], true)) {
            return 'food';
        }
        if (in_array($type, ['services', 'service', 'digital_services', 'local_services'], true)) {
            return 'services';
        }
        if ($type === 'used') {
            return 'used';
        }
        if (in_array($type, ['enquiry', 'b2b', 'machinery', 'real_estate', 'wholesale', 'industrial', 'manufacturer'], true)) {
            return 'enquiry';
        }
        return 'products';
    }
};
