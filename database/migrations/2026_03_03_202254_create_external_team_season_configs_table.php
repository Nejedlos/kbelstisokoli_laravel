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
        Schema::create('external_team_season_configs', function (Blueprint $table) {
            $table->id();
            $table->string('source_key')->default('czbasketball');
            $table->foreignId('season_id')->constrained()->onDelete('cascade');
            $table->foreignId('team_id')->constrained()->onDelete('cascade');
            $table->string('external_team_id');
            $table->integer('external_season_year');
            $table->string('team_season_url');
            $table->string('matches_list_url');
            $table->string('competition_label')->nullable();
            $table->boolean('is_enabled')->default(true);
            $table->timestamp('last_synced_at')->nullable();
            $table->longText('metadata')->nullable();
            $table->timestamps();

            $table->unique(['source_key', 'season_id', 'team_id'], 'ext_team_season_unique');
            $table->index(['source_key', 'external_team_id', 'external_season_year'], 'ext_team_season_lookup_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('external_team_season_configs');
    }
};
