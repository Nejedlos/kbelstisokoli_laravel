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
            Schema::table('external_player_matches', function (Blueprint $table) {
                $table->foreignId('basketball_match_id')->nullable()->after('user_id')->constrained('matches')->nullOnDelete();
            });

            return;
        }

        $prefix = DB::getTablePrefix();
        $table = $prefix.'external_player_matches';
        $matchesTable = $prefix.'matches';

        try {
            DB::statement("ALTER TABLE {$table} ADD COLUMN basketball_match_id BIGINT UNSIGNED NULL AFTER user_id");
            DB::statement("ALTER TABLE {$table} ADD CONSTRAINT fk_player_match_id FOREIGN KEY (basketball_match_id) REFERENCES {$matchesTable}(id) ON DELETE SET NULL");
        } catch (Throwable $e) {
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            Schema::table('external_player_matches', function (Blueprint $table) {
                $table->dropConstrainedForeignId('basketball_match_id');
            });

            return;
        }

        $prefix = DB::getTablePrefix();
        $table = $prefix.'external_player_matches';

        try {
            DB::statement("ALTER TABLE {$table} DROP FOREIGN KEY fk_player_match_id");
            DB::statement("ALTER TABLE {$table} DROP COLUMN basketball_match_id");
        } catch (Throwable $e) {
        }
    }
};
