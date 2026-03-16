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
        Schema::table('external_team_season_configs', function (Blueprint $table) {
            $table->string('competition_url')->nullable()->after('team_season_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('external_team_season_configs', function (Blueprint $table) {
            $table->dropColumn('competition_url');
        });
    }
};
