<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // We use raw DB::statement to avoid Laravel's schema introspection
        // which fails on this production DB environment (Unknown column 'generation_expression').
        $prefix = \Illuminate\Support\Facades\DB::getTablePrefix();
        $table = $prefix . 'ai_documents';

        try {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE {$table} ADD COLUMN IF NOT EXISTS summary TEXT NULL AFTER title");
        } catch (\Throwable $e) {
            // Silently ignore
        }
    }

    public function down(): void
    {
        $prefix = \Illuminate\Support\Facades\DB::getTablePrefix();
        $table = $prefix . 'ai_documents';

        try {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE {$table} DROP COLUMN IF EXISTS summary");
        } catch (\Throwable $e) {
            // Ignore
        }
    }
};
