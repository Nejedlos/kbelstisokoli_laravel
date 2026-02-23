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
        // 1. Club Competitions (Klubové soutěže typu Lumír Trophy)
        if (!Schema::hasTable('club_competitions')) {
            Schema::create('club_competitions', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->text('description')->nullable();
                $table->string('metric_description')->nullable(); // např. "Nejlepší střelec sezóny"
                $table->foreignId('season_id')->nullable()->constrained('seasons')->onDelete('set null');
                $table->text('rules')->nullable();
                $table->boolean('is_public')->default(true);
                $table->string('status')->default('active'); // active, completed, archived
                $table->timestamps();
            });
        }

        // 2. Club Competition Entries (Jednotlivé body/záznamy soutěže)
        if (!Schema::hasTable('club_competition_entries')) {
            Schema::create('club_competition_entries', function (Blueprint $table) {
                $table->id();
                $table->foreignId('club_competition_id')->constrained()->onDelete('cascade');
                $table->foreignId('player_id')->nullable()->constrained('users')->onDelete('set null');
                $table->foreignId('team_id')->nullable()->constrained('teams')->onDelete('set null');
                $table->string('label')->nullable(); // Např. název týmu nebo jméno, které není v DB
                $table->decimal('value', 10, 2)->default(0); // Číselná hodnota (skóre)
                $table->string('value_type')->default('incremental'); // incremental, absolute
                $table->string('source_note')->nullable(); // Poznámka k původu (z jakého zápasu/akce)
                $table->foreignId('basketball_match_id')->nullable()->constrained('matches')->onDelete('set null');
                $table->longText('metadata')->nullable(); // Libovolná doplňující metadata
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('club_competition_entries');
        Schema::dropIfExists('club_competitions');
    }
};
