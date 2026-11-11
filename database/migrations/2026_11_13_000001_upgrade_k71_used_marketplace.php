<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        // 1. Make products.vendor_id nullable safely (so any K71 user can list Used goods without a merchant Vendor record)
        $this->makeForeignColumnNullable('products', 'vendor_id', 'vendors');

        // 2. Extend products table with dedicated Used Marketplace fields
        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                if (!Schema::hasColumn('products', 'user_id')) {
                    $table->unsignedBigInteger('user_id')->nullable()->after('vendor_id')->index();
                }
                if (!Schema::hasColumn('products', 'used_status')) {
                    $table->string('used_status', 25)->nullable()->after('status')->index();
                }
                if (!Schema::hasColumn('products', 'condition_grade')) {
                    $table->string('condition_grade', 30)->nullable()->after('used_status')->index();
                }
                if (!Schema::hasColumn('products', 'is_refurbished')) {
                    $table->boolean('is_refurbished')->default(false)->after('condition_grade');
                }
                if (!Schema::hasColumn('products', 'model_name')) {
                    $table->string('model_name', 120)->nullable()->after('brand');
                }
                if (!Schema::hasColumn('products', 'age_months')) {
                    $table->unsignedSmallInteger('age_months')->nullable()->after('model_name');
                }
                if (!Schema::hasColumn('products', 'purchase_year')) {
                    $table->unsignedSmallInteger('purchase_year')->nullable()->after('age_months');
                }
                if (!Schema::hasColumn('products', 'defect_notes')) {
                    $table->text('defect_notes')->nullable()->after('care_instructions');
                }
                if (!Schema::hasColumn('products', 'included_accessories')) {
                    $table->text('included_accessories')->nullable()->after('defect_notes');
                }
                if (!Schema::hasColumn('products', 'original_packaging')) {
                    $table->string('original_packaging', 60)->nullable()->after('included_accessories');
                }
                if (!Schema::hasColumn('products', 'reason_for_selling')) {
                    $table->string('reason_for_selling', 255)->nullable()->after('original_packaging');
                }
                if (!Schema::hasColumn('products', 'quantity_available')) {
                    $table->unsignedSmallInteger('quantity_available')->default(1)->after('total_stock');
                }
                if (!Schema::hasColumn('products', 'original_retail_price')) {
                    $table->unsignedBigInteger('original_retail_price')->nullable()->after('compare_at_price');
                }
                if (!Schema::hasColumn('products', 'min_offer_amount')) {
                    $table->unsignedBigInteger('min_offer_amount')->nullable()->after('base_price');
                }
                if (!Schema::hasColumn('products', 'city')) {
                    $table->string('city', 100)->nullable()->index();
                }
                if (!Schema::hasColumn('products', 'locality')) {
                    $table->string('locality', 120)->nullable();
                }
                if (!Schema::hasColumn('products', 'private_pickup_area')) {
                    $table->string('private_pickup_area', 255)->nullable();
                }
                if (!Schema::hasColumn('products', 'fulfillment_options')) {
                    $table->json('fulfillment_options')->nullable();
                }
                if (!Schema::hasColumn('products', 'reserved_for_user_id')) {
                    $table->unsignedBigInteger('reserved_for_user_id')->nullable()->index();
                }
                if (!Schema::hasColumn('products', 'reserved_at')) {
                    $table->timestamp('reserved_at')->nullable();
                }
                if (!Schema::hasColumn('products', 'sold_at')) {
                    $table->timestamp('sold_at')->nullable();
                }
                if (!Schema::hasColumn('products', 'views_count')) {
                    $table->unsignedInteger('views_count')->default(0);
                }
                if (!Schema::hasColumn('products', 'saves_count')) {
                    $table->unsignedInteger('saves_count')->default(0);
                }
                if (!Schema::hasColumn('products', 'duplicate_fingerprint')) {
                    $table->string('duplicate_fingerprint', 64)->nullable()->index();
                }
                if (!Schema::hasColumn('products', 'refreshed_at')) {
                    $table->timestamp('refreshed_at')->nullable();
                }
            });
        }

        // 3. Make offers.vendor_id nullable, widen price columns, and add negotiation metadata
        if (Schema::hasTable('offers')) {
            $this->makeForeignColumnNullable('offers', 'vendor_id', 'vendors');

            Schema::table('offers', function (Blueprint $table) use ($driver) {
                if (in_array($driver, ['mysql', 'mariadb', 'sqlite'], true)) {
                    $table->bigInteger('offer_amount')->change();
                    $table->bigInteger('counter_amount')->nullable()->change();
                }
                if (!Schema::hasColumn('offers', 'seller_id')) {
                    $table->unsignedBigInteger('seller_id')->nullable()->after('vendor_id')->index();
                }
                if (!Schema::hasColumn('offers', 'expires_at')) {
                    $table->timestamp('expires_at')->nullable()->after('responded_at');
                }
                if (!Schema::hasColumn('offers', 'accepted_at')) {
                    $table->timestamp('accepted_at')->nullable()->after('expires_at');
                }
                if (!Schema::hasColumn('offers', 'rejected_at')) {
                    $table->timestamp('rejected_at')->nullable()->after('accepted_at');
                }
                if (!Schema::hasColumn('offers', 'withdrawn_at')) {
                    $table->timestamp('withdrawn_at')->nullable()->after('rejected_at');
                }
                if (!Schema::hasColumn('offers', 'last_action_by')) {
                    $table->string('last_action_by', 20)->nullable()->after('status');
                }
                if (!Schema::hasColumn('offers', 'handoff_preference')) {
                    $table->string('handoff_preference', 40)->nullable()->after('message');
                }
            });
        }

        // 4. Used Seller Profiles (one unified public seller profile per K71 user)
        if (!Schema::hasTable('used_seller_profiles')) {
            Schema::create('used_seller_profiles', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
                $table->string('handle', 60)->unique();
                $table->string('display_name', 120);
                $table->text('bio')->nullable();
                $table->string('city', 100)->nullable()->index();
                $table->string('locality', 120)->nullable();
                $table->json('preferred_contact_methods')->nullable();
                $table->json('preferred_handoff_methods')->nullable();
                $table->boolean('show_sold_history')->default(true);
                $table->boolean('show_phone_publicly')->default(false);
                $table->decimal('avg_rating', 3, 2)->nullable();
                $table->unsignedInteger('reviews_count')->default(0);
                $table->unsignedInteger('sold_count')->default(0);
                $table->timestamps();
            });
        }

        // 5. Used Saved Searches
        if (!Schema::hasTable('used_saved_searches')) {
            Schema::create('used_saved_searches', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('name', 120)->nullable();
                $table->string('query', 200)->nullable();
                $table->string('city', 100)->nullable();
                $table->json('filters_json')->nullable();
                $table->timestamp('last_notified_at')->nullable();
                $table->timestamps();

                $table->index(['user_id', 'created_at']);
            });
        }

        // 6. Direct Peer Handover / Reservation Transactions (honest local handover tracking, no fake escrow)
        if (!Schema::hasTable('used_transactions')) {
            Schema::create('used_transactions', function (Blueprint $table) {
                $table->id();
                $table->string('transaction_number', 40)->unique();
                $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
                $table->foreignId('seller_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('buyer_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('offer_id')->nullable()->constrained('offers')->nullOnDelete();
                $table->unsignedBigInteger('agreed_price');
                $table->unsignedSmallInteger('quantity')->default(1);
                $table->string('fulfillment_method', 40)->default('local_pickup');
                $table->string('status', 25)->default('reserved')->index(); // reserved, completed, cancelled
                $table->text('private_pickup_location')->nullable();
                $table->text('seller_notes')->nullable();
                $table->text('buyer_notes')->nullable();
                $table->timestamp('reserved_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->timestamp('cancelled_at')->nullable();
                $table->timestamps();

                $table->index(['seller_id', 'status']);
                $table->index(['buyer_id', 'status']);
                $table->index(['product_id', 'status']);
            });
        }

        // 7. Listing-scoped Used Enquiry Threads & Messages (bridges with chat_conversations without breaking DM pair uniqueness)
        if (!Schema::hasTable('used_threads')) {
            Schema::create('used_threads', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
                $table->foreignId('seller_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('buyer_id')->constrained('users')->cascadeOnDelete();
                $table->unsignedBigInteger('chat_conversation_id')->nullable()->index();
                $table->text('shared_pickup_location')->nullable();
                $table->timestamp('pickup_shared_at')->nullable();
                $table->string('status', 20)->default('open')->index(); // open, locked, closed
                $table->timestamp('last_message_at')->nullable()->index();
                $table->timestamps();

                $table->unique(['product_id', 'buyer_id'], 'used_threads_product_buyer_unique');
                $table->index(['seller_id', 'last_message_at']);
                $table->index(['buyer_id', 'last_message_at']);
            });
        }

        if (!Schema::hasTable('used_thread_messages')) {
            Schema::create('used_thread_messages', function (Blueprint $table) {
                $table->id();
                $table->foreignId('used_thread_id')->constrained('used_threads')->cascadeOnDelete();
                $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
                $table->string('message_type', 25)->default('text'); // text, offer_event, location_share, system
                $table->text('body');
                $table->boolean('is_read')->default(false)->index();
                $table->timestamp('read_at')->nullable();
                $table->timestamps();

                $table->index(['used_thread_id', 'created_at']);
            });
        }

        // 8. Extend reviews for Used handover-verified reviews
        if (Schema::hasTable('reviews')) {
            Schema::table('reviews', function (Blueprint $table) {
                if (!Schema::hasColumn('reviews', 'used_transaction_id')) {
                    $table->unsignedBigInteger('used_transaction_id')->nullable()->after('project_contract_id')->index();
                }
                if (!Schema::hasColumn('reviews', 'reviewed_user_id')) {
                    $table->unsignedBigInteger('reviewed_user_id')->nullable()->after('used_transaction_id')->index();
                }
            });
        }

        // 9. Backfill existing Used products & offers cleanly without touching other verticals
        $this->backfillExistingUsedRecords();

        // 10. Seed comprehensive configurable Used categories, subcategories & category_fields
        $this->seedConfigurableUsedTaxonomy();
    }

    private function makeForeignColumnNullable(string $tableName, string $column, string $referencedTable): void
    {
        if (!Schema::hasTable($tableName) || !Schema::hasColumn($tableName, $column)) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            $dbName = Schema::getConnection()->getDatabaseName();
            $fkNames = DB::table('information_schema.KEY_COLUMN_USAGE')
                ->where('TABLE_SCHEMA', $dbName)
                ->where('TABLE_NAME', $tableName)
                ->where('COLUMN_NAME', $column)
                ->whereNotNull('REFERENCED_TABLE_NAME')
                ->pluck('CONSTRAINT_NAME')
                ->unique();

            foreach ($fkNames as $fkName) {
                DB::statement("ALTER TABLE `{$tableName}` DROP FOREIGN KEY `{$fkName}`");
            }

            DB::statement("ALTER TABLE `{$tableName}` MODIFY `{$column}` BIGINT UNSIGNED NULL");

            Schema::table($tableName, function (Blueprint $table) use ($column, $referencedTable) {
                $table->foreign($column)->references('id')->on($referencedTable)->nullOnDelete();
            });

            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($column) {
            $table->unsignedBigInteger($column)->nullable()->change();
        });
    }

    private function backfillExistingUsedRecords(): void
    {
        if (!Schema::hasTable('products')) {
            return;
        }

        $usedProducts = DB::table('products')->where('vertical', 'used')->get();
        $vendorUsers = DB::table('vendors')->pluck('user_id', 'id');

        foreach ($usedProducts as $p) {
            $cf = json_decode($p->custom_fields ?? '{}', true) ?: [];
            $ownerUserId = $p->user_id ?: ($cf['user_id'] ?? ($p->vendor_id ? ($vendorUsers[$p->vendor_id] ?? null) : null));
            $usedStatus = $p->used_status ?: ($p->is_sold ? 'sold' : ($p->status === 'active' ? 'active' : ($p->status ?: 'draft')));
            $rawCond = strtolower((string) ($p->condition_grade ?: ($cf['condition'] ?? '')));
            $grade = match (true) {
                str_contains($rawCond, 'new') && !str_contains($rawCond, 'like') => 'new_unused',
                str_contains($rawCond, 'like') || str_contains($rawCond, 'mint') => 'like_new',
                str_contains($rawCond, 'parts') || str_contains($rawCond, 'repair') => 'for_parts',
                str_contains($rawCond, 'fair') || str_contains($rawCond, 'heavy') || str_contains($rawCond, 'heavily') || str_contains($rawCond, 'well') => 'fair',
                default => 'good',
            };

            $city = $p->city ?: ($cf['location'] ?? ($cf['city'] ?? null));

            DB::table('products')->where('id', $p->id)->update([
                'user_id' => $ownerUserId,
                'used_status' => $usedStatus,
                'condition_grade' => $grade,
                'city' => $city,
                'refreshed_at' => $p->refreshed_at ?: $p->updated_at,
            ]);
        }

        if (Schema::hasTable('offers')) {
            $offers = DB::table('offers')->whereNull('seller_id')->get();
            foreach ($offers as $o) {
                $sellerId = null;
                if ($o->vendor_id && isset($vendorUsers[$o->vendor_id])) {
                    $sellerId = $vendorUsers[$o->vendor_id];
                } else {
                    $prod = DB::table('products')->where('id', $o->product_id)->first();
                    $sellerId = $prod?->user_id ?: ($prod?->vendor_id ? ($vendorUsers[$prod->vendor_id] ?? null) : null);
                }
                if ($sellerId) {
                    DB::table('offers')->where('id', $o->id)->update(['seller_id' => $sellerId]);
                }
            }
        }
    }

    private function seedConfigurableUsedTaxonomy(): void
    {
        if (!Schema::hasTable('categories')) {
            return;
        }

        $now = now();
        $taxonomy = [
            'Mobiles & Electronics' => [
                'slug' => 'used-mobiles-tablets',
                'icon' => '📱',
                'description' => 'Pre-owned smartphones, tablets, wearables, audio gear & cameras.',
                'sort_order' => 1,
                'children' => [
                    ['name' => 'Smartphones', 'slug' => 'used-smartphones'],
                    ['name' => 'Tablets & E-Readers', 'slug' => 'used-tablets'],
                    ['name' => 'Audio, Cameras & Gadgets', 'slug' => 'used-audio-cameras'],
                ],
                'fields' => [
                    ['name' => 'storage_capacity', 'label' => 'Storage Capacity', 'field_type' => 'select', 'options' => ['32 GB', '64 GB', '128 GB', '256 GB', '512 GB', '1 TB', 'N/A'], 'placeholder' => 'Select storage', 'is_required' => false, 'sort_order' => 10],
                    ['name' => 'battery_health', 'label' => 'Battery Health / Condition', 'field_type' => 'text', 'options' => null, 'placeholder' => 'e.g. 88% capacity or holds full-day charge', 'is_required' => false, 'sort_order' => 11],
                    ['name' => 'bill_available', 'label' => 'Purchase Invoice / Bill Available', 'field_type' => 'select', 'options' => ['Yes', 'No'], 'placeholder' => 'Invoice available?', 'is_required' => false, 'sort_order' => 12],
                ],
            ],
            'Computers & Accessories' => [
                'slug' => 'used-laptops-electronics',
                'icon' => '💻',
                'description' => 'Used laptops, desktops, monitors, GPUs & peripherals.',
                'sort_order' => 2,
                'children' => [
                    ['name' => 'Laptops & Notebooks', 'slug' => 'used-laptops'],
                    ['name' => 'Monitors & Desktops', 'slug' => 'used-desktops-monitors'],
                    ['name' => 'Components & Peripherals', 'slug' => 'used-pc-accessories'],
                ],
                'fields' => [
                    ['name' => 'processor', 'label' => 'Processor / Chipset', 'field_type' => 'text', 'options' => null, 'placeholder' => 'e.g. Apple M2, Intel Core i5 12th Gen, Ryzen 5', 'is_required' => false, 'sort_order' => 10],
                    ['name' => 'ram_size', 'label' => 'RAM Memory', 'field_type' => 'select', 'options' => ['4 GB', '8 GB', '16 GB', '32 GB', '64 GB+'], 'placeholder' => 'Select RAM', 'is_required' => false, 'sort_order' => 11],
                    ['name' => 'storage_drive', 'label' => 'SSD / Storage', 'field_type' => 'text', 'options' => null, 'placeholder' => 'e.g. 512 GB NVMe SSD', 'is_required' => false, 'sort_order' => 12],
                ],
            ],
            'Vehicles & Accessories' => [
                'slug' => 'used-vehicles-bikes',
                'icon' => '🛵',
                'description' => 'Two-wheelers, cars, commercial vehicles, helmets & spares.',
                'sort_order' => 3,
                'children' => [
                    ['name' => 'Motorcycles & Scooters', 'slug' => 'used-motorcycles-scooters'],
                    ['name' => 'Cars & Four-Wheelers', 'slug' => 'used-cars'],
                    ['name' => 'Helmets & Vehicle Spares', 'slug' => 'used-vehicle-accessories'],
                ],
                'fields' => [
                    ['name' => 'registration_year', 'label' => 'Registration Year', 'field_type' => 'number', 'options' => null, 'placeholder' => 'e.g. 2021', 'is_required' => false, 'sort_order' => 10],
                    ['name' => 'km_driven', 'label' => 'Kilometers Driven', 'field_type' => 'number', 'options' => null, 'placeholder' => 'e.g. 14500', 'is_required' => false, 'sort_order' => 11],
                    ['name' => 'fuel_type', 'label' => 'Fuel / Powertrain', 'field_type' => 'select', 'options' => ['Petrol', 'Diesel', 'Electric', 'CNG / Hybrid', 'N/A'], 'placeholder' => 'Fuel type', 'is_required' => false, 'sort_order' => 12],
                    ['name' => 'owner_number', 'label' => 'Ownership Count', 'field_type' => 'select', 'options' => ['1st Owner', '2nd Owner', '3rd+ Owner'], 'placeholder' => 'Owner count', 'is_required' => false, 'sort_order' => 13],
                ],
            ],
            'Home & Furniture' => [
                'slug' => 'used-furniture',
                'icon' => '🛋️',
                'description' => 'Beds, sofas, study desks, wardrobes & home decor.',
                'sort_order' => 4,
                'children' => [
                    ['name' => 'Living Room & Sofas', 'slug' => 'used-sofas-living'],
                    ['name' => 'Office Desks & Chairs', 'slug' => 'used-office-furniture'],
                    ['name' => 'Beds, Tables & Storage', 'slug' => 'used-beds-tables'],
                ],
                'fields' => [
                    ['name' => 'primary_material', 'label' => 'Primary Material', 'field_type' => 'select', 'options' => ['Solid Wood (Sheesham/Teak)', 'Engineered Wood', 'Metal / Steel', 'Fabric / Leatherette', 'Marble / Stone'], 'placeholder' => 'Material', 'is_required' => false, 'sort_order' => 10],
                    ['name' => 'dimensions', 'label' => 'Approximate Dimensions', 'field_type' => 'text', 'options' => null, 'placeholder' => 'e.g. 6ft x 3ft x 30in', 'is_required' => false, 'sort_order' => 11],
                ],
            ],
            'Appliances' => [
                'slug' => 'used-appliances',
                'icon' => '🧊',
                'description' => 'Refrigerators, washing machines, ACs, kitchen & home appliances.',
                'sort_order' => 5,
                'children' => [
                    ['name' => 'ACs & Coolers', 'slug' => 'used-ac-coolers'],
                    ['name' => 'Fridges & Washing Machines', 'slug' => 'used-fridges-washers'],
                    ['name' => 'Kitchen & Small Appliances', 'slug' => 'used-kitchen-appliances'],
                ],
                'fields' => [
                    ['name' => 'capacity_spec', 'label' => 'Capacity / Tonnage', 'field_type' => 'text', 'options' => null, 'placeholder' => 'e.g. 1.5 Ton 5-Star, 260L, 7kg Front Load', 'is_required' => false, 'sort_order' => 10],
                    ['name' => 'working_status_note', 'label' => 'Testing & Cooling / Functional Status', 'field_type' => 'text', 'options' => null, 'placeholder' => 'e.g. Available for live power-on test at pickup', 'is_required' => false, 'sort_order' => 11],
                ],
            ],
            'Fashion & Accessories' => [
                'slug' => 'used-fashion-accessories',
                'icon' => '🧥',
                'description' => 'Pre-loved jackets, watches, sneakers, bags & traditional wear.',
                'sort_order' => 6,
                'children' => [
                    ['name' => 'Footwear & Sneakers', 'slug' => 'used-footwear'],
                    ['name' => 'Watches & Bags', 'slug' => 'used-watches-bags'],
                ],
                'fields' => [
                    ['name' => 'apparel_size', 'label' => 'Size (EU/UK/Apparel)', 'field_type' => 'text', 'options' => null, 'placeholder' => 'e.g. M, UK 9, 40mm Dial', 'is_required' => false, 'sort_order' => 10],
                ],
            ],
            'Books & Education' => [
                'slug' => 'used-books-education',
                'icon' => '📚',
                'description' => 'Textbooks, competitive exam guides, novels & study gear.',
                'sort_order' => 7,
                'children' => [
                    ['name' => 'Competitive & College Books', 'slug' => 'used-study-books'],
                    ['name' => 'Fiction & Non-Fiction', 'slug' => 'used-novels-books'],
                ],
                'fields' => [
                    ['name' => 'author_publisher', 'label' => 'Author / Publisher & Edition', 'field_type' => 'text', 'options' => null, 'placeholder' => 'e.g. HC Verma Vol 1 & 2 (2024 Edition)', 'is_required' => false, 'sort_order' => 10],
                    ['name' => 'markings_status', 'label' => 'Page Markings / Highlighting', 'field_type' => 'select', 'options' => ['Clean / Unmarked Pages', 'Light Pencil Notes', 'Highlighted / Annotated'], 'placeholder' => 'Condition of pages', 'is_required' => false, 'sort_order' => 11],
                ],
            ],
            'Sports & Hobbies' => [
                'slug' => 'used-sports-hobbies',
                'icon' => '🎸',
                'description' => 'Musical instruments, gym weights, cycles & sports gear.',
                'sort_order' => 8,
                'children' => [
                    ['name' => 'Musical Instruments', 'slug' => 'used-musical-instruments'],
                    ['name' => 'Bicycles & Fitness Equipment', 'slug' => 'used-bicycles-fitness'],
                ],
                'fields' => [
                    ['name' => 'size_or_weight', 'label' => 'Size / Weight / Frame', 'field_type' => 'text', 'options' => null, 'placeholder' => 'e.g. 27.5T Alloy Frame / 20kg Plate Set', 'is_required' => false, 'sort_order' => 10],
                ],
            ],
            'Tools & Equipment' => [
                'slug' => 'used-tools-equipment',
                'icon' => '🛠️',
                'description' => 'Power tools, workshop machines, stone tools & test equipment.',
                'sort_order' => 9,
                'children' => [
                    ['name' => 'Power & Hand Tools', 'slug' => 'used-power-tools'],
                    ['name' => 'Workshop & Measuring Gear', 'slug' => 'used-workshop-equipment'],
                ],
                'fields' => [
                    ['name' => 'power_rating', 'label' => 'Voltage / Power Rating', 'field_type' => 'text', 'options' => null, 'placeholder' => 'e.g. 220V Single Phase, 18V Cordless', 'is_required' => false, 'sort_order' => 10],
                ],
            ],
            'Other Used Goods' => [
                'slug' => 'used-other-goods',
                'icon' => '📦',
                'description' => 'Other legitimate pre-owned items & household goods.',
                'sort_order' => 10,
                'children' => [],
                'fields' => [],
            ],
        ];

        foreach ($taxonomy as $name => $meta) {
            $parent = DB::table('categories')->where('slug', $meta['slug'])->first();

            if (!$parent) {
                $parentId = DB::table('categories')->insertGetId([
                    'parent_id' => null,
                    'vertical' => 'used',
                    'name' => $name,
                    'slug' => $meta['slug'],
                    'description' => $meta['description'],
                    'icon' => $meta['icon'],
                    'is_active' => true,
                    'sort_order' => $meta['sort_order'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            } else {
                $parentId = $parent->id;
                DB::table('categories')->where('id', $parentId)->update([
                    'vertical' => 'used',
                    'icon' => $parent->icon ?: $meta['icon'],
                    'sort_order' => $meta['sort_order'],
                ]);
            }

            foreach ($meta['children'] as $idx => $child) {
                $existingChild = DB::table('categories')->where('slug', $child['slug'])->first();
                if (!$existingChild) {
                    DB::table('categories')->insert([
                        'parent_id' => $parentId,
                        'vertical' => 'used',
                        'name' => $child['name'],
                        'slug' => $child['slug'],
                        'description' => $child['name'],
                        'is_active' => true,
                        'sort_order' => $idx + 1,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                } else {
                    DB::table('categories')->where('id', $existingChild->id)->update([
                        'parent_id' => $parentId,
                        'vertical' => 'used',
                    ]);
                }
            }

            if (Schema::hasTable('category_fields')) {
                foreach ($meta['fields'] as $field) {
                    $exists = DB::table('category_fields')
                        ->where('category_id', $parentId)
                        ->where('name', $field['name'])
                        ->exists();

                    if (!$exists) {
                        DB::table('category_fields')->insert([
                            'category_id' => $parentId,
                            'vertical' => 'used',
                            'name' => $field['name'],
                            'label' => $field['label'],
                            'field_type' => $field['field_type'],
                            'options' => isset($field['options']) ? json_encode($field['options']) : null,
                            'placeholder' => $field['placeholder'] ?? null,
                            'help_text' => null,
                            'default_value' => null,
                            'is_required' => $field['is_required'] ?? false,
                            'sort_order' => $field['sort_order'] ?? 10,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ]);
                    }
                }
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('used_thread_messages');
        Schema::dropIfExists('used_threads');
        Schema::dropIfExists('used_transactions');
        Schema::dropIfExists('used_saved_searches');
        Schema::dropIfExists('used_seller_profiles');

        if (Schema::hasTable('reviews')) {
            Schema::table('reviews', function (Blueprint $table) {
                $cols = array_intersect(['used_transaction_id', 'reviewed_user_id'], Schema::getColumnListing('reviews'));
                if ($cols) {
                    $table->dropColumn($cols);
                }
            });
        }

        if (Schema::hasTable('offers')) {
            Schema::table('offers', function (Blueprint $table) {
                $cols = array_intersect([
                    'seller_id', 'expires_at', 'accepted_at', 'rejected_at',
                    'withdrawn_at', 'last_action_by', 'handoff_preference',
                ], Schema::getColumnListing('offers'));
                if ($cols) {
                    $table->dropColumn($cols);
                }
            });
        }

        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                $cols = array_intersect([
                    'user_id', 'used_status', 'condition_grade', 'is_refurbished',
                    'model_name', 'age_months', 'purchase_year', 'defect_notes',
                    'included_accessories', 'original_packaging', 'reason_for_selling',
                    'quantity_available', 'original_retail_price', 'min_offer_amount',
                    'city', 'locality', 'private_pickup_area', 'fulfillment_options',
                    'reserved_for_user_id', 'reserved_at', 'sold_at', 'views_count',
                    'saves_count', 'duplicate_fingerprint', 'refreshed_at',
                ], Schema::getColumnListing('products'));
                if ($cols) {
                    $table->dropColumn($cols);
                }
            });
        }
    }
};

