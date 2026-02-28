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
        if (config('database.default') === 'sqlite') {
            Schema::table('ai_documents', function (Blueprint $table) {
                if (!Schema::hasColumn('ai_documents', 'section')) {
                    $table->string('section')->after('id')->index()->nullable();
                }
                if (!Schema::hasColumn('ai_documents', 'source_type')) {
                    $table->string('source_type')->nullable()->after('source');
                }
                if (!Schema::hasColumn('ai_documents', 'source_id')) {
                    $table->unsignedBigInteger('source_id')->nullable()->after('source_type');
                }
                if (!Schema::hasColumn('ai_documents', 'content_hash')) {
                    $table->char('content_hash', 64)->nullable()->after('checksum');
                }
                if (!Schema::hasColumn('ai_documents', 'is_active')) {
                    $table->boolean('is_active')->default(true)->after('content_hash');
                }
                if (!Schema::hasColumn('ai_documents', 'last_indexed_at')) {
                    $table->timestamp('last_indexed_at')->nullable()->after('is_active');
                }

                $table->index(['section', 'url']);
                $table->index(['section', 'is_active']);

                if (config('database.default') === 'mysql') {
                    $table->fulltext(['title', 'content'], 'ai_documents_fulltext');
                }
            });
            return;
        }

        // Pro MySQL používáme bezpečnější SQL dotazy, protože Schema::hasColumn na některých hostinzích selhává
        $prefix = DB::getTablePrefix();
        $table = $prefix . 'ai_documents';

        // Add 'section'
        try {
            $columnExists = DB::select("SHOW COLUMNS FROM {$table} LIKE 'section'");
            if (empty($columnExists)) {
                DB::statement("ALTER TABLE {$table} ADD COLUMN section VARCHAR(191) NULL AFTER id");
                DB::statement("CREATE INDEX ai_documents_section_index ON {$table} (section)");
            }
        } catch (\Throwable $e) {}

        // Add 'source_type'
        try {
            $columnExists = DB::select("SHOW COLUMNS FROM {$table} LIKE 'source_type'");
            if (empty($columnExists)) {
                DB::statement("ALTER TABLE {$table} ADD COLUMN source_type VARCHAR(191) NULL AFTER source");
            }
        } catch (\Throwable $e) {}

        // Add 'source_id'
        try {
            $columnExists = DB::select("SHOW COLUMNS FROM {$table} LIKE 'source_id'");
            if (empty($columnExists)) {
                DB::statement("ALTER TABLE {$table} ADD COLUMN source_id BIGINT UNSIGNED NULL AFTER source_type");
            }
        } catch (\Throwable $e) {}

        // Add 'content_hash'
        try {
            $columnExists = DB::select("SHOW COLUMNS FROM {$table} LIKE 'content_hash'");
            if (empty($columnExists)) {
                DB::statement("ALTER TABLE {$table} ADD COLUMN content_hash CHAR(64) NULL AFTER checksum");
            }
        } catch (\Throwable $e) {}

        // Add 'is_active'
        try {
            $columnExists = DB::select("SHOW COLUMNS FROM {$table} LIKE 'is_active'");
            if (empty($columnExists)) {
                DB::statement("ALTER TABLE {$table} ADD COLUMN is_active TINYINT(1) DEFAULT 1 NOT NULL AFTER content_hash");
            }
        } catch (\Throwable $e) {}

        // Add 'last_indexed_at'
        try {
            $columnExists = DB::select("SHOW COLUMNS FROM {$table} LIKE 'last_indexed_at'");
            if (empty($columnExists)) {
                DB::statement("ALTER TABLE {$table} ADD COLUMN last_indexed_at TIMESTAMP NULL AFTER is_active");
            }
        } catch (\Throwable $e) {}

        // Indexes
        try {
            DB::statement("CREATE INDEX ai_documents_section_url_index ON {$table} (section, url)");
        } catch (\Throwable $e) {}
        try {
            DB::statement("CREATE INDEX ai_documents_section_is_active_index ON {$table} (section, is_active)");
        } catch (\Throwable $e) {}
        try {
            DB::statement("ALTER TABLE {$table} ADD FULLTEXT ai_documents_fulltext (title, content)");
        } catch (\Throwable $e) {}
    }

    public function down(): void
    {
        Schema::table('ai_documents', function (Blueprint $table) {
            if (config('database.default') === 'mysql') {
                $table->dropIndex('ai_documents_fulltext');
            }
            $table->dropIndex(['section', 'url']);
            $table->dropIndex(['section', 'is_active']);
            $table->dropColumn(['section', 'source_type', 'source_id', 'content_hash', 'is_active', 'last_indexed_at']);
        });
    }
};
