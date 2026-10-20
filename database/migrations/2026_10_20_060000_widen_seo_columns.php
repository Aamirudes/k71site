<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations to expand SEO titles and descriptions to prevent string truncation.
     */
    public function up(): void
    {
        $tables = ['categories', 'vendors', 'products', 'pages'];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                try {
                    DB::statement("ALTER TABLE `{$table}` MODIFY `seo_title` VARCHAR(255) NULL");
                } catch (\Throwable $e) {}

                try {
                    DB::statement("ALTER TABLE `{$table}` MODIFY `seo_description` TEXT NULL");
                } catch (\Throwable $e) {}
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = ['categories', 'vendors', 'products', 'pages'];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                try {
                    DB::statement("ALTER TABLE `{$table}` MODIFY `seo_title` VARCHAR(70) NULL");
                } catch (\Throwable $e) {}

                try {
                    DB::statement("ALTER TABLE `{$table}` MODIFY `seo_description` VARCHAR(160) NULL");
                } catch (\Throwable $e) {}
            }
        }
    }
};
