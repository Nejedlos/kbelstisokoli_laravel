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
        // 1. Zjistíme existující indexy a FK
        $conn = Schema::getConnection();
        $dbName = $conn->getDatabaseName();
        $isSqlite = config('database.default') === 'sqlite';
        $prefix = \Illuminate\Support\Facades\DB::getTablePrefix();

        if ($isSqlite) {
            $hasUnique = false; // V testech/SQLite nepotřebujeme takto detekovat
            $hasForeign = false;
        } else {
            $hasUnique = count(\Illuminate\Support\Facades\DB::select("SHOW INDEX FROM {$prefix}player_profiles WHERE Key_name = 'player_profiles_user_id_unique'")) > 0;
            $hasForeign = count(\Illuminate\Support\Facades\DB::select("
                SELECT CONSTRAINT_NAME
                FROM information_schema.KEY_COLUMN_USAGE
                WHERE TABLE_SCHEMA = ? AND TABLE_NAME = '{$prefix}player_profiles' AND CONSTRAINT_NAME = 'player_profiles_user_id_foreign'
            ", [$dbName])) > 0;
        }

        if ($isSqlite) {
            Schema::table('player_profiles', function (Blueprint $table) use ($hasUnique, $hasForeign) {
                if ($hasForeign) {
                    $table->dropForeign(['user_id']);
                }
                if ($hasUnique) {
                    $table->dropUnique(['user_id']);
                }

                // Přidání časové platnosti profilu (pokud neexistují)
                if (! Schema::hasColumn('player_profiles', 'valid_from')) {
                    $table->date('valid_from')->nullable()->after('is_active');
                }
                if (! Schema::hasColumn('player_profiles', 'valid_to')) {
                    $table->date('valid_to')->nullable()->after('valid_from');
                }
            });

            return;
        }

        // MySQL/MariaDB (Webglobe)
        $table = $prefix.'player_profiles';
        try {
            if ($hasForeign) {
                \Illuminate\Support\Facades\DB::statement("ALTER TABLE {$table} DROP FOREIGN KEY player_profiles_user_id_foreign");
            }
            if ($hasUnique) {
                \Illuminate\Support\Facades\DB::statement("ALTER TABLE {$table} DROP INDEX player_profiles_user_id_unique");
            }

            // valid_from
            $columnExists = \Illuminate\Support\Facades\DB::select("SHOW COLUMNS FROM {$table} LIKE 'valid_from'");
            if (empty($columnExists)) {
                \Illuminate\Support\Facades\DB::statement("ALTER TABLE {$table} ADD COLUMN valid_from DATE NULL AFTER is_active");
            }

            // valid_to
            $columnExists = \Illuminate\Support\Facades\DB::select("SHOW COLUMNS FROM {$table} LIKE 'valid_to'");
            if (empty($columnExists)) {
                \Illuminate\Support\Facades\DB::statement("ALTER TABLE {$table} ADD COLUMN valid_to DATE NULL AFTER valid_from");
            }

            // Add foreign key back
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE {$table} ADD CONSTRAINT player_profiles_user_id_foreign FOREIGN KEY (user_id) REFERENCES {$prefix}users(id) ON DELETE CASCADE");
        } catch (\Throwable $e) {
            // Log or ignore
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (\Illuminate\Support\Facades\DB::getDriverName() === 'sqlite') {
            Schema::table('player_profiles', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
                $table->unique(['user_id']);
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->dropColumn(['valid_from', 'valid_to']);
            });

            return;
        }

        $prefix = \Illuminate\Support\Facades\DB::getTablePrefix();
        $table = $prefix.'player_profiles';
        try {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE {$table} DROP FOREIGN KEY player_profiles_user_id_foreign");
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE {$table} ADD UNIQUE (user_id)");
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE {$table} ADD CONSTRAINT player_profiles_user_id_foreign FOREIGN KEY (user_id) REFERENCES {$prefix}users(id) ON DELETE CASCADE");
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE {$table} DROP COLUMN IF EXISTS valid_from");
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE {$table} DROP COLUMN IF EXISTS valid_to");
        } catch (\Throwable $e) {
            // Ignore
        }
    }
};
