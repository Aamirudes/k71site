<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('vendors')) {
            Schema::table('vendors', function (Blueprint $table) {
                if (!Schema::hasColumn('vendors', 'provider_mode')) {
                    $table->string('provider_mode', 20)->default('individual');
                }
                if (!Schema::hasColumn('vendors', 'headline')) {
                    $table->string('headline', 200)->nullable();
                }
                if (!Schema::hasColumn('vendors', 'website_status')) {
                    $table->string('website_status', 20)->default('published');
                }
                if (!Schema::hasColumn('vendors', 'website_published_at')) {
                    $table->timestamp('website_published_at')->nullable();
                }
                if (!Schema::hasColumn('vendors', 'onboarding_step')) {
                    $table->unsignedTinyInteger('onboarding_step')->default(1);
                }
                if (!Schema::hasColumn('vendors', 'work_mode')) {
                    $table->string('work_mode', 20)->default('remote');
                }
                if (!Schema::hasColumn('vendors', 'availability_status')) {
                    $table->string('availability_status', 20)->default('available');
                }
                if (!Schema::hasColumn('vendors', 'response_time_minutes')) {
                    $table->unsignedSmallInteger('response_time_minutes')->default(60);
                }
                if (!Schema::hasColumn('vendors', 'years_experience')) {
                    $table->unsignedSmallInteger('years_experience')->nullable();
                }
                if (!Schema::hasColumn('vendors', 'public_contact_preferences')) {
                    $table->json('public_contact_preferences')->nullable();
                }
                if (!Schema::hasColumn('vendors', 'external_links')) {
                    $table->json('external_links')->nullable();
                }
                if (!Schema::hasColumn('vendors', 'website_cache_version')) {
                    $table->unsignedInteger('website_cache_version')->default(1);
                }
            });
        }

        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                if (!Schema::hasColumn('products', 'pricing_mode')) {
                    $table->string('pricing_mode', 20)->default('packages');
                }
                if (!Schema::hasColumn('products', 'skills_json')) {
                    $table->json('skills_json')->nullable();
                }
                if (!Schema::hasColumn('products', 'is_website_featured')) {
                    $table->boolean('is_website_featured')->default(false);
                }
                if (!Schema::hasColumn('products', 'show_on_website')) {
                    $table->boolean('show_on_website')->default(true);
                }
            });
        }

        if (Schema::hasTable('reviews')) {
            Schema::table('reviews', function (Blueprint $table) {
                if (!Schema::hasColumn('reviews', 'vendor_id')) {
                    $table->unsignedBigInteger('vendor_id')->nullable()->index();
                }
                if (!Schema::hasColumn('reviews', 'service_order_id')) {
                    $table->unsignedBigInteger('service_order_id')->nullable()->index();
                }
                if (!Schema::hasColumn('reviews', 'project_contract_id')) {
                    $table->unsignedBigInteger('project_contract_id')->nullable()->index();
                }
                if (!Schema::hasColumn('reviews', 'vendor_reply')) {
                    $table->text('vendor_reply')->nullable();
                }
                if (!Schema::hasColumn('reviews', 'vendor_replied_at')) {
                    $table->timestamp('vendor_replied_at')->nullable();
                }
            });
        }

        if (!Schema::hasTable('vendor_subdomain_histories')) {
            Schema::create('vendor_subdomain_histories', function (Blueprint $table) {
                $table->id();
                $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();
                $table->string('slug', 30)->index();
                $table->boolean('is_active_redirect')->default(true);
                $table->timestamp('released_at')->nullable();
                $table->timestamp('cooldown_until')->nullable();
                $table->timestamps();

                $table->index(['slug', 'is_active_redirect']);
            });
        }

        if (!Schema::hasTable('portfolio_projects')) {
            Schema::create('portfolio_projects', function (Blueprint $table) {
                $table->id();
                $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();
                $table->unsignedBigInteger('category_id')->nullable();
                $table->unsignedBigInteger('service_product_id')->nullable();
                $table->unsignedBigInteger('service_order_id')->nullable();
                $table->string('title', 180);
                $table->string('slug', 200);
                $table->text('summary')->nullable();
                $table->longText('description')->nullable();
                $table->string('client_name', 120)->nullable();
                $table->string('role', 120)->nullable();
                $table->string('work_category', 80)->nullable();
                $table->json('skills_json')->nullable();
                $table->json('deliverables_json')->nullable();
                $table->string('external_url', 500)->nullable();
                $table->string('cover_image', 500)->nullable();
                $table->json('gallery_json')->nullable();
                $table->date('completed_at')->nullable();
                $table->boolean('is_featured')->default(false);
                $table->integer('sort_order')->default(0);
                $table->string('status', 20)->default('draft');
                $table->timestamps();

                $table->unique(['vendor_id', 'slug']);
                $table->index(['vendor_id', 'status', 'is_featured']);
            });
        }

        if (!Schema::hasTable('service_projects')) {
            Schema::create('service_projects', function (Blueprint $table) {
                $table->id();
                $table->foreignId('buyer_id')->constrained('users')->cascadeOnDelete();
                $table->unsignedBigInteger('category_id')->nullable();
                $table->string('project_number', 40)->unique();
                $table->string('title', 200);
                $table->string('slug', 220)->unique();
                $table->longText('description');
                $table->json('skills_json')->nullable();
                $table->json('deliverables_json')->nullable();
                $table->json('attachments_json')->nullable();
                $table->string('budget_type', 20)->default('fixed');
                $table->unsignedBigInteger('budget_min')->default(0);
                $table->unsignedBigInteger('budget_max')->nullable();
                $table->string('expected_duration', 80)->nullable();
                $table->date('deadline_at')->nullable();
                $table->string('work_mode', 20)->default('remote');
                $table->string('city', 100)->nullable();
                $table->string('status', 30)->default('open');
                $table->unsignedInteger('proposals_count')->default(0);
                $table->unsignedBigInteger('hired_vendor_id')->nullable();
                $table->timestamps();

                $table->index(['status', 'category_id', 'created_at']);
                $table->index(['buyer_id', 'status']);
            });
        }

        if (!Schema::hasTable('project_proposals')) {
            Schema::create('project_proposals', function (Blueprint $table) {
                $table->id();
                $table->foreignId('project_id')->constrained('service_projects')->cascadeOnDelete();
                $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();
                $table->longText('cover_letter');
                $table->unsignedBigInteger('bid_amount');
                $table->unsignedSmallInteger('delivery_days')->default(7);
                $table->string('pricing_type', 20)->default('fixed');
                $table->json('portfolio_project_ids_json')->nullable();
                $table->json('proposed_milestones_json')->nullable();
                $table->string('status', 25)->default('pending');
                $table->timestamps();

                $table->unique(['project_id', 'vendor_id']);
                $table->index(['vendor_id', 'status']);
                $table->index(['project_id', 'status']);
            });
        }

        if (!Schema::hasTable('project_contracts')) {
            Schema::create('project_contracts', function (Blueprint $table) {
                $table->id();
                $table->string('contract_number', 40)->unique();
                $table->foreignId('project_id')->unique()->constrained('service_projects')->cascadeOnDelete();
                $table->foreignId('proposal_id')->unique()->constrained('project_proposals')->cascadeOnDelete();
                $table->foreignId('buyer_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();
                $table->unsignedBigInteger('agreed_amount');
                $table->string('payment_status', 25)->default('escrow_funded');
                $table->string('status', 25)->default('active');
                $table->timestamp('started_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->unsignedTinyInteger('buyer_rating')->nullable();
                $table->text('buyer_review')->nullable();
                $table->timestamp('reviewed_at')->nullable();
                $table->text('vendor_reply')->nullable();
                $table->timestamp('vendor_replied_at')->nullable();
                $table->timestamps();

                $table->index(['buyer_id', 'status']);
                $table->index(['vendor_id', 'status']);
            });
        }

        if (!Schema::hasTable('project_milestones')) {
            Schema::create('project_milestones', function (Blueprint $table) {
                $table->id();
                $table->foreignId('contract_id')->constrained('project_contracts')->cascadeOnDelete();
                $table->string('title', 180);
                $table->text('description')->nullable();
                $table->unsignedBigInteger('amount')->default(0);
                $table->date('due_at')->nullable();
                $table->unsignedSmallInteger('sort_order')->default(0);
                $table->string('status', 25)->default('pending');
                $table->json('deliverables_json')->nullable();
                $table->text('submitted_notes')->nullable();
                $table->text('revision_notes')->nullable();
                $table->timestamp('submitted_at')->nullable();
                $table->timestamp('approved_at')->nullable();
                $table->timestamps();

                $table->index(['contract_id', 'sort_order']);
            });
        }

        if (!Schema::hasTable('storefront_analytics_events')) {
            Schema::create('storefront_analytics_events', function (Blueprint $table) {
                $table->id();
                $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();
                $table->string('event_type', 40)->index();
                $table->string('entity_type', 40)->nullable();
                $table->unsignedBigInteger('entity_id')->nullable();
                $table->string('visitor_hash', 64)->nullable();
                $table->string('referrer', 255)->nullable();
                $table->timestamp('created_at')->index();

                $table->index(['vendor_id', 'event_type', 'created_at'], 'storefront_events_vendor_type_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('storefront_analytics_events');
        Schema::dropIfExists('project_milestones');
        Schema::dropIfExists('project_contracts');
        Schema::dropIfExists('project_proposals');
        Schema::dropIfExists('service_projects');
        Schema::dropIfExists('portfolio_projects');
        Schema::dropIfExists('vendor_subdomain_histories');
    }
};

