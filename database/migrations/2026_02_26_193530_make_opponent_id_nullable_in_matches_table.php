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
                $table->foreignId('opponent_id')->nullable()->change();
            });

            return;
        }

        $prefix = \Illuminate\Support\Facades\DB::getTablePrefix();
        $table = $prefix.'matches';

        try {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE {$table} MODIFY opponent_id BIGINT UNSIGNED NULL");
        } catch (\Throwable $e) {
            // Ignore
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (\Illuminate\Support\Facades\DB::getDriverName() === 'sqlite') {
            Schema::table('matches', function (Blueprint $table) {
                $table->foreignId('opponent_id')->nullable(false)->change();
            });

            return;
        }

        $prefix = \Illuminate\Support\Facades\DB::getTablePrefix();
        $table = $prefix.'matches';

        try {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE {$table} MODIFY opponent_id BIGINT UNSIGNED NOT NULL");
        } catch (\Throwable $e) {
            // Ignore
        }
    }
};
