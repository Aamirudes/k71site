<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Upgrade Vendors for Freelance & Professional Services Profile
        Schema::table('vendors', function (Blueprint $table) {
            if (!Schema::hasColumn('vendors', 'tagline')) {
                $table->string('tagline', 255)->nullable()->after('shop_name');
            }
            if (!Schema::hasColumn('vendors', 'skills_json')) {
                $table->json('skills_json')->nullable()->after('tagline');
            }
            if (!Schema::hasColumn('vendors', 'languages_json')) {
                $table->json('languages_json')->nullable()->after('skills_json');
            }
            if (!Schema::hasColumn('vendors', 'hourly_rate')) {
                $table->integer('hourly_rate')->nullable()->after('languages_json'); // paise
            }
            if (!Schema::hasColumn('vendors', 'response_time')) {
                $table->string('response_time', 50)->default('Within 1 hour')->after('hourly_rate');
            }
            if (!Schema::hasColumn('vendors', 'rating')) {
                $table->decimal('rating', 3, 2)->default(5.00)->after('response_time');
            }
            if (!Schema::hasColumn('vendors', 'completed_projects')) {
                $table->integer('completed_projects')->default(0)->after('rating');
            }
            if (!Schema::hasColumn('vendors', 'portfolio_items')) {
                $table->json('portfolio_items')->nullable()->after('completed_projects');
            }
            if (!Schema::hasColumn('vendors', 'is_verified_pro')) {
                $table->boolean('is_verified_pro')->default(true)->after('portfolio_items');
            }
        });

        // 2. Upgrade Products for Service Detail Requirements, FAQs and Add-ons
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'faqs_json')) {
                $table->json('faqs_json')->nullable()->after('service_config_json');
            }
            if (!Schema::hasColumn('products', 'requirements_prompt')) {
                $table->text('requirements_prompt')->nullable()->after('faqs_json');
            }
            if (!Schema::hasColumn('products', 'addons_json')) {
                $table->json('addons_json')->nullable()->after('requirements_prompt');
            }
        });

        // 3. Create Dedicated Freelance & Service Orders Workspace Table
        if (!Schema::hasTable('service_orders')) {
            Schema::create('service_orders', function (Blueprint $table) {
                $table->id();
                $table->string('order_number', 50)->unique();
                $table->foreignId('buyer_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();
                $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
                $table->unsignedInteger('gig_package_id')->nullable();
                $table->string('package_tier', 20)->default('basic'); // basic, standard, premium, custom
                $table->string('package_title', 200)->nullable();
                $table->integer('amount'); // paise
                $table->string('payment_status', 20)->default('paid'); // paid, pending, refunded
                $table->string('status', 30)->default('in_progress'); // in_progress, delivered, revision_requested, completed, cancelled, disputed
                $table->unsignedSmallInteger('delivery_days')->default(3);
                $table->integer('revisions_allowed')->default(2);
                $table->integer('revisions_used')->default(0);
                $table->dateTime('due_at')->nullable();
                $table->text('requirements')->nullable();
                $table->json('requirements_files')->nullable();
                $table->json('deliveries')->nullable();
                $table->text('revision_notes')->nullable();
                $table->text('dispute_reason')->nullable();
                $table->string('dispute_status', 20)->default('none'); // none, opened, resolved
                $table->unsignedTinyInteger('buyer_rating')->nullable();
                $table->text('buyer_review')->nullable();
                $table->dateTime('delivered_at')->nullable();
                $table->dateTime('completed_at')->nullable();
                $table->timestamps();

                $table->index(['buyer_id', 'status']);
                $table->index(['vendor_id', 'status']);
                $table->index('order_number');
            });
        }

        // 4. Create Workspace Conversation Messages Table
        if (!Schema::hasTable('service_order_messages')) {
            Schema::create('service_order_messages', function (Blueprint $table) {
                $table->id();
                $table->foreignId('service_order_id')->constrained('service_orders')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('sender_type', 20)->default('buyer'); // buyer, seller, admin
                $table->text('message');
                $table->json('attachments')->nullable();
                $table->timestamps();

                $table->index(['service_order_id', 'created_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('service_order_messages');
        Schema::dropIfExists('service_orders');

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['faqs_json', 'requirements_prompt', 'addons_json']);
        });

        Schema::table('vendors', function (Blueprint $table) {
            $table->dropColumn([
                'tagline',
                'skills_json',
                'languages_json',
                'hourly_rate',
                'response_time',
                'rating',
                'completed_projects',
                'portfolio_items',
                'is_verified_pro',
            ]);
        });
    }
};
