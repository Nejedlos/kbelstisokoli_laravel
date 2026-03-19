<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        // Tabulka ai_documents byla problematická
        if (Schema::hasTable('ai_documents')) {
            Schema::table('ai_documents', function (Blueprint $table) {
                // Převod na JSON typ v MySQL 8
                $table->json('title')->nullable()->change();
                $table->json('summary')->nullable()->change();
                $table->json('content')->nullable()->change();
                $table->json('keywords')->nullable()->change();
                $table->json('metadata')->nullable()->change();
            });
        }

        // Tabulka settings a value
        if (Schema::hasTable('settings')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->json('value')->nullable()->change();
            });
        }

        // Tabulka feedback_reports (předchozí migrace ji sice měla, ale pro jistotu)
        if (Schema::hasTable('feedback_reports')) {
             Schema::table('feedback_reports', function (Blueprint $table) {
                $table->json('viewport')->nullable()->change();
                $table->json('screen')->nullable()->change();
                $table->json('meta')->nullable()->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        if (Schema::hasTable('ai_documents')) {
            Schema::table('ai_documents', function (Blueprint $table) {
                $table->string('title')->nullable()->change();
                $table->text('summary')->nullable()->change();
                $table->longText('content')->nullable()->change();
                $table->longText('keywords')->nullable()->change();
                $table->longText('metadata')->nullable()->change();
            });
        }

        if (Schema::hasTable('settings')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->text('value')->nullable()->change();
            });
        }
    }
};
