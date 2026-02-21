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
        Schema::table('seo_metadatas', function (Blueprint $table) {
            // Změna existujících polí na JSON pro spatie/laravel-translatable
            $table->json('title')->nullable()->change();
            $table->json('description')->nullable()->change();
            $table->json('og_title')->nullable()->change();
            $table->json('og_description')->nullable()->change();

            // Nová pole
            $table->string('canonical_url')->nullable();
            $table->boolean('robots_index')->default(true);
            $table->boolean('robots_follow')->default(true);
            $table->string('twitter_card')->nullable()->default('summary_large_image');
            $table->json('structured_data_override')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('seo_metadatas', function (Blueprint $table) {
            $table->string('title')->nullable()->change();
            $table->text('description')->nullable()->change();
            $table->string('og_title')->nullable()->change();
            $table->text('og_description')->nullable()->change();

            $table->dropColumn([
                'canonical_url',
                'robots_index',
                'robots_follow',
                'twitter_card',
                'structured_data_override',
            ]);
        });
    }
};
