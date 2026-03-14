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
            Schema::table('external_player_matches', function (Blueprint $table) {
                $table->foreignId('basketball_match_id')->nullable()->after('user_id')->constrained('matches')->nullOnDelete();
            });

            return;
        }

        $prefix = \Illuminate\Support\Facades\DB::getTablePrefix();
        $table = $prefix.'external_player_matches';
        $matchesTable = $prefix.'matches';

        try {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE {$table} ADD COLUMN basketball_match_id BIGINT UNSIGNED NULL AFTER user_id");
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE {$table} ADD CONSTRAINT fk_player_match_id FOREIGN KEY (basketball_match_id) REFERENCES {$matchesTable}(id) ON DELETE SET NULL");
        } catch (\Throwable $e) {}
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (\Illuminate\Support\Facades\DB::getDriverName() === 'sqlite') {
            Schema::table('external_player_matches', function (Blueprint $table) {
                $table->dropConstrainedForeignId('basketball_match_id');
            });

            return;
        }

        $prefix = \Illuminate\Support\Facades\DB::getTablePrefix();
        $table = $prefix.'external_player_matches';

        try {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE {$table} DROP FOREIGN KEY fk_player_match_id");
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE {$table} DROP COLUMN basketball_match_id");
        } catch (\Throwable $e) {}
    }
};
