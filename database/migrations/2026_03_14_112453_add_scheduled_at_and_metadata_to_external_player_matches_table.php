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
        // Pro SQLite (testy) použijeme standardní Schema
        if (\Illuminate\Support\Facades\DB::getDriverName() === 'sqlite') {
            Schema::table('external_player_matches', function (Blueprint $table) {
                if (!Schema::hasColumn('external_player_matches', 'scheduled_at')) {
                    $table->datetime('scheduled_at')->nullable()->after('match_date');
                }
                if (!Schema::hasColumn('external_player_matches', 'venue')) {
                    $table->string('venue')->nullable()->after('opponent_name');
                }
                if (!Schema::hasColumn('external_player_matches', 'metadata')) {
                    $table->longText('metadata')->nullable()->after('fouls_drawn');
                }
            });

            return;
        }

        // Pro MySQL/MariaDB použijeme raw SQL, protože Schema::hasColumn() spouští getColumnListing,
        // což na starých verzích MariaDB padá na chybě 'generation_expression'.
        $prefix = \Illuminate\Support\Facades\DB::getTablePrefix();
        $table = $prefix.'external_player_matches';

        try {
            $columnExists = \Illuminate\Support\Facades\DB::select("SHOW COLUMNS FROM {$table} LIKE 'scheduled_at'");
            if (empty($columnExists)) {
                \Illuminate\Support\Facades\DB::statement("ALTER TABLE {$table} ADD COLUMN scheduled_at DATETIME NULL AFTER match_date");
            }
        } catch (\Throwable $e) {}

        try {
            $columnExists = \Illuminate\Support\Facades\DB::select("SHOW COLUMNS FROM {$table} LIKE 'venue'");
            if (empty($columnExists)) {
                \Illuminate\Support\Facades\DB::statement("ALTER TABLE {$table} ADD COLUMN venue VARCHAR(255) NULL AFTER opponent_name");
            }
        } catch (\Throwable $e) {}

        try {
            $columnExists = \Illuminate\Support\Facades\DB::select("SHOW COLUMNS FROM {$table} LIKE 'metadata'");
            if (empty($columnExists)) {
                \Illuminate\Support\Facades\DB::statement("ALTER TABLE {$table} ADD COLUMN metadata LONGTEXT NULL AFTER fouls_drawn");
            }
        } catch (\Throwable $e) {}
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (\Illuminate\Support\Facades\DB::getDriverName() === 'sqlite') {
            Schema::table('external_player_matches', function (Blueprint $table) {
                $table->dropColumn(array_filter(['scheduled_at', 'venue', 'metadata'], function($column) {
                    return Schema::hasColumn('external_player_matches', $column);
                }));
            });

            return;
        }

        $prefix = \Illuminate\Support\Facades\DB::getTablePrefix();
        $table = $prefix.'external_player_matches';

        try {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE {$table} DROP COLUMN IF EXISTS scheduled_at");
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE {$table} DROP COLUMN IF EXISTS venue");
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE {$table} DROP COLUMN IF EXISTS metadata");
        } catch (\Throwable $e) {}
    }
};
