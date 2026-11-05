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
        if (!Schema::hasTable('vendor_storefront_configs')) {
            Schema::create('vendor_storefront_configs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('vendor_id')->unique()->constrained('vendors')->cascadeOnDelete();
                $table->string('preset_key', 50)->default('atelier');
                $table->unsignedSmallInteger('schema_version')->default(2);
                $table->json('draft_config')->nullable();
                $table->json('published_config')->nullable();
                $table->boolean('is_published')->default(true);
                $table->timestamp('last_saved_at')->nullable();
                $table->timestamp('last_published_at')->nullable();
                $table->timestamps();

                $table->index(['vendor_id', 'preset_key']);
            });
        }

        if (!Schema::hasTable('vendor_storefront_revisions')) {
            Schema::create('vendor_storefront_revisions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();
                $table->string('preset_key', 50);
                $table->json('config_data');
                $table->string('summary', 255)->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->index(['vendor_id', 'created_at']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendor_storefront_revisions');
        Schema::dropIfExists('vendor_storefront_configs');
    }
};

