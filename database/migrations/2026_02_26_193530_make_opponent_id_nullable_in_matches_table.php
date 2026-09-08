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
                $table->foreignId('opponent_id')->nullable()->change();
            });

            return;
        }

        $prefix = DB::getTablePrefix();
        $table = $prefix.'matches';

        try {
            DB::statement("ALTER TABLE {$table} MODIFY opponent_id BIGINT UNSIGNED NULL");
        } catch (Throwable $e) {
            // Ignore
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            Schema::table('matches', function (Blueprint $table) {
                $table->foreignId('opponent_id')->nullable(false)->change();
            });

            return;
        }

        $prefix = DB::getTablePrefix();
        $table = $prefix.'matches';

        try {
            DB::statement("ALTER TABLE {$table} MODIFY opponent_id BIGINT UNSIGNED NOT NULL");
        } catch (Throwable $e) {
            // Ignore
        }
    }
};
