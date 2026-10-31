<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chat_messages', function (Blueprint $table) {
            if (!Schema::hasColumn('chat_messages', 'attachment_url')) {
                $table->string('attachment_url')->nullable()->after('body');
            }
            if (!Schema::hasColumn('chat_messages', 'attachment_type')) {
                $table->string('attachment_type', 30)->nullable()->after('attachment_url');
            }
            if (!Schema::hasColumn('chat_messages', 'reply_to_id')) {
                $table->unsignedBigInteger('reply_to_id')->nullable()->after('attachment_type');
            }
            if (!Schema::hasColumn('chat_messages', 'reply_to_body')) {
                $table->text('reply_to_body')->nullable()->after('reply_to_id');
            }
            if (!Schema::hasColumn('chat_messages', 'reply_to_sender_name')) {
                $table->string('reply_to_sender_name')->nullable()->after('reply_to_body');
            }
        });
    }

    public function down(): void
    {
        Schema::table('chat_messages', function (Blueprint $table) {
            $table->dropColumn(['attachment_url', 'attachment_type', 'reply_to_id', 'reply_to_body', 'reply_to_sender_name']);
        });
    }
};

