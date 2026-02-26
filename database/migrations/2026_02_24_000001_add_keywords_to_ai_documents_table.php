<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Pro SQLite (testy) použijeme standardní Schema, abychom se vyhnuli problémům s raw SQL
        if (\Illuminate\Support\Facades\DB::getDriverName() === 'sqlite') {
            Schema::table('ai_documents', function (Blueprint $table) {
                $table->longText('keywords')->nullable()->after('content');
                $table->longText('metadata')->nullable()->after('keywords');
            });

            return;
        }

        // We use raw DB::statement because Schema::table triggers getColumnListing
        // which fails on this production DB environment due to a Laravel/MariaDB bug
        // (Unknown column 'generation_expression').
        $prefix = \Illuminate\Support\Facades\DB::getTablePrefix();
        $table = $prefix . 'ai_documents';

        try {
            $columnExists = \Illuminate\Support\Facades\DB::select("SHOW COLUMNS FROM {$table} LIKE 'keywords'");
            if (empty($columnExists)) {
                \Illuminate\Support\Facades\DB::statement("ALTER TABLE {$table} ADD COLUMN keywords LONGTEXT NULL AFTER content");
            }
        } catch (\Throwable $e) {
            // Silently ignore
        }

        try {
            $columnExists = \Illuminate\Support\Facades\DB::select("SHOW COLUMNS FROM {$table} LIKE 'metadata'");
            if (empty($columnExists)) {
                \Illuminate\Support\Facades\DB::statement("ALTER TABLE {$table} ADD COLUMN metadata LONGTEXT NULL AFTER keywords");
            }
        } catch (\Throwable $e) {
            // Silently ignore
        }
    }

    public function down(): void
    {
        if (\Illuminate\Support\Facades\DB::getDriverName() === 'sqlite') {
            Schema::table('ai_documents', function (Blueprint $table) {
                $table->dropColumn(['keywords', 'metadata']);
            });

            return;
        }

        $prefix = \Illuminate\Support\Facades\DB::getTablePrefix();
        $table = $prefix . 'ai_documents';

        try {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE {$table} DROP COLUMN IF EXISTS keywords");
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE {$table} DROP COLUMN IF EXISTS metadata");
        } catch (\Throwable $e) {
            // Ignore
        }
    }
};
