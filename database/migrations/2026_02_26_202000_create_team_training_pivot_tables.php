<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Vytvoření pivot tabulek
        Schema::create('team_training', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('training_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('club_event_team', function (Blueprint $table) {
            $table->id();
            $table->foreignId('club_event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });

        // 2. Přelití dat ze sloupců team_id do pivot tabulek
        DB::table('trainings')->whereNotNull('team_id')->orderBy('id')->each(function ($training) {
            DB::table('team_training')->insert([
                'team_id' => $training->team_id,
                'training_id' => $training->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        DB::table('club_events')->whereNotNull('team_id')->orderBy('id')->each(function ($event) {
            DB::table('club_event_team')->insert([
                'club_event_id' => $event->id,
                'team_id' => $event->team_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        // 3. Odstranění starých sloupců
        Schema::table('trainings', function (Blueprint $table) {
            $table->dropForeign(['team_id']);
            $table->dropColumn('team_id');
        });

        Schema::table('club_events', function (Blueprint $table) {
            $table->dropForeign(['team_id']);
            $table->dropColumn('team_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Obnova sloupců (nullable pro začátek)
        Schema::table('trainings', function (Blueprint $table) {
            $table->foreignId('team_id')->nullable()->constrained()->onDelete('cascade');
        });

        Schema::table('club_events', function (Blueprint $table) {
            $table->foreignId('team_id')->nullable()->constrained()->onDelete('set null');
        });

        // 2. Zpětné přelití dat (vezme se první přiřazený tým)
        DB::table('team_training')->orderBy('id', 'asc')->each(function ($pivot) {
            DB::table('trainings')->where('id', $pivot->training_id)->update(['team_id' => $pivot->team_id]);
        });

        DB::table('club_event_team')->orderBy('id', 'asc')->each(function ($pivot) {
            DB::table('club_events')->where('id', $pivot->club_event_id)->update(['team_id' => $pivot->team_id]);
        });

        // 3. Odstranění pivot tabulek
        Schema::dropIfExists('team_training');
        Schema::dropIfExists('club_event_team');
    }
};
