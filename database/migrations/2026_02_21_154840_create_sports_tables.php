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
        // 1. Seasons (Sezóny)
        if (!Schema::hasTable('seasons')) {
            Schema::create('seasons', function (Blueprint $table) {
                $table->id();
                $table->string('name'); // např. 2023/2024
                $table->boolean('is_active')->default(false);
                $table->timestamps();
            });
        }

        // 2. Teams (Kategorie / Týmy)
        if (!Schema::hasTable('teams')) {
            Schema::create('teams', function (Blueprint $table) {
                $table->id();
                $table->string('name'); // např. U14 Kluci
                $table->string('slug')->unique();
                $table->string('category')->nullable(); // např. youth, senior
                $table->text('description')->nullable();
                $table->timestamps();
            });
        }

        // 3. Opponents (Soupeři)
        if (!Schema::hasTable('opponents')) {
            Schema::create('opponents', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('city')->nullable();
                $table->string('logo')->nullable(); // Reference na soubor (Media Library)
                $table->timestamps();
            });
        }

        // 4. Matches (Zápasy)
        if (!Schema::hasTable('matches')) {
            Schema::create('matches', function (Blueprint $table) {
                $table->id();
                $table->foreignId('team_id')->constrained()->onDelete('cascade');
                $table->foreignId('season_id')->constrained()->onDelete('cascade');
                $table->foreignId('opponent_id')->constrained()->onDelete('cascade');
                $table->dateTime('scheduled_at');
                $table->string('location')->nullable();
                $table->boolean('is_home')->default(true);
                $table->string('status')->default('planned'); // planned, completed, cancelled, postponed
                $table->integer('score_home')->nullable();
                $table->integer('score_away')->nullable();
                $table->text('notes_internal')->nullable();
                $table->text('notes_public')->nullable();
                $table->timestamps();
            });
        }

        // 5. Trainings (Tréninky - jednotlivé termíny)
        if (!Schema::hasTable('trainings')) {
            Schema::create('trainings', function (Blueprint $table) {
                $table->id();
                $table->foreignId('team_id')->constrained()->onDelete('cascade');
                $table->string('location')->nullable();
                $table->dateTime('starts_at');
                $table->dateTime('ends_at');
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        // 6. Events (Klubové akce)
        if (!Schema::hasTable('events') && !Schema::hasTable('club_events')) {
            Schema::create('events', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->text('description')->nullable();
                $table->string('location')->nullable();
                $table->dateTime('starts_at');
                $table->dateTime('ends_at');
                $table->boolean('is_public')->default(true);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
        Schema::dropIfExists('trainings');
        Schema::dropIfExists('matches');
        Schema::dropIfExists('opponents');
        Schema::dropIfExists('teams');
        Schema::dropIfExists('seasons');
    }
};
