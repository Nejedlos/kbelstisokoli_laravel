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
        Schema::table('player_profile_team', function (Blueprint $table) {
            $table->boolean('is_on_roster')->default(false)->after('is_primary_team');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('player_profile_team', function (Blueprint $table) {
            $table->dropColumn('is_on_roster');
        });
    }
};
