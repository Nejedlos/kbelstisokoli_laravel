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
        Schema::create('partners', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('type')->default('partner'); // main_partner, general_partner, partner, media_partner
            $table->string('website_url')->nullable();
            $table->string('logo_path_png')->nullable();
            $table->string('logo_path_webp')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->integer('sort_order')->default(0);

            // Umístění
            $table->boolean('show_on_homepage')->default(true);
            $table->boolean('show_below_hero')->default(true);
            $table->boolean('show_in_footer')->default(true);
            $table->boolean('show_on_match_pages')->default(true);
            $table->boolean('show_on_contact_page')->default(true);
            $table->boolean('show_on_recruitment_page')->default(true);

            // Překlady
            $table->json('label')->nullable();
            $table->json('description')->nullable();

            $table->boolean('opened_in_new_tab')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('partners');
    }
};
