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
        if (! Schema::hasTable('basketball_match_team')) {
            Schema::create('basketball_match_team', function (Blueprint $table) {
                $table->id();
                $table->foreignId('basketball_match_id')->constrained('matches')->onDelete('cascade');
                $table->foreignId('team_id')->constrained()->onDelete('cascade');
                $table->timestamps();
            });
        }

        // Migrace dat z team_id do nové pivot tabulky
        if (Schema::hasTable('matches') && Schema::hasColumn('matches', 'team_id')) {
            $matches = DB::table('matches')->whereNotNull('team_id')->get();
            foreach ($matches as $match) {
                // Zkontrolujeme, zda už záznam neexistuje
                $exists = DB::table('basketball_match_team')
                    ->where('basketball_match_id', $match->id)
                    ->where('team_id', $match->team_id)
                    ->exists();

                if (! $exists) {
                    DB::table('basketball_match_team')->insert([
                        'basketball_match_id' => $match->id,
                        'team_id' => $match->team_id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        // Změna team_id na nullable (pro zachování kompatibility během přechodu)
        if (Schema::hasTable('matches') && Schema::hasColumn('matches', 'team_id')) {
            Schema::table('matches', function (Blueprint $table) {
                $table->foreignId('team_id')->nullable()->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('basketball_match_team');

        Schema::table('matches', function (Blueprint $table) {
            $table->foreignId('team_id')->nullable(false)->change();
        });
    }
};
