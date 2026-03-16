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
        Schema::table('external_player_matches', function (Blueprint $table) {
            $table->timestamp('boxscore_synced_at')->nullable()->after('metadata');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('external_player_matches', function (Blueprint $table) {
            $table->dropColumn('boxscore_synced_at');
        });
    }
};
