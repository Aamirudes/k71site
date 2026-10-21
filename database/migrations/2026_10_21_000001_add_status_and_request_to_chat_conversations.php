<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations for Instagram Direct Message Requests.
     */
    public function up(): void
    {
        if (Schema::hasTable('chat_conversations')) {
            Schema::table('chat_conversations', function (Blueprint $table) {
                if (!Schema::hasColumn('chat_conversations', 'status')) {
                    $table->string('status', 20)->default('accepted')->after('user_two_id');
                    $table->index('status', 'idx_chat_status');
                }
                if (!Schema::hasColumn('chat_conversations', 'requested_by')) {
                    $table->unsignedBigInteger('requested_by')->nullable()->after('status');
                    $table->index('requested_by', 'idx_chat_requested_by');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('chat_conversations')) {
            Schema::table('chat_conversations', function (Blueprint $table) {
                if (Schema::hasColumn('chat_conversations', 'requested_by')) {
                    $table->dropColumn('requested_by');
                }
                if (Schema::hasColumn('chat_conversations', 'status')) {
                    $table->dropColumn('status');
                }
            });
        }
    }
};
