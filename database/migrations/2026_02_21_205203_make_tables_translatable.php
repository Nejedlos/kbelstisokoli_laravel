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
        // Settings
        Schema::table('settings', function (Blueprint $table) {
            $table->json('value')->nullable()->change();
        });

        // CMS
        Schema::table('posts', function (Blueprint $table) {
            $table->json('title')->change();
            $table->json('excerpt')->nullable()->change();
            $table->json('content')->nullable()->change();
        });

        Schema::table('pages', function (Blueprint $table) {
            $table->json('title')->change();
        });

        Schema::table('post_categories', function (Blueprint $table) {
            $table->json('name')->change();
            $table->json('description')->nullable()->change();
        });

        Schema::table('menu_items', function (Blueprint $table) {
            $table->json('label')->change();
        });

        // Announcements
        Schema::table('announcements', function (Blueprint $table) {
            $table->json('title')->nullable()->change();
            $table->json('message')->change();
            $table->json('cta_label')->nullable()->change();
        });

        // Galleries
        Schema::table('galleries', function (Blueprint $table) {
            $table->json('title')->change();
            $table->json('description')->nullable()->change();
        });

        // Sports
        Schema::table('teams', function (Blueprint $table) {
            $table->json('name')->change();
            $table->json('description')->nullable()->change();
        });

        Schema::table('club_events', function (Blueprint $table) {
            $table->json('title')->change();
            $table->json('description')->nullable()->change();
        });

        Schema::table('matches', function (Blueprint $table) {
            $table->json('notes_public')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // V down metodě bychom měli teoreticky vracet na string/text,
        // ale jelikož v datech bude JSON, tak by to mohlo způsobit problémy.
        // Pro jednoduchost a bezpečnost v této fázi necháme json.
    }
};
