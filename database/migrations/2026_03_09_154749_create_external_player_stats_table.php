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
        Schema::create('external_player_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('source_key')->default('czbasketball'); // např. czbasketball
            $table->string('external_id')->nullable(); // ID hráče na webu (pro rychlé hledání)
            $table->string('season_label')->nullable(); // např. 2024/25
            $table->string('competition_label')->nullable(); // např. muži - Základní fáze
            $table->string('team_name')->nullable();

            $table->integer('games_played')->default(0);
            $table->decimal('minutes_avg', 5, 2)->nullable();
            $table->decimal('points_avg', 5, 2)->nullable();
            $table->decimal('two_points_made_avg', 5, 2)->nullable();
            $table->decimal('two_points_attempts_avg', 5, 2)->nullable(); // Pokud je M/A
            $table->decimal('two_points_pct', 5, 2)->nullable();
            $table->decimal('three_points_made_avg', 5, 2)->nullable();
            $table->decimal('three_points_attempts_avg', 5, 2)->nullable();
            $table->decimal('three_points_pct', 5, 2)->nullable();
            $table->decimal('free_throws_made_avg', 5, 2)->nullable();
            $table->decimal('free_throws_attempts_avg', 5, 2)->nullable();
            $table->decimal('free_throws_pct', 5, 2)->nullable();

            $table->decimal('rebounds_offensive_avg', 5, 2)->nullable();
            $table->decimal('rebounds_defensive_avg', 5, 2)->nullable();
            $table->decimal('rebounds_total_avg', 5, 2)->nullable();
            $table->decimal('assists_avg', 5, 2)->nullable();
            $table->decimal('steals_avg', 5, 2)->nullable();
            $table->decimal('turnovers_avg', 5, 2)->nullable();
            $table->decimal('blocks_avg', 5, 2)->nullable();
            $table->decimal('fouls_avg', 5, 2)->nullable();
            $table->decimal('fouls_received_avg', 5, 2)->nullable();
            $table->decimal('valuation_avg', 5, 2)->nullable();
            $table->decimal('plus_minus_avg', 5, 2)->nullable();

            $table->boolean('is_career_total')->default(false); // Příznak pro celkový součet kariéry
            $table->timestamps();

            $table->index(['user_id', 'source_key']);
            $table->index(['external_id', 'source_key']);
            // Unikátní index pro sezónu, soutěž a tým, aby se data neopakovala
            $table->unique(['user_id', 'source_key', 'season_label', 'competition_label', 'team_name', 'is_career_total'], 'unique_player_stat_row');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('external_player_stats');
    }
};
