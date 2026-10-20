<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. shorts table
        Schema::create('shorts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('video_url', 500);
            $table->string('video_path', 500)->nullable();
            $table->string('thumb_url', 500)->nullable();
            $table->string('thumb_b_url', 500)->nullable();
            $table->string('ratio', 10)->default('9:16');
            $table->unsignedInteger('duration')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_boosted')->default(false);
            $table->boolean('is_manual_boost')->default(false);
            $table->string('boost_type', 30)->nullable();
            $table->unsignedBigInteger('views')->default(0);
            $table->unsignedBigInteger('likes')->default(0);
            $table->unsignedBigInteger('shares')->default(0);
            $table->unsignedBigInteger('clicks')->default(0);
            $table->unsignedInteger('comments_count')->default(0);
            $table->decimal('trending_score', 10, 2)->default(0.00);
            $table->string('status', 20)->default('published'); // published, pending, rejected
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index(['is_boosted', 'status']);
            $table->index('trending_score');
            $table->index('views');
            $table->index(['vendor_id', 'status']);
        });

        // 2. short_products pivot table
        Schema::create('short_products', function (Blueprint $table) {
            $table->foreignId('short_id')->constrained('shorts')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->primary(['short_id', 'product_id']);
            $table->index(['short_id', 'sort_order']);
        });

        // 3. short_events table
        Schema::create('short_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('short_id')->constrained('shorts')->cascadeOnDelete();
            $table->string('event_type', 30)->default('view'); // view, like, share, click, comment, watch_pct
            $table->unsignedBigInteger('user_id')->default(0);
            $table->string('session_id', 64)->default('');
            $table->string('device', 20)->default('unknown');
            $table->string('browser', 50)->default('');
            $table->string('country', 10)->default('');
            $table->string('referrer', 500)->default('');
            $table->string('ip_hash', 64)->default('');
            $table->unsignedTinyInteger('watch_pct')->default(0);
            $table->timestamp('created_at')->useCurrent();

            $table->index('short_id');
            $table->index('event_type');
            $table->index('created_at');
            $table->index('user_id');
        });

        // 4. short_boosts table
        Schema::create('short_boosts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('short_id')->constrained('shorts')->cascadeOnDelete();
            $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();
            $table->string('boost_type', 30)->default('standard'); // standard, prime, elite
            $table->unsignedInteger('coins_spent')->default(0);
            $table->timestamp('starts_at');
            $table->timestamp('ends_at');
            $table->string('status', 20)->default('active'); // active, expired, cancelled
            $table->unsignedInteger('impressions')->default(0);
            $table->unsignedInteger('clicks')->default(0);
            $table->timestamps();

            $table->index('short_id');
            $table->index('vendor_id');
            $table->index('status');
            $table->index('ends_at');
        });

        // 5. short_coins table
        Schema::create('short_coins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->integer('delta')->default(0);
            $table->integer('balance')->default(0);
            $table->string('reason', 100)->default('');
            $table->unsignedBigInteger('ref_id')->default(0);
            $table->text('meta')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('created_at');
        });

        // 6. short_comments table
        Schema::create('short_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('short_id')->constrained('shorts')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedBigInteger('parent_id')->default(0);
            $table->text('content');
            $table->string('status', 20)->default('approved'); // approved, pending, spam
            $table->unsignedInteger('likes')->default(0);
            $table->timestamps();

            $table->index('short_id');
            $table->index('user_id');
            $table->index('status');
        });

        // 7. short_follows table
        Schema::create('short_follows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('follower_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['follower_id', 'vendor_id']);
            $table->index('vendor_id');
            $table->index('follower_id');
        });

        // 8. short_shares table
        Schema::create('short_shares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('short_id')->constrained('shorts')->cascadeOnDelete();
            $table->string('platform', 40)->default('generic');
            $table->unsignedBigInteger('user_id')->default(0);
            $table->string('session_id', 64)->default('');
            $table->timestamp('created_at')->useCurrent();

            $table->index('short_id');
            $table->index('platform');
            $table->index('created_at');
        });

        // 9. spin_prizes table
        Schema::create('spin_prizes', function (Blueprint $table) {
            $table->id();
            $table->string('label', 50);
            $table->string('color', 20);
            $table->string('type', 20)->default('coupon'); // coupon, coins, free_shipping
            $table->string('value', 100);
            $table->foreignId('coupon_id')->nullable()->constrained('coupons')->nullOnDelete();
            $table->unsignedInteger('probability')->default(10);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Seed initial real coupons for Spin wheel to satisfy Decision D8
        $now = now();
        $coupons = [
            ['code' => 'SPIN10', 'type' => 'percentage', 'value' => 10, 'starts_at' => $now, 'expires_at' => $now->copy()->addYears(2), 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'SPIN15', 'type' => 'percentage', 'value' => 15, 'starts_at' => $now, 'expires_at' => $now->copy()->addYears(2), 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'SPIN20', 'type' => 'percentage', 'value' => 20, 'starts_at' => $now, 'expires_at' => $now->copy()->addYears(2), 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ];

        foreach ($coupons as $c) {
            $existing = DB::table('coupons')->where('code', $c['code'])->first();
            if (!$existing) {
                $couponId = DB::table('coupons')->insertGetId($c);
            } else {
                $couponId = $existing->id;
            }

            // Seed corresponding spin prize
            $label = $c['value'] . '% OFF';
            $color = match ($c['value']) {
                10 => '#7c3aed',
                15 => '#dc2626',
                20 => '#d97706',
                default => '#2563eb'
            };

            DB::table('spin_prizes')->updateOrInsert(
                ['value' => $c['code']],
                [
                    'label' => $label,
                    'color' => $color,
                    'type' => 'coupon',
                    'coupon_id' => $couponId,
                    'probability' => 20,
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }

        // Seed default shorts settings in settings table
        $defaultSettings = [
            'zsu_autoplay' => '1',
            'zsu_muted_start' => '1',
            'zsu_loop' => '1',
            'zsu_show_featured' => '1',
            'zsu_featured_count' => '8',
            'zsu_videos_per_page' => '24',
            'zsu_grid_columns' => '2',
            'zsu_primary_color' => '#7c3aed',
            'zsu_accent_color' => '#f59e0b',
            'zsu_spin_wheel_enabled' => '1',
            'zsu_spin_wheel_title' => 'Spin & Win!',
            'zsu_spin_wheel_subtitle' => 'Spin once a day for exclusive prizes',
            'zsu_spin_cooldown_hours' => '24',
            'zsu_boost_enabled' => '1',
            'zsu_boost_price_std' => '199',
            'zsu_boost_price_prime' => '499',
            'zsu_boost_price_elite' => '999',
        ];

        foreach ($defaultSettings as $key => $val) {
            DB::table('settings')->updateOrInsert(
                ['key' => $key],
                [
                    'group' => 'shorts',
                    'type' => 'string',
                    'value' => $val,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spin_prizes');
        Schema::dropIfExists('short_shares');
        Schema::dropIfExists('short_follows');
        Schema::dropIfExists('short_comments');
        Schema::dropIfExists('short_coins');
        Schema::dropIfExists('short_boosts');
        Schema::dropIfExists('short_events');
        Schema::dropIfExists('short_products');
        Schema::dropIfExists('shorts');
    }
};
