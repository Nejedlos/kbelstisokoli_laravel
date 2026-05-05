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
        Schema::table('statistic_rows', function (Blueprint $table) {
            // Složené indexy pro časté filtrování statistik
            $table->index(['team_id', 'season_id', 'statistic_set_id'], 'idx_stats_team_season_set');
            $table->index(['player_id', 'season_id', 'statistic_set_id'], 'idx_stats_player_season_set');
            $table->index(['basketball_match_id', 'statistic_set_id'], 'idx_stats_match_set');

            // Index pro řazení podle bodů (MySQL 8 Functional Index)
            // Poznámka: Laravel Blueprint přímo nepodporuje functional indexy přes ->index(),
            // proto použijeme RAW statement pokud jsme na MySQL 8.
        });

        // Přidání indexů pro zápasy
        Schema::table('matches', function (Blueprint $table) {
            $table->index(['team_id', 'season_id', 'status'], 'idx_matches_team_season_status');
            $table->index(['scheduled_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('statistic_rows', function (Blueprint $table) {
            $table->dropIndex('idx_stats_team_season_set');
            $table->dropIndex('idx_stats_player_season_set');
            $table->dropIndex('idx_stats_match_set');
        });

        Schema::table('matches', function (Blueprint $table) {
            $table->dropIndex('idx_matches_team_season_status');
            $table->dropIndex('idx_matches_scheduled_at');
        });
    }
};
