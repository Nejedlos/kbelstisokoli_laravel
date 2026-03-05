<?php

namespace Database\Seeders;

use App\Models\ClubCompetition;
use App\Models\ClubCompetitionEntry;
use App\Models\Season;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TrophyMigrationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $oldDb = config('database.old_database');
        if (! $oldDb) {
            $this->command->error('Databáze pro migraci nebyla nalezena (DB_DATABASE_OLD ani DB_DATABASE).');

            return;
        }

        $this->command->info('Migruji klubové trofeje ze staré DB...');

        $usersByName = User::all()->keyBy('name');
        $seasons = Season::all();

        $competitions = ClubCompetition::all();
        $entries = ClubCompetitionEntry::all();

        try {
            $oldTrophies = DB::connection('old_mysql')->table($oldDb.'.web_trophy')->get();

            foreach ($oldTrophies as $ot) {
                // Odhad sezóny podle data
                $date = \Carbon\Carbon::parse($ot->kdy);
                $year = $date->year;
                $month = $date->month;
                $seasonName = ($month >= 9) ? "$year/".($year + 1) : ($year - 1)."/$year";

                $season = $seasons->firstWhere('name', $seasonName);
                if (! $season) {
                    $season = Season::create(['name' => $seasonName, 'is_active' => false]);
                    $seasons->push($season);
                }

                $competition = $competitions->first(fn ($c) => $c->getTranslation('name', 'cs') === $ot->nazev && $c->season_id === $season->id);

                $competitionData = [
                    'name' => ['cs' => $ot->nazev],
                    'slug' => Str::slug($ot->nazev.'-'.$seasonName),
                    'description' => ['cs' => $ot->popis],
                    'is_public' => true,
                    'status' => 'completed',
                ];

                if ($competition) {
                    $competition->update($competitionData);
                } else {
                    $competition = ClubCompetition::create(array_merge(['season_id' => $season->id], $competitionData));
                    $competitions->push($competition);
                }

                // Zpracování oceněných (1., 2., 3. místo)
                $winners = [
                    1 => $ot->prvni,
                    2 => $ot->druhy,
                    3 => $ot->treti,
                ];

                foreach ($winners as $position => $winnerName) {
                    if (empty(trim($winnerName))) {
                        continue;
                    }

                    $winnerName = trim($winnerName);
                    $user = $usersByName->get($winnerName);

                    $entry = $entries->first(fn ($e) => $e->club_competition_id === $competition->id &&
                        ($e->metadata['legacy_trophy_id'] ?? null) == $ot->id &&
                        ($e->metadata['legacy_position'] ?? null) == $position
                    );

                    $entryData = [
                        'player_id' => $user?->id,
                        'label' => $user ? null : $winnerName,
                        'value' => (float) (4 - $position), // 1. místo = 3 body, 2. = 2 body, 3. = 1 bod
                        'value_type' => 'rank',
                        'source_note' => "Migrováno z trofejí: Pozice {$position}",
                        'metadata' => [
                            'legacy_trophy_id' => $ot->id,
                            'legacy_position' => $position,
                            'original_name' => $winnerName,
                        ],
                    ];

                    if ($entry) {
                        $entry->update($entryData);
                    } else {
                        $entry = ClubCompetitionEntry::create(array_merge([
                            'club_competition_id' => $competition->id,
                        ], $entryData));
                        $entries->push($entry);
                    }
                }
            }

            $this->command->info('Migrace trofejí dokončena.');

        } catch (\Exception $e) {
            $this->command->error('Chyba při migraci trofejí: '.$e->getMessage());
        }
    }
}
