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
        if (Schema::hasTable('settings')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->json('value')->nullable()->change();
            });
        }

        // CMS
        if (Schema::hasTable('posts')) {
            Schema::table('posts', function (Blueprint $table) {
                $table->json('title')->change();
                $table->json('excerpt')->nullable()->change();
                $table->json('content')->nullable()->change();
            });
        }

        if (Schema::hasTable('pages')) {
            Schema::table('pages', function (Blueprint $table) {
                $table->json('title')->change();
            });
        }

        if (Schema::hasTable('post_categories')) {
            Schema::table('post_categories', function (Blueprint $table) {
                $table->json('name')->change();
                $table->json('description')->nullable()->change();
            });
        }

        if (Schema::hasTable('menu_items')) {
            Schema::table('menu_items', function (Blueprint $table) {
                $table->json('label')->change();
            });
        }

        // Announcements
        if (Schema::hasTable('announcements')) {
            Schema::table('announcements', function (Blueprint $table) {
                $table->json('title')->nullable()->change();
                $table->json('message')->change();
                $table->json('cta_label')->nullable()->change();
            });
        }

        // Galleries
        if (Schema::hasTable('galleries')) {
            Schema::table('galleries', function (Blueprint $table) {
                $table->json('title')->change();
                $table->json('description')->nullable()->change();
            });
        }

        // Sports
        if (Schema::hasTable('teams')) {
            Schema::table('teams', function (Blueprint $table) {
                $table->json('name')->change();
                $table->json('description')->nullable()->change();
            });
        }

        if (Schema::hasTable('club_events')) {
            Schema::table('club_events', function (Blueprint $table) {
                $table->json('title')->change();
                $table->json('description')->nullable()->change();
            });
        }

        if (Schema::hasTable('matches')) {
            Schema::table('matches', function (Blueprint $table) {
                $table->json('notes_public')->nullable()->change();
            });
        }
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
