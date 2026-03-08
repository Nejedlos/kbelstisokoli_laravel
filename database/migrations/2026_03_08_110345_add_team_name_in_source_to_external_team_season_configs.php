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
            $table->string('team_name_in_source')->nullable()->after('competition_label');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('external_team_season_configs', function (Blueprint $table) {
            $table->dropColumn('team_name_in_source');
        });
    }
};
