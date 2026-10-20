<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run migrations for K71 Phase 9: Auctions, Bidding & Direct Messaging.
     */
    public function up(): void
    {
        // 1. Add auction columns to products table
        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                if (!Schema::hasColumn('products', 'reserve_price')) {
                    $table->integer('reserve_price')->nullable()->after('base_price');
                }
                if (!Schema::hasColumn('products', 'bid_increment')) {
                    $table->integer('bid_increment')->default(10000)->after('reserve_price'); // In paise (default ₹100)
                }
                if (!Schema::hasColumn('products', 'auction_starts_at')) {
                    $table->timestamp('auction_starts_at')->nullable()->after('bid_increment');
                }
                if (!Schema::hasColumn('products', 'auction_ends_at')) {
                    $table->timestamp('auction_ends_at')->nullable()->after('auction_starts_at');
                }
                if (!Schema::hasColumn('products', 'current_bid')) {
                    $table->integer('current_bid')->nullable()->after('auction_ends_at');
                }
                if (!Schema::hasColumn('products', 'winning_bidder_id')) {
                    $table->foreignId('winning_bidder_id')->nullable()->after('current_bid')->constrained('users')->nullOnDelete();
                }
                if (!Schema::hasColumn('products', 'bids_count')) {
                    $table->unsignedInteger('bids_count')->default(0)->after('winning_bidder_id');
                }
                if (!Schema::hasColumn('products', 'auction_status')) {
                    $table->string('auction_status', 20)->default('draft')->after('bids_count')->index(); // draft, live, ended, reserve_not_met
                }
            });
        }

        // 2. Create bids table
        if (!Schema::hasTable('bids')) {
            Schema::create('bids', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->integer('bid_amount'); // In paise
                $table->string('status', 20)->default('active')->index(); // active, outbid, winning, won
                $table->string('ip_address', 45)->nullable();
                $table->timestamps();

                $table->index(['product_id', 'bid_amount']);
                $table->index(['user_id', 'status']);
            });
        }

        // 3. Create conversations table
        if (!Schema::hasTable('conversations')) {
            Schema::create('conversations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
                $table->string('subject', 200)->nullable();
                $table->timestamp('last_message_at')->useCurrent();
                $table->timestamps();

                $table->index(['vendor_id', 'last_message_at']);
                $table->index(['user_id', 'last_message_at']);
            });
        }

        // 4. Create messages table
        if (!Schema::hasTable('messages')) {
            Schema::create('messages', function (Blueprint $table) {
                $table->id();
                $table->foreignId('conversation_id')->constrained('conversations')->cascadeOnDelete();
                $table->string('sender_type', 20); // 'user' or 'vendor'
                $table->unsignedBigInteger('sender_id');
                $table->text('body');
                $table->boolean('is_read')->default(false)->index();
                $table->timestamp('read_at')->nullable();
                $table->timestamps();

                $table->index(['conversation_id', 'created_at']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
        Schema::dropIfExists('conversations');
        Schema::dropIfExists('bids');

        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                $columns = ['reserve_price', 'bid_increment', 'auction_starts_at', 'auction_ends_at', 'current_bid', 'winning_bidder_id', 'bids_count', 'auction_status'];
                $table->dropColumn(array_intersect($columns, Schema::getColumnListing('products')));
            });
        }
    }
};
