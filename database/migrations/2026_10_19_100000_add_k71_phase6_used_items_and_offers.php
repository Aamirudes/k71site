<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run migrations for K71 Phase 6: Used Items & Make-an-Offer.
     */
    public function up(): void
    {
        // 0. Ensure vendors table has city and contact columns
        if (Schema::hasTable('vendors')) {
            Schema::table('vendors', function (Blueprint $table) {
                if (!Schema::hasColumn('vendors', 'city')) {
                    $table->string('city', 100)->nullable()->index();
                }
                if (!Schema::hasColumn('vendors', 'state')) {
                    $table->string('state', 100)->nullable();
                }
                if (!Schema::hasColumn('vendors', 'pincode')) {
                    $table->string('pincode', 20)->nullable();
                }
                if (!Schema::hasColumn('vendors', 'address')) {
                    $table->text('address')->nullable();
                }
                if (!Schema::hasColumn('vendors', 'phone')) {
                    $table->string('phone', 20)->nullable();
                }
                if (!Schema::hasColumn('vendors', 'email')) {
                    $table->string('email', 150)->nullable();
                }
            });
        }

        // 1. Add used-item flags to products table
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'is_negotiable')) {
                $table->boolean('is_negotiable')->default(true)->after('listing_type');
            }
            if (!Schema::hasColumn('products', 'is_sold')) {
                $table->boolean('is_sold')->default(false)->after('is_negotiable')->index();
            }
        });

        // 2. Create offers table for negotiations & buyer offers
        if (!Schema::hasTable('offers')) {
            Schema::create('offers', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
                $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('buyer_name', 120);
                $table->string('buyer_email', 150)->nullable();
                $table->string('buyer_phone', 20);
                $table->integer('offer_amount'); // In paise (e.g. 500000 = ₹5,000)
                $table->text('message')->nullable();
                $table->string('status', 20)->default('pending')->index(); // pending, accepted, rejected, countered
                $table->integer('counter_amount')->nullable(); // In paise
                $table->text('vendor_notes')->nullable();
                $table->timestamp('responded_at')->nullable();
                $table->timestamps();

                $table->index(['vendor_id', 'status']);
                $table->index(['product_id', 'status']);
            });
        }

        // 3. Seed initial Used-Item categories if not present
        $now = now();
        $usedCategories = [
            ['name' => 'Mobiles & Tablets', 'slug' => 'used-mobiles-tablets', 'sort_order' => 1],
            ['name' => 'Laptops & Electronics', 'slug' => 'used-laptops-electronics', 'sort_order' => 2],
            ['name' => 'Vehicles & Two-Wheelers', 'slug' => 'used-vehicles-bikes', 'sort_order' => 3],
            ['name' => 'Pre-Owned Furniture', 'slug' => 'used-furniture', 'sort_order' => 4],
            ['name' => 'Vintage & Collectibles', 'slug' => 'used-vintage-collectibles', 'sort_order' => 5],
            ['name' => 'Home Appliances', 'slug' => 'used-appliances', 'sort_order' => 6],
        ];

        foreach ($usedCategories as $cat) {
            if (!DB::table('categories')->where('slug', $cat['slug'])->exists()) {
                DB::table('categories')->insert([
                    'name' => $cat['name'],
                    'slug' => $cat['slug'],
                    'vertical' => 'used',
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
        Schema::dropIfExists('offers');

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['is_negotiable', 'is_sold']);
        });
    }
};
