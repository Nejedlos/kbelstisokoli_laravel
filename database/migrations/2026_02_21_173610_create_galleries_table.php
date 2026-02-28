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
        if (! Schema::hasTable('galleries')) {
            Schema::create('galleries', function (Blueprint $table) {
                $table->id();
                $table->longText('title');
                $table->string('slug')->unique();
                $table->longText('description')->nullable();
                $table->boolean('is_public')->default(true);
                $table->boolean('is_visible')->default(true);
                $table->string('variant')->default('grid'); // grid, masonry
                $table->foreignId('cover_asset_id')->nullable()->constrained('media_assets')->onDelete('set null');
                $table->timestamp('published_at')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('galleries');
    }
};
