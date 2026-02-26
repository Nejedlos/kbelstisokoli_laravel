<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // We use raw DB::statement because Schema::table triggers getColumnListing
        // which fails on this production DB environment due to a Laravel/MariaDB bug
        // (Unknown column 'generation_expression').
        $prefix = \Illuminate\Support\Facades\DB::getTablePrefix();
        $table = $prefix . 'ai_documents';

        try {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE {$table} ADD COLUMN IF NOT EXISTS keywords LONGTEXT NULL AFTER content");
        } catch (\Throwable $e) {
            // Silently ignore if it fails, although IF NOT EXISTS should handle it
        }

        try {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE {$table} ADD COLUMN IF NOT EXISTS metadata LONGTEXT NULL AFTER keywords");
        } catch (\Throwable $e) {
            // Silently ignore
        }
    }

    public function down(): void
    {
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
