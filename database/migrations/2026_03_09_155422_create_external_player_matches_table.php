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
        Schema::create('external_player_matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('source_key')->default('czbasketball');
            $table->string('external_id')->nullable(); // ID hráče
            $table->string('external_match_id')->nullable(); // ID zápasu na webu
            $table->date('match_date')->nullable();
            $table->string('competition_label')->nullable();
            $table->string('opponent_name')->nullable();

            $table->integer('points')->default(0);
            $table->integer('two_points_made')->nullable();
            $table->integer('two_points_attempts')->nullable();
            $table->integer('three_points_made')->nullable();
            $table->integer('three_points_attempts')->nullable();
            $table->integer('free_throws_made')->nullable();
            $table->integer('free_throws_attempts')->nullable();
            $table->decimal('free_throws_pct', 5, 2)->nullable();
            $table->integer('fouls')->nullable();
            $table->integer('minutes')->nullable();
            $table->integer('valuation')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'source_key']);
            $table->index(['external_match_id', 'source_key']);
            $table->unique(['user_id', 'source_key', 'external_match_id', 'match_date', 'opponent_name'], 'unique_player_match');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('external_player_matches');
    }
};
