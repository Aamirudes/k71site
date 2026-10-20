<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('vendor_settings')) {
            Schema::table('vendor_settings', function (Blueprint $table) {
                $table->boolean('cod_enabled')->default(false)->change();
            });

            DB::table('vendor_settings')->update(['cod_enabled' => false]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
