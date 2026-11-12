<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $addIndexIfMissing = static function (string $tableName, array $columns, string $indexName): void {
            if (!Schema::hasTable($tableName)) {
                return;
            }

            foreach ($columns as $col) {
                if (!Schema::hasColumn($tableName, $col)) {
                    return;
                }
            }

            $existing = collect(Schema::getIndexes($tableName));
            $alreadyExists = $existing->contains(function (array $idx) use ($indexName, $columns) {
                return ($idx['name'] ?? '') === $indexName || ($idx['columns'] ?? []) === $columns;
            });

            if (!$alreadyExists) {
                Schema::table($tableName, function (Blueprint $table) use ($columns, $indexName) {
                    $table->index($columns, $indexName);
                });
            }
        };

        $addIndexIfMissing('products', ['status', 'vertical', 'created_at'], 'products_status_vertical_created_idx');
        $addIndexIfMissing('products', ['vendor_id', 'status', 'updated_at'], 'products_vendor_status_updated_idx');
        $addIndexIfMissing('products', ['category_id', 'status'], 'products_category_status_idx');

        $addIndexIfMissing('orders', ['vendor_id', 'created_at'], 'orders_vendor_created_idx');
        $addIndexIfMissing('orders', ['user_id', 'created_at'], 'orders_user_created_idx');
        $addIndexIfMissing('orders', ['vendor_id', 'payment_method', 'payment_status'], 'orders_vendor_payment_idx');

        $addIndexIfMissing('vendors', ['status', 'verification_status'], 'vendors_status_verification_idx');
        $addIndexIfMissing('vendors', ['vendor_type', 'status'], 'vendors_type_status_idx');

        $addIndexIfMissing('reports', ['status', 'created_at'], 'reports_status_created_idx');
        $addIndexIfMissing('shorts', ['status', 'vendor_id', 'trending_score'], 'shorts_status_vendor_score_idx');
    }

    public function down(): void
    {
        $dropIndexIfExists = static function (string $tableName, string $indexName): void {
            if (!Schema::hasTable($tableName)) {
                return;
            }

            $existing = collect(Schema::getIndexes($tableName));
            if ($existing->contains(fn (array $idx) => ($idx['name'] ?? '') === $indexName)) {
                Schema::table($tableName, function (Blueprint $table) use ($indexName) {
                    $table->dropIndex($indexName);
                });
            }
        };

        $dropIndexIfExists('products', 'products_status_vertical_created_idx');
        $dropIndexIfExists('products', 'products_vendor_status_updated_idx');
        $dropIndexIfExists('products', 'products_category_status_idx');

        $dropIndexIfExists('orders', 'orders_vendor_created_idx');
        $dropIndexIfExists('orders', 'orders_user_created_idx');
        $dropIndexIfExists('orders', 'orders_vendor_payment_idx');

        $dropIndexIfExists('vendors', 'vendors_status_verification_idx');
        $dropIndexIfExists('vendors', 'vendors_type_status_idx');

        $dropIndexIfExists('reports', 'reports_status_created_idx');
        $dropIndexIfExists('shorts', 'shorts_status_vendor_score_idx');
    }
};

