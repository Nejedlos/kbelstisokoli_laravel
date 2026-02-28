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
        if (! Schema::hasTable('photo_pool_media_asset')) {
            Schema::create('photo_pool_media_asset', function (Blueprint $table) {
                $table->id();
                $table->foreignId('photo_pool_id')->constrained('photo_pools')->onDelete('cascade');
                $table->foreignId('media_asset_id')->constrained('media_assets')->onDelete('cascade');
                $table->unsignedInteger('sort_order')->default(0);
                $table->string('caption_override')->nullable();
                $table->boolean('is_visible')->default(true);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('photo_pool_media_asset');
    }
};
