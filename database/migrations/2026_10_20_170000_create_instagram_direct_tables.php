<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations for Instagram Direct (DM) clone.
     */
    public function up(): void
    {
        if (!Schema::hasTable('user_follows')) {
            Schema::create('user_follows', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('follower_id');
                $table->unsignedBigInteger('following_id');
                $table->string('status', 20)->default('pending'); // 'pending', 'accepted', 'declined', 'blocked'
                $table->timestamps();

                $table->unique(['follower_id', 'following_id'], 'idx_follower_following');
                $table->index(['following_id', 'status'], 'idx_following_status');
                $table->index(['follower_id', 'status'], 'idx_follower_status');
            });
        }

        if (!Schema::hasTable('chat_conversations')) {
            Schema::create('chat_conversations', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_one_id');
                $table->unsignedBigInteger('user_two_id');
                $table->timestamp('last_message_at')->useCurrent();

                $table->unique(['user_one_id', 'user_two_id'], 'idx_conversation_pair');
                $table->index('user_one_id', 'idx_user_one');
                $table->index('user_two_id', 'idx_user_two');
                $table->index('last_message_at', 'idx_last_message');
            });
        }

        if (!Schema::hasTable('chat_messages')) {
            Schema::create('chat_messages', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('conversation_id');
                $table->unsignedBigInteger('sender_id');
                $table->text('body');
                $table->boolean('is_read')->default(false);
                $table->timestamp('created_at')->useCurrent();

                $table->index('conversation_id', 'idx_conversation_id');
                $table->index('sender_id', 'idx_sender_id');
                $table->index('created_at', 'idx_created_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_messages');
        Schema::dropIfExists('chat_conversations');
        Schema::dropIfExists('user_follows');
    }
};
