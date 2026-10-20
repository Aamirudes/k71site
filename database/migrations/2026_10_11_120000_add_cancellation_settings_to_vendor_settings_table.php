<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendor_settings', function (Blueprint $table) {
            $table->boolean('allow_customer_cancellation')->default(true)->after('allow_backorder');
            $table->unsignedInteger('cancellation_window_hours')->default(24)->after('allow_customer_cancellation');
        });
    }

    public function down(): void
    {
        Schema::table('vendor_settings', function (Blueprint $table) {
            $table->dropColumn(['allow_customer_cancellation', 'cancellation_window_hours']);
        });
    }
};
