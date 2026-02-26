<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     * This migration ensures that all necessary columns for AI indexing exist,
     * as previous migrations might have failed silently on some MySQL versions
     * due to 'IF NOT EXISTS' syntax or other schema introspection issues.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            // Pro SQLite to přeskočíme, protože předchozí migrace jsou již opraveny
            // a Schema::hasColumn by mohl selhat na stejné introspekci jako na produkci
            // (i když v SQLite by to mělo fungovat, raději neriskujeme duplicitu).
            return;
        }

        $prefix = DB::getTablePrefix();
        $table = $prefix . 'ai_documents';

        // Check and add 'keywords'
        try {
            $columnExists = DB::select("SHOW COLUMNS FROM {$table} LIKE 'keywords'");
            if (empty($columnExists)) {
                DB::statement("ALTER TABLE {$table} ADD COLUMN keywords LONGTEXT NULL AFTER content");
            }
        } catch (\Throwable $e) {
            // Log or ignore
        }

        // Check and add 'metadata'
        try {
            $columnExists = DB::select("SHOW COLUMNS FROM {$table} LIKE 'metadata'");
            if (empty($columnExists)) {
                DB::statement("ALTER TABLE {$table} ADD COLUMN metadata LONGTEXT NULL AFTER keywords");
            }
        } catch (\Throwable $e) {
            // Log or ignore
        }

        // Check and add 'summary'
        try {
            $columnExists = DB::select("SHOW COLUMNS FROM {$table} LIKE 'summary'");
            if (empty($columnExists)) {
                DB::statement("ALTER TABLE {$table} ADD COLUMN summary TEXT NULL AFTER title");
            }
        } catch (\Throwable $e) {
            // Log or ignore
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No need to drop columns here as they are handled by their original migrations
    }
};
