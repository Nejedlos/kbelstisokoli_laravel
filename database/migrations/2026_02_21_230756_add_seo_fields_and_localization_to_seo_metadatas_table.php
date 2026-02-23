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
        if (Schema::hasTable('seo_metadatas')) {
            Schema::table('seo_metadatas', function (Blueprint $table) {
                // Nová pole
                $table->string('canonical_url')->nullable();
                $table->boolean('robots_index')->default(true);
                $table->boolean('robots_follow')->default(true);
                $table->string('twitter_card')->nullable()->default('summary_large_image');
                $table->longText('structured_data_override')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('seo_metadatas', function (Blueprint $table) {
            // Metoda change() je zakázána kvůli kompatibilitě s Webglobe
            // Sloupce title, description atd. zůstanou jako longText, což ničemu nevadí.

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
