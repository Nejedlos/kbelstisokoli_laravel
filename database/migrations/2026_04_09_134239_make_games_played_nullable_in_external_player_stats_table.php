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
        Schema::table('external_player_stats', function (Blueprint $table) {
            $table->integer('games_played')->nullable()->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('external_player_stats', function (Blueprint $table) {
            $table->integer('games_played')->nullable(false)->default(0)->change();
        });
    }
};
