<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('chat_messages')) {
            Schema::table('chat_messages', function (Blueprint $table) {
                if (!Schema::hasColumn('chat_messages', 'is_edited')) {
                    $table->boolean('is_edited')->default(false)->after('is_read');
                }
                if (!Schema::hasColumn('chat_messages', 'edited_at')) {
                    $table->timestamp('edited_at')->nullable()->after('is_edited');
                }
            });
        }

        if (Schema::hasTable('messages')) {
            Schema::table('messages', function (Blueprint $table) {
                if (!Schema::hasColumn('messages', 'is_edited')) {
                    $table->boolean('is_edited')->default(false)->after('is_read');
                }
                if (!Schema::hasColumn('messages', 'edited_at')) {
                    $table->timestamp('edited_at')->nullable()->after('is_edited');
                }
            });
        }

        if (Schema::hasTable('chat_conversations')) {
            Schema::table('chat_conversations', function (Blueprint $table) {
                if (!Schema::hasColumn('chat_conversations', 'typing_user_id')) {
                    $table->unsignedBigInteger('typing_user_id')->nullable()->after('last_message_at');
                }
                if (!Schema::hasColumn('chat_conversations', 'typing_at')) {
                    $table->timestamp('typing_at')->nullable()->after('typing_user_id');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('chat_messages')) {
            Schema::table('chat_messages', function (Blueprint $table) {
                if (Schema::hasColumn('chat_messages', 'is_edited')) {
                    $table->dropColumn('is_edited');
                }
                if (Schema::hasColumn('chat_messages', 'edited_at')) {
                    $table->dropColumn('edited_at');
                }
            });
        }

        if (Schema::hasTable('chat_conversations')) {
            Schema::table('chat_conversations', function (Blueprint $table) {
                if (Schema::hasColumn('chat_conversations', 'typing_user_id')) {
                    $table->dropColumn('typing_user_id');
                }
                if (Schema::hasColumn('chat_conversations', 'typing_at')) {
                    $table->dropColumn('typing_at');
                }
            });
        }
    }
};
