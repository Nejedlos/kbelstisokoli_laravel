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
            Schema::table('users', function (Blueprint $table) {
                $table->longText('metadata')->nullable()->after('onboarding_completed_at');
            });

            return;
        }

        $prefix = \Illuminate\Support\Facades\DB::getTablePrefix();
        $table = $prefix . 'users';

        try {
            $columnExists = \Illuminate\Support\Facades\DB::select("SHOW COLUMNS FROM {$table} LIKE 'metadata'");
            if (empty($columnExists)) {
                \Illuminate\Support\Facades\DB::statement("ALTER TABLE {$table} ADD COLUMN metadata LONGTEXT NULL AFTER onboarding_completed_at");
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
        if (\Illuminate\Support\Facades\DB::getDriverName() === 'sqlite') {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('metadata');
            });

            return;
        }

        $prefix = \Illuminate\Support\Facades\DB::getTablePrefix();
        $table = $prefix . 'users';

        try {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE {$table} DROP COLUMN IF EXISTS metadata");
        } catch (\Throwable $e) {
            // Ignore
        }
    }
};
