<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations for K71 Phase 4: Listing Types & Category Fields.
     */
    public function up(): void
    {
        // 1. Add vertical column to categories
        if (!Schema::hasColumn('categories', 'vertical')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->string('vertical', 30)->default('marketplace')->after('parent_id')->index();
            });
        }

        // 2. Add vertical, listing_type, and custom_fields to products
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'vertical')) {
                $table->string('vertical', 30)->default('marketplace')->after('category_id')->index();
            }
            if (!Schema::hasColumn('products', 'listing_type')) {
                $table->string('listing_type', 30)->default('purchase')->after('vertical')->index();
            }
            if (!Schema::hasColumn('products', 'custom_fields')) {
                $table->json('custom_fields')->nullable()->after('listing_type');
            }
        });

        // 3. Create category_fields table
        if (!Schema::hasTable('category_fields')) {
            Schema::create('category_fields', function (Blueprint $table) {
                $table->id();
                $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
                $table->string('vertical', 30)->nullable()->index();
                $table->string('name', 50); // programmatic field key
                $table->string('label', 100); // UI display label
                $table->string('field_type', 30)->default('text'); // text, number, select, textarea, checkbox
                $table->json('options')->nullable(); // dropdown or radio options
                $table->string('placeholder', 150)->nullable();
                $table->string('help_text', 255)->nullable();
                $table->string('default_value', 100)->nullable();
                $table->boolean('is_required')->default(false);
                $table->integer('sort_order')->default(0);
                $table->timestamps();

                $table->index(['vertical', 'sort_order']);
            });
        }

        // 4. Seed categories for the 5 verticals
        $verticalCategories = [
            'food' => [
                'name' => 'Food & Dining',
                'children' => ['North Indian', 'South Indian', 'Biryani & Rice', 'Fast Food & Snacks', 'Desserts & Sweets', 'Beverages & Juices', 'Bakery & Cakes']
            ],
            'services' => [
                'name' => 'Professional Services',
                'children' => ['Home Repairs & Electrician', 'Cleaning & Deep Sanitization', 'Salon & Grooming', 'Tailoring & Alterations', 'Photography & Events', 'IT & Digital Work']
            ],
            'used' => [
                'name' => 'Pre-Owned & Used Goods',
                'children' => ['Used Mobiles & Tablets', 'Pre-owned Laptops & PCs', 'Second-hand Furniture', 'Pre-owned Bikes & Scooters', 'Home Appliances', 'Sports & Fitness']
            ],
            'enquiry' => [
                'name' => 'Bulk & Custom Enquiries',
                'children' => ['Wholesale Fabrics & Apparel', 'Marble, Granite & Tiles', 'Industrial Hardware & Tools', 'Packaging Materials', 'Custom Corporate Gifts']
            ],
        ];

        foreach ($verticalCategories as $vertKey => $vData) {
            $existing = DB::table('categories')->where('slug', Str::slug($vData['name']))->first();
            $parentId = $existing?->id;

            if (!$parentId) {
                $parentId = DB::table('categories')->insertGetId([
                    'parent_id' => null,
                    'vertical' => $vertKey,
                    'name' => $vData['name'],
                    'slug' => Str::slug($vData['name']),
                    'description' => "All {$vData['name']} on K71",
                    'is_active' => true,
                    'sort_order' => 10,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                DB::table('categories')->where('id', $parentId)->update(['vertical' => $vertKey]);
            }

            foreach ($vData['children'] as $idx => $childName) {
                $childSlug = Str::slug($vertKey . '-' . $childName);
                if (!DB::table('categories')->where('slug', $childSlug)->exists()) {
                    DB::table('categories')->insert([
                        'parent_id' => $parentId,
                        'vertical' => $vertKey,
                        'name' => $childName,
                        'slug' => $childSlug,
                        'description' => $childName,
                        'is_active' => true,
                        'sort_order' => $idx + 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        // 5. Seed standard category_fields for verticals
        $standardFields = [
            // FOOD VERTICAL
            [
                'vertical' => 'food',
                'name' => 'dietary_type',
                'label' => 'Dietary Preference',
                'field_type' => 'select',
                'options' => json_encode(['Veg (Vegetarian)', 'Non-Veg', 'Contains Egg', 'Vegan', 'Jain Friendly']),
                'placeholder' => 'Select dietary option',
                'help_text' => 'Indicate whether this item is vegetarian, non-veg, or vegan',
                'default_value' => 'Veg (Vegetarian)',
                'is_required' => true,
                'sort_order' => 1,
            ],
            [
                'vertical' => 'food',
                'name' => 'cuisine',
                'label' => 'Cuisine Style',
                'field_type' => 'text',
                'options' => null,
                'placeholder' => 'e.g. Mughlai, Chettinad, Continental, Street Food',
                'help_text' => 'Cuisine or regional origin of this dish',
                'default_value' => null,
                'is_required' => false,
                'sort_order' => 2,
            ],
            [
                'vertical' => 'food',
                'name' => 'prep_time_minutes',
                'label' => 'Preparation Time (Minutes)',
                'field_type' => 'number',
                'options' => null,
                'placeholder' => 'e.g. 25',
                'help_text' => 'Average preparation or cooking time in minutes',
                'default_value' => '20',
                'is_required' => false,
                'sort_order' => 3,
            ],
            [
                'vertical' => 'food',
                'name' => 'spice_level',
                'label' => 'Spice Level',
                'field_type' => 'select',
                'options' => json_encode(['Mild', 'Medium Spicy', 'Spicy', 'Extra Hot']),
                'placeholder' => 'Select spice level',
                'help_text' => 'Spiciness rating for customers',
                'default_value' => 'Medium Spicy',
                'is_required' => false,
                'sort_order' => 4,
            ],
            [
                'vertical' => 'food',
                'name' => 'allergens',
                'label' => 'Allergen Information',
                'field_type' => 'text',
                'options' => null,
                'placeholder' => 'e.g. Peanuts, Dairy, Gluten, Soy',
                'help_text' => 'List any common food allergens',
                'default_value' => null,
                'is_required' => false,
                'sort_order' => 5,
            ],

            // USED ITEMS VERTICAL
            [
                'vertical' => 'used',
                'name' => 'condition',
                'label' => 'Item Condition',
                'field_type' => 'select',
                'options' => json_encode(['Brand New (Open Box / Unused)', 'Like New (Mint Condition)', 'Gently Used (Minor Signs of Wear)', 'Well Used (Fully Functional)', 'For Parts / Needs Repair']),
                'placeholder' => 'Select current condition',
                'help_text' => 'Be honest about cosmetic and working condition',
                'default_value' => 'Like New (Mint Condition)',
                'is_required' => true,
                'sort_order' => 1,
            ],
            [
                'vertical' => 'used',
                'name' => 'age_months',
                'label' => 'Approximate Age (Months)',
                'field_type' => 'number',
                'options' => null,
                'placeholder' => 'e.g. 8',
                'help_text' => 'How many months have you owned this item?',
                'default_value' => null,
                'is_required' => false,
                'sort_order' => 2,
            ],
            [
                'vertical' => 'used',
                'name' => 'original_box_included',
                'label' => 'Original Packaging Included',
                'field_type' => 'select',
                'options' => json_encode(['Yes, Original Box & Manuals', 'Original Box Only', 'No Packaging (Item Only)']),
                'placeholder' => 'Packaging availability',
                'help_text' => 'Does it come with original manufacturer box?',
                'default_value' => 'No Packaging (Item Only)',
                'is_required' => false,
                'sort_order' => 3,
            ],
            [
                'vertical' => 'used',
                'name' => 'warranty_status',
                'label' => 'Warranty Status',
                'field_type' => 'select',
                'options' => json_encode(['Brand Warranty Active', 'Vendor / Seller Warranty', 'Out of Warranty']),
                'placeholder' => 'Select warranty status',
                'help_text' => 'Does this item still have an active warranty?',
                'default_value' => 'Out of Warranty',
                'is_required' => false,
                'sort_order' => 4,
            ],
            [
                'vertical' => 'used',
                'name' => 'defect_notes',
                'label' => 'Scratches, Flaws, or Notes',
                'field_type' => 'textarea',
                'options' => null,
                'placeholder' => 'Describe any visible marks, scratches, or replaced parts',
                'help_text' => 'Clear disclosure builds buyer trust and avoids disputes',
                'default_value' => null,
                'is_required' => false,
                'sort_order' => 5,
            ],

            // SERVICES VERTICAL
            [
                'vertical' => 'services',
                'name' => 'service_duration_minutes',
                'label' => 'Estimated Service Duration (Minutes)',
                'field_type' => 'number',
                'options' => null,
                'placeholder' => 'e.g. 60',
                'help_text' => 'Typical duration to complete this service in minutes',
                'default_value' => '60',
                'is_required' => false,
                'sort_order' => 1,
            ],
            [
                'vertical' => 'services',
                'name' => 'service_mode',
                'label' => 'Service Delivery Mode',
                'field_type' => 'select',
                'options' => json_encode(['Doorstep / Customer Location', 'In-Store / Provider Studio', 'Remote / Online Call']),
                'placeholder' => 'Select location of service',
                'help_text' => 'Where will the service be delivered?',
                'default_value' => 'Doorstep / Customer Location',
                'is_required' => true,
                'sort_order' => 2,
            ],
            [
                'vertical' => 'services',
                'name' => 'experience_years',
                'label' => 'Provider Experience (Years)',
                'field_type' => 'number',
                'options' => null,
                'placeholder' => 'e.g. 5',
                'help_text' => 'Years of professional experience in this trade',
                'default_value' => null,
                'is_required' => false,
                'sort_order' => 3,
            ],
            [
                'vertical' => 'services',
                'name' => 'included_features',
                'label' => 'What is Included in This Service',
                'field_type' => 'textarea',
                'options' => null,
                'placeholder' => 'e.g. Consumables included, post-service cleanup, 30-day rework warranty',
                'help_text' => 'Clear breakdown of scope of work',
                'default_value' => null,
                'is_required' => false,
                'sort_order' => 4,
            ],

            // ENQUIRY VERTICAL
            [
                'vertical' => 'enquiry',
                'name' => 'minimum_order_quantity',
                'label' => 'Minimum Order Quantity (MOQ)',
                'field_type' => 'number',
                'options' => null,
                'placeholder' => 'e.g. 50',
                'help_text' => 'Minimum units or volume required for wholesale enquiry',
                'default_value' => '10',
                'is_required' => true,
                'sort_order' => 1,
            ],
            [
                'vertical' => 'enquiry',
                'name' => 'lead_time_days',
                'label' => 'Estimated Production Lead Time (Days)',
                'field_type' => 'number',
                'options' => null,
                'placeholder' => 'e.g. 14',
                'help_text' => 'Days required to manufacture or supply bulk orders',
                'default_value' => '7',
                'is_required' => false,
                'sort_order' => 2,
            ],
            [
                'vertical' => 'enquiry',
                'name' => 'sample_available',
                'label' => 'Sample Availability',
                'field_type' => 'select',
                'options' => json_encode(['Free Sample on Request', 'Paid Sample Available', 'No Samples Available']),
                'placeholder' => 'Can buyer inspect a sample?',
                'help_text' => 'Sample policy before bulk order',
                'default_value' => 'Paid Sample Available',
                'is_required' => false,
                'sort_order' => 3,
            ],
            [
                'vertical' => 'enquiry',
                'name' => 'certifications',
                'label' => 'Quality Certifications / BIS / ISO',
                'field_type' => 'text',
                'options' => null,
                'placeholder' => 'e.g. ISO 9001:2015, CE, BIS Hallmark',
                'help_text' => 'Quality compliance standards met',
                'default_value' => null,
                'is_required' => false,
                'sort_order' => 4,
            ],
        ];

        foreach ($standardFields as $f) {
            DB::table('category_fields')->updateOrInsert(
                [
                    'vertical' => $f['vertical'],
                    'name' => $f['name'],
                ],
                array_merge($f, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('category_fields');

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['vertical', 'listing_type', 'custom_fields']);
        });

        if (Schema::hasColumn('categories', 'vertical')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->dropColumn('vertical');
            });
        }
    }
};
