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
        Schema::create('team_elo_ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('season_id')->constrained('seasons')->onDelete('cascade');
            $table->string('team_key'); // 'team_{id}' or 'opp_{id}'
            $table->float('rating')->default(1500);
            $table->dateTime('last_match_at')->nullable();
            $table->timestamps();

            $table->unique(['season_id', 'team_key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('team_elo_ratings');
    }
};
