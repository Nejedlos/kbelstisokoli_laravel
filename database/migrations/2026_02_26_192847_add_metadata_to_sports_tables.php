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
        if (\Illuminate\Support\Facades\DB::getDriverName() === 'sqlite') {
            Schema::table('matches', function (Blueprint $table) {
                $table->longText('metadata')->nullable()->after('notes_public');
            });

            Schema::table('trainings', function (Blueprint $table) {
                $table->longText('metadata')->nullable()->after('notes');
            });

            return;
        }

        $prefix = \Illuminate\Support\Facades\DB::getTablePrefix();

        // Matches
        $tableMatches = $prefix.'matches';
        try {
            $columnExists = \Illuminate\Support\Facades\DB::select("SHOW COLUMNS FROM {$tableMatches} LIKE 'metadata'");
            if (empty($columnExists)) {
                \Illuminate\Support\Facades\DB::statement("ALTER TABLE {$tableMatches} ADD COLUMN metadata LONGTEXT NULL AFTER notes_public");
            }
        } catch (\Throwable $e) {
        }

        // Trainings
        $tableTrainings = $prefix.'trainings';
        try {
            $columnExists = \Illuminate\Support\Facades\DB::select("SHOW COLUMNS FROM {$tableTrainings} LIKE 'metadata'");
            if (empty($columnExists)) {
                \Illuminate\Support\Facades\DB::statement("ALTER TABLE {$tableTrainings} ADD COLUMN metadata LONGTEXT NULL AFTER notes");
            }
        } catch (\Throwable $e) {
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (\Illuminate\Support\Facades\DB::getDriverName() === 'sqlite') {
            Schema::table('trainings', function (Blueprint $table) {
                $table->dropColumn('metadata');
            });

            Schema::table('matches', function (Blueprint $table) {
                $table->dropColumn('metadata');
            });

            return;
        }

        $prefix = \Illuminate\Support\Facades\DB::getTablePrefix();
        try {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE {$prefix}trainings DROP COLUMN IF EXISTS metadata");
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE {$prefix}matches DROP COLUMN IF EXISTS metadata");
        } catch (\Throwable $e) {
        }
    }
};
