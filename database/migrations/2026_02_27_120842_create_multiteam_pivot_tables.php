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
        // photo_pool_team
        if (!Schema::hasTable('photo_pool_team')) {
            Schema::create('photo_pool_team', function (Blueprint $table) {
                $table->id();
                $table->foreignId('photo_pool_id')->constrained()->cascadeOnDelete();
                $table->foreignId('team_id')->constrained()->cascadeOnDelete();
                $table->timestamps();
            });
        }

        // club_competition_entry_team
        if (!Schema::hasTable('club_competition_entry_team')) {
            Schema::create('club_competition_entry_team', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('entry_id');
                $table->foreign('entry_id', 'cce_team_entry_id_foreign')
                    ->references('id')->on('club_competition_entries')
                    ->cascadeOnDelete();
                $table->foreignId('team_id')->constrained()->cascadeOnDelete();
                $table->timestamps();
            });
        }

        // statistic_row_team
        if (!Schema::hasTable('statistic_row_team')) {
            Schema::create('statistic_row_team', function (Blueprint $table) {
                $table->id();
                $table->foreignId('statistic_row_id')->constrained()->cascadeOnDelete();
                $table->foreignId('team_id')->constrained()->cascadeOnDelete();
                $table->timestamps();
            });
        }

        // Data migration (PhotoPool)
        $photoPools = \Illuminate\Support\Facades\DB::table('photo_pools')->whereNotNull('team_id')->get();
        foreach ($photoPools as $pool) {
            \Illuminate\Support\Facades\DB::table('photo_pool_team')->insert([
                'photo_pool_id' => $pool->id,
                'team_id' => $pool->team_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Data migration (ClubCompetitionEntry)
        $entries = \Illuminate\Support\Facades\DB::table('club_competition_entries')->whereNotNull('team_id')->get();
        foreach ($entries as $entry) {
            \Illuminate\Support\Facades\DB::table('club_competition_entry_team')->insert([
                'entry_id' => $entry->id,
                'team_id' => $entry->team_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Data migration (StatisticRow)
        $rows = \Illuminate\Support\Facades\DB::table('statistic_rows')->whereNotNull('team_id')->get();
        foreach ($rows as $row) {
            \Illuminate\Support\Facades\DB::table('statistic_row_team')->insert([
                'statistic_row_id' => $row->id,
                'team_id' => $row->team_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('photo_pool_team');
        Schema::dropIfExists('club_competition_entry_team');
        Schema::dropIfExists('statistic_row_team');
    }
};
