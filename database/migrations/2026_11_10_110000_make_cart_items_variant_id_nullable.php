<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('cart_items')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            $dbName = Schema::getConnection()->getDatabaseName();

            // Ensure cart_id has its own non-unique index before dropping composite unique index
            $hasCartIdx = DB::table('information_schema.STATISTICS')
                ->where('TABLE_SCHEMA', $dbName)
                ->where('TABLE_NAME', 'cart_items')
                ->where('COLUMN_NAME', 'cart_id')
                ->where('INDEX_NAME', '!=', 'cart_items_cart_id_variant_id_unique')
                ->exists();

            if (!$hasCartIdx) {
                Schema::table('cart_items', function (Blueprint $table) {
                    $table->index('cart_id', 'cart_items_cart_id_index');
                });
            }

            $hasCompositeUnique = DB::table('information_schema.STATISTICS')
                ->where('TABLE_SCHEMA', $dbName)
                ->where('TABLE_NAME', 'cart_items')
                ->where('INDEX_NAME', 'cart_items_cart_id_variant_id_unique')
                ->exists();

            if ($hasCompositeUnique) {
                DB::statement('ALTER TABLE `cart_items` DROP INDEX `cart_items_cart_id_variant_id_unique`');
            }

            // Drop FK on variant_id before altering nullability
            $fkNames = DB::table('information_schema.KEY_COLUMN_USAGE')
                ->where('TABLE_SCHEMA', $dbName)
                ->where('TABLE_NAME', 'cart_items')
                ->where('COLUMN_NAME', 'variant_id')
                ->whereNotNull('REFERENCED_TABLE_NAME')
                ->pluck('CONSTRAINT_NAME')
                ->unique();

            foreach ($fkNames as $fkName) {
                DB::statement("ALTER TABLE `cart_items` DROP FOREIGN KEY `{$fkName}`");
            }

            DB::statement('ALTER TABLE `cart_items` MODIFY `variant_id` BIGINT UNSIGNED NULL');

            Schema::table('cart_items', function (Blueprint $table) {
                $table->foreign('variant_id')
                    ->references('id')
                    ->on('product_variants')
                    ->nullOnDelete();
            });

            return;
        }

        // SQLite and other drivers
        try {
            Schema::table('cart_items', function (Blueprint $table) {
                $table->dropUnique('cart_items_cart_id_variant_id_unique');
            });
        } catch (\Throwable $e) {
            // Composite unique index already dropped
        }

        Schema::table('cart_items', function (Blueprint $table) {
            $table->unsignedBigInteger('variant_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('cart_items')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            $dbName = Schema::getConnection()->getDatabaseName();
            $fkNames = DB::table('information_schema.KEY_COLUMN_USAGE')
                ->where('TABLE_SCHEMA', $dbName)
                ->where('TABLE_NAME', 'cart_items')
                ->where('COLUMN_NAME', 'variant_id')
                ->whereNotNull('REFERENCED_TABLE_NAME')
                ->pluck('CONSTRAINT_NAME')
                ->unique();

            foreach ($fkNames as $fkName) {
                DB::statement("ALTER TABLE `cart_items` DROP FOREIGN KEY `{$fkName}`");
            }

            DB::table('cart_items')->whereNull('variant_id')->delete();
            DB::statement('ALTER TABLE `cart_items` MODIFY `variant_id` BIGINT UNSIGNED NOT NULL');

            Schema::table('cart_items', function (Blueprint $table) {
                $table->foreign('variant_id')
                    ->references('id')
                    ->on('product_variants')
                    ->cascadeOnDelete();
            });
        }
    }
};

