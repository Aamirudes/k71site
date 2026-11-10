<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('payment_claims') && Schema::hasColumn('payment_claims', 'utr_number')) {
            DB::table('payment_claims')
                ->where('utr_number', 'WAITING_PROOF')
                ->update(['utr_number' => null]);
        }
    }

    public function down(): void
    {
        // Clean up is non-destructive; legacy placeholder sentinel 'WAITING_PROOF' must not be restored.
    }
};
