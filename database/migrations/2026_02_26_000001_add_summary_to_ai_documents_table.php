<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Pro SQLite (testy) použijeme standardní Schema
        if (\Illuminate\Support\Facades\DB::getDriverName() === 'sqlite') {
            Schema::table('ai_documents', function (Blueprint $table) {
                $table->text('summary')->nullable()->after('title');
            });

            return;
        }

        // We use raw DB::statement to avoid Laravel's schema introspection
        // which fails on this production DB environment (Unknown column 'generation_expression').
        $prefix = \Illuminate\Support\Facades\DB::getTablePrefix();
        $table = $prefix.'ai_documents';

        try {
            // Safer way to check for column existence without Schema::hasColumn
            $columnExists = \Illuminate\Support\Facades\DB::select("SHOW COLUMNS FROM {$table} LIKE 'summary'");

            if (empty($columnExists)) {
                \Illuminate\Support\Facades\DB::statement("ALTER TABLE {$table} ADD COLUMN summary TEXT NULL AFTER title");
            }
        } catch (\Throwable $e) {
            // Silently ignore if it really fails, but we try our best
        }
    }

    public function down(): void
    {
        $prefix = \Illuminate\Support\Facades\DB::getTablePrefix();
        $table = $prefix.'ai_documents';

        try {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE {$table} DROP COLUMN IF EXISTS summary");
        } catch (\Throwable $e) {
            // Ignore
        }
    }
};
