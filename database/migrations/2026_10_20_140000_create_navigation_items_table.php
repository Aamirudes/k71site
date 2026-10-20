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
        Schema::create('navigation_items', function (Blueprint $table) {
            $table->id();
            $table->string('group', 50)->default('mobile_bottom')->index(); // 'mobile_bottom', 'mobile_drawer', 'header_quick'
            $table->string('title', 100);
            $table->string('subtitle', 150)->nullable();
            $table->string('url', 255);
            $table->enum('icon_type', ['svg', 'image', 'emoji'])->default('svg');
            $table->text('icon')->nullable(); // SVG code or emoji
            $table->string('image_url', 500)->nullable(); // Hosted image or uploaded path
            $table->string('badge', 50)->nullable(); // e.g. 'HOT', 'NEW', 'LIVE'
            $table->string('badge_color', 50)->nullable()->default('emerald'); // 'emerald', 'rose', 'indigo', 'amber'
            $table->integer('sort_order')->default(0)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->string('target', 20)->default('_self');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('navigation_items');
    }
};
