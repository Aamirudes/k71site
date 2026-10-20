<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run migrations for K71 Phase 8: Enquiry Marketplace & Quotes (enquiry.k71.in).
     */
    public function up(): void
    {
        // 1. Create enquiries table
        if (!Schema::hasTable('enquiries')) {
            Schema::create('enquiries', function (Blueprint $table) {
                $table->id();
                $table->string('enquiry_number', 32)->unique();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('vendor_id')->nullable()->constrained('vendors')->nullOnDelete();
                $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
                $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
                $table->string('title', 200);
                $table->text('description');
                $table->string('quantity', 100);
                $table->integer('target_budget')->nullable(); // In paise
                $table->string('city', 100);
                $table->string('pincode', 20)->nullable();
                $table->string('urgency', 30)->default('flexible'); // immediate, within_1_week, flexible
                $table->string('buyer_name', 120);
                $table->string('buyer_phone', 20);
                $table->string('buyer_email', 150)->nullable();
                $table->string('status', 20)->default('open')->index(); // open, quoted, closed, cancelled
                $table->unsignedInteger('quotes_count')->default(0);
                $table->timestamps();

                $table->index(['category_id', 'status']);
                $table->index(['vendor_id', 'status']);
                $table->index(['city', 'status']);
            });
        }

        // 2. Create quotes table
        if (!Schema::hasTable('quotes')) {
            Schema::create('quotes', function (Blueprint $table) {
                $table->id();
                $table->string('quote_number', 32)->unique();
                $table->foreignId('enquiry_id')->constrained('enquiries')->cascadeOnDelete();
                $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();
                $table->integer('quote_amount'); // In paise
                $table->unsignedSmallInteger('lead_time_days')->default(7);
                $table->date('valid_until')->nullable();
                $table->text('notes')->nullable();
                $table->string('status', 20)->default('sent')->index(); // sent, accepted, rejected
                $table->timestamp('accepted_at')->nullable();
                $table->timestamps();

                $table->index(['enquiry_id', 'status']);
                $table->index(['vendor_id', 'status']);
            });
        }

        // 3. Seed standard Enquiry categories if missing
        $now = now();
        $enquiryCategories = [
            ['name' => 'Wholesale Fabrics & Apparel', 'slug' => 'enquiry-wholesale-fabrics', 'sort_order' => 1],
            ['name' => 'Marble, Granite & Construction', 'slug' => 'enquiry-marble-granite', 'sort_order' => 2],
            ['name' => 'Industrial Hardware & Supplies', 'slug' => 'enquiry-industrial-hardware', 'sort_order' => 3],
            ['name' => 'Packaging Materials & Boxes', 'slug' => 'enquiry-packaging-materials', 'sort_order' => 4],
            ['name' => 'Custom Corporate Gifting', 'slug' => 'enquiry-corporate-gifts', 'sort_order' => 5],
            ['name' => 'Electronic Components & Bulk Tech', 'slug' => 'enquiry-electronic-components', 'sort_order' => 6],
        ];

        foreach ($enquiryCategories as $cat) {
            if (!DB::table('categories')->where('slug', $cat['slug'])->exists()) {
                DB::table('categories')->insert([
                    'name' => $cat['name'],
                    'slug' => $cat['slug'],
                    'vertical' => 'enquiry',
                    'is_active' => true,
                    'sort_order' => $cat['sort_order'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotes');
        Schema::dropIfExists('enquiries');
    }
};
