<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run migrations for K71 Phase 7: Services & Bookings (services.k71.in).
     */
    public function up(): void
    {
        // 1. Add service-specific columns to vendor_settings
        if (Schema::hasTable('vendor_settings')) {
            Schema::table('vendor_settings', function (Blueprint $table) {
                if (!Schema::hasColumn('vendor_settings', 'service_experience_years')) {
                    $table->unsignedSmallInteger('service_experience_years')->nullable()->after('cod_enabled');
                }
                if (!Schema::hasColumn('vendor_settings', 'service_radius_km')) {
                    $table->unsignedSmallInteger('service_radius_km')->default(25)->after('service_experience_years');
                }
                if (!Schema::hasColumn('vendor_settings', 'service_working_hours')) {
                    $table->string('service_working_hours', 100)->default('09:00 AM - 08:00 PM')->after('service_radius_km');
                }
            });
        }

        // 2. Create bookings table
        if (!Schema::hasTable('bookings')) {
            Schema::create('bookings', function (Blueprint $table) {
                $table->id();
                $table->string('booking_number', 32)->unique();
                $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
                $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('customer_name', 120);
                $table->string('customer_phone', 20);
                $table->string('customer_email', 150)->nullable();
                $table->text('service_address');
                $table->date('booking_date');
                $table->string('booking_slot', 60); // e.g. "10:00 AM - 12:00 PM"
                $table->integer('total_amount')->default(0); // In paise
                $table->string('status', 20)->default('pending')->index(); // pending, confirmed, completed, cancelled
                $table->text('notes')->nullable();
                $table->text('vendor_notes')->nullable();
                $table->text('cancelled_reason')->nullable();
                $table->timestamp('confirmed_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->timestamp('cancelled_at')->nullable();
                $table->timestamps();

                $table->index(['vendor_id', 'status']);
                $table->index(['product_id', 'booking_date']);
                $table->index(['booking_date', 'status']);
            });
        }

        // 3. Seed initial Services categories
        $now = now();
        $serviceCategories = [
            ['name' => 'Home Repairs & Maintenance', 'slug' => 'services-home-repairs', 'sort_order' => 1],
            ['name' => 'Electricians & Plumbers', 'slug' => 'services-electricians-plumbers', 'sort_order' => 2],
            ['name' => 'AC & Appliance Repair', 'slug' => 'services-appliance-repair', 'sort_order' => 3],
            ['name' => 'Cleaning & Pest Control', 'slug' => 'services-cleaning-pest', 'sort_order' => 4],
            ['name' => 'Beauty & Salon at Home', 'slug' => 'services-salon-grooming', 'sort_order' => 5],
            ['name' => 'Photography & Events', 'slug' => 'services-photography-events', 'sort_order' => 6],
            ['name' => 'Tutors & Lessons', 'slug' => 'services-tutors-lessons', 'sort_order' => 7],
        ];

        foreach ($serviceCategories as $cat) {
            if (!DB::table('categories')->where('slug', $cat['slug'])->exists()) {
                DB::table('categories')->insert([
                    'name' => $cat['name'],
                    'slug' => $cat['slug'],
                    'vertical' => 'services',
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
        Schema::dropIfExists('bookings');

        if (Schema::hasTable('vendor_settings')) {
            Schema::table('vendor_settings', function (Blueprint $table) {
                $columns = ['service_experience_years', 'service_radius_km', 'service_working_hours'];
                $table->dropColumn(array_intersect($columns, Schema::getColumnListing('vendor_settings')));
            });
        }
    }
};
