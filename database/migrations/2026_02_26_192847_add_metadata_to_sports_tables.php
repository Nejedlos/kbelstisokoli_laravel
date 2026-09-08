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
            Schema::table('matches', function (Blueprint $table) {
                $table->longText('metadata')->nullable()->after('notes_public');
            });

            Schema::table('trainings', function (Blueprint $table) {
                $table->longText('metadata')->nullable()->after('notes');
            });

            return;
        }

        $prefix = DB::getTablePrefix();

        // Matches
        $tableMatches = $prefix.'matches';
        try {
            $columnExists = DB::select("SHOW COLUMNS FROM {$tableMatches} LIKE 'metadata'");
            if (empty($columnExists)) {
                DB::statement("ALTER TABLE {$tableMatches} ADD COLUMN metadata LONGTEXT NULL AFTER notes_public");
            }
        } catch (Throwable $e) {
        }

        // Trainings
        $tableTrainings = $prefix.'trainings';
        try {
            $columnExists = DB::select("SHOW COLUMNS FROM {$tableTrainings} LIKE 'metadata'");
            if (empty($columnExists)) {
                DB::statement("ALTER TABLE {$tableTrainings} ADD COLUMN metadata LONGTEXT NULL AFTER notes");
            }
        } catch (Throwable $e) {
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            Schema::table('trainings', function (Blueprint $table) {
                $table->dropColumn('metadata');
            });

            Schema::table('matches', function (Blueprint $table) {
                $table->dropColumn('metadata');
            });

            return;
        }

        $prefix = DB::getTablePrefix();
        try {
            DB::statement("ALTER TABLE {$prefix}trainings DROP COLUMN IF EXISTS metadata");
            DB::statement("ALTER TABLE {$prefix}matches DROP COLUMN IF EXISTS metadata");
        } catch (Throwable $e) {
        }
    }
};
