<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('settlement_status', 20)->default('pending')->after('payment_status'); // pending, cleared, failed
            $table->timestamp('settlement_expected_at')->nullable()->after('settlement_status');
            $table->timestamp('settled_at')->nullable()->after('settlement_expected_at');
            $table->string('settlement_batch_ref', 100)->nullable()->after('settled_at');

            $table->string('vendor_payout_status', 20)->default('unsettled')->after('settlement_batch_ref'); // unsettled, ready, paid, failed, refunded
            $table->integer('vendor_payout_amount')->nullable()->after('vendor_payout_status'); // in paise
            $table->string('vendor_payout_utr', 100)->nullable()->after('vendor_payout_amount');
            $table->timestamp('vendor_payout_at')->nullable()->after('vendor_payout_utr');
            $table->timestamp('vendor_accepted_at')->nullable()->after('vendor_payout_at');
            $table->timestamp('vendor_rejected_at')->nullable()->after('vendor_accepted_at');
            $table->string('vendor_rejection_reason', 500)->nullable()->after('vendor_rejected_at');
        });

        Schema::table('vendor_settings', function (Blueprint $table) {
            $table->string('bank_account_number', 50)->nullable()->after('upi_id_previous');
            $table->string('bank_ifsc', 20)->nullable()->after('bank_account_number');
            $table->string('bank_name', 100)->nullable()->after('bank_ifsc');
            $table->string('payout_method', 20)->default('upi')->after('bank_name'); // upi, bank
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'settlement_status',
                'settlement_expected_at',
                'settled_at',
                'settlement_batch_ref',
                'vendor_payout_status',
                'vendor_payout_amount',
                'vendor_payout_utr',
                'vendor_payout_at',
                'vendor_accepted_at',
                'vendor_rejected_at',
                'vendor_rejection_reason',
            ]);
        });

        Schema::table('vendor_settings', function (Blueprint $table) {
            $table->dropColumn([
                'bank_account_number',
                'bank_ifsc',
                'bank_name',
                'payout_method',
            ]);
        });
    }
};
