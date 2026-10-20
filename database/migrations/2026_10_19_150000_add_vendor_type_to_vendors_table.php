<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('vendors', 'vendor_type')) {
            Schema::table('vendors', function (Blueprint $table) {
                $table->string('vendor_type', 30)->default('clothing')->after('shop_name');
            });
        }

        // Set existing vendor types accurately
        DB::table('vendors')->where('id', 1)->update(['vendor_type' => 'clothing']);
        DB::table('vendors')->where('id', 2)->update(['vendor_type' => 'food']);
        DB::table('vendors')->where('id', 3)->update(['vendor_type' => 'services']);
    }

    public function down(): void
    {
        if (Schema::hasColumn('vendors', 'vendor_type')) {
            Schema::table('vendors', function (Blueprint $table) {
                $table->dropColumn('vendor_type');
            });
        }
    }
};
