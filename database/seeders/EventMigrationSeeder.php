<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class EventMigrationSeeder extends Seeder
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

        $isFresh = config('app.seed_fresh', false);

        if ($isFresh) {
            $this->command->warn('Režim FRESH: Mažu existující události (zápasy, tréninky, klubové akce, statistiky a docházku)...');
            \Illuminate\Support\Facades\Schema::disableForeignKeyConstraints();

            // Smažeme všechna data v cílových tabulkách, abychom začali s čistým štítem
            // Toto vymaže i data, která nebyla migrována z legacy (včetně statistik a docházky)
            \App\Models\StatisticRow::truncate();
            \App\Models\StatisticSet::truncate();
            \App\Models\Attendance::truncate();
            \App\Models\MatchPrediction::truncate();
            \App\Models\ExternalPlayerMatch::truncate();

            \App\Models\BasketballMatch::truncate();
            \Illuminate\Support\Facades\DB::table('basketball_match_team')->truncate();

            \App\Models\Training::truncate();
            \Illuminate\Support\Facades\DB::table('team_training')->truncate();

            \App\Models\ClubEvent::truncate();
            \Illuminate\Support\Facades\DB::table('club_event_team')->truncate();

            \Illuminate\Support\Facades\Schema::enableForeignKeyConstraints();
            $this->command->info('Data byla smazána.');
        }

        $this->command->info('Načítám zápasy a tréninky ze staré DB...');

        try {
            $oldEvents = \Illuminate\Support\Facades\DB::connection('old_mysql')->table('zapasy')->get();
            $seasons = \App\Models\Season::all()->keyBy('name');
            $teamC = \App\Models\Team::where('slug', 'muzi-c')->first();
            $teamE = \App\Models\Team::where('slug', 'muzi-e')->first();

            // Načtení existujících událostí do paměti pro zamezení JSON dotazům v databázi
            $existingMatches = \App\Models\BasketballMatch::all()->keyBy(fn ($m) => $m->metadata['legacy_z_id'] ?? null)->forget(null);
            $existingTrainings = \App\Models\Training::all()->keyBy(fn ($t) => $t->metadata['legacy_z_id'] ?? null)->forget(null);
            $existingClubEvents = \App\Models\ClubEvent::all()->keyBy(fn ($e) => $e->metadata['legacy_z_id'] ?? null)->forget(null);

            if (! $teamC || ! $teamE) {
                $this->command->error('Týmy C nebo E nebyly nalezeny.');

                return;
            }

            $bar = $this->command->getOutput()->createProgressBar($oldEvents->count());
            $bar->start();

            foreach ($oldEvents as $old) {
                try {
                    // Normalizace názvu sezóny a formátu
                    $seasonName = $old->sezona ? trim(str_replace(['-', ' '], ['/', ''], $old->sezona)) : null;

                    // Pokud u historického záznamu chybí název sezóny, odvodíme jej podle data (sezóna začíná 1. září)
                    if (! $seasonName && $old->datum) {
                        $date = \Carbon\Carbon::parse($old->datum);
                        $year = $date->year;
                        if ($date->month < 9) {
                            $seasonName = ($year - 1).'/'.$year;
                        } else {
                            $seasonName = $year.'/'.($year + 1);
                        }
                    }

                    // Určení sezóny v DB
                    if ($seasonName) {
                        $season = $seasons->get($seasonName);
                        if (! $season) {
                            $season = \App\Models\Season::updateOrCreate(['name' => $seasonName], ['is_active' => false]);
                            $seasons->put($seasonName, $season);
                        }
                    } else {
                        $season = null;
                    }

                    // Mapování týmu
                    $targetTeamIds = match ((int) $old->team) {
                        1 => [$teamC->id],
                        2 => [$teamE->id],
                        3 => [$teamC->id, $teamE->id],
                        default => [$teamC->id], // Fallback
                    };

                    // Pro zápas potřebujeme jeden hlavní team_id
                    $mainTeamId = $targetTeamIds[0];

                    // Normalizace času
                    $time = $old->cas ?: '00:00';
                    if (strlen($time) === 4 && str_contains($time, ':')) {
                        $time = '0'.$time;
                    }
                    $scheduledAt = \Carbon\Carbon::parse($old->datum.' '.$time);

                    $matchTypes = ['MI', 'PO', 'PRATEL'];
                    if (in_array($old->druh, $matchTypes)) {
                        // Migrace zápasu
                        $this->migrateMatch($old, $targetTeamIds, $season?->id, $scheduledAt, $existingMatches->get($old->id));
                    } elseif ($old->druh === 'TR') {
                        // Migrace tréninku
                        $this->migrateTraining($old, $targetTeamIds, $scheduledAt, $existingTrainings->get($old->id));
                    } elseif (in_array($old->druh, ['ALL', 'TUR'])) {
                        // Migrace klubové akce (včetně turnajů)
                        $this->migrateClubEvent($old, $targetTeamIds, $scheduledAt, $existingClubEvents->get($old->id));
                    } else {
                        // Fallback pro ostatní typy (např. TR, pokud tam bylo dříve něco jiného)
                        $this->migrateTraining($old, $targetTeamIds, $scheduledAt, $existingTrainings->get($old->id));
                    }
                } catch (\Exception $e) {
                    $this->command->error("\nChyba u záznamu ID {$old->id}: ".$e->getMessage());

                    continue;
                }

                $bar->advance();
            }

            $bar->finish();
            $this->command->info("\nMigrace událostí dokončena.");

        } catch (\Exception $e) {
            $this->command->error("\nChyba při migraci událostí: ".$e->getMessage());
        }
    }

    protected function migrateMatch($old, $teamIds, $seasonId, $scheduledAt, $existing = null)
    {
        $teamIds = (array) $teamIds;
        // Najít nebo vytvořit soupeře
        $opponentName = trim($old->souper);
        $opponentId = null;
        if ($opponentName) {
            $opponent = \App\Models\Opponent::firstOrCreate(['name' => $opponentName]);
            $opponentId = $opponent->id;
        }

        // Rozparsování výsledku (např. "85:72")
        $scoreHome = null;
        $scoreAway = null;
        if ($old->vysledek && str_contains($old->vysledek, ':')) {
            $parts = explode(':', $old->vysledek);
            $s1 = (int) trim($parts[0]);
            $s2 = (int) trim($parts[1]);

            if ($old->kde === 'doma') {
                $scoreHome = $s1;
                $scoreAway = $s2;
            } else {
                // Venku: ve staré DB je to "naši:soupeř", ale v nové home/away
                // Takže naši (hosté) jsou scoreAway, soupeř (domácí) je scoreHome
                $scoreHome = $s2;
                $scoreAway = $s1;
            }
        }

        $status = 'scheduled';
        if ($old->vysledek) {
            $status = 'completed';
        } elseif ($scheduledAt->isPast() && $scheduledAt->diffInHours(now()) > 2) {
            $status = 'played';
        }

        $matchData = [
            'team_id' => $teamIds[0], // Ponecháme i původní team_id pro kompatibilitu
            'season_id' => $seasonId,
            'opponent_id' => $opponentId,
            'scheduled_at' => $scheduledAt,
            'match_type' => match ($old->druh) {
                'MI' => 'mistrovske',
                'PO' => 'poharove',
                'PRATEL' => 'pratelske',
                default => 'mistrovske',
            },
            'location' => $old->adresa ?: ($old->kde === 'doma' ? 'Kbely' : null),
            'is_home' => $old->kde === 'doma',
            'status' => $status,
            'score_home' => $scoreHome,
            'score_away' => $scoreAway,
            'notes_internal' => "Původní ID: {$old->id}\nSport: {$old->sport}",
            'metadata' => ['legacy_z_id' => (int) $old->id],
        ];

        if (! $existing && $scheduledAt && !empty($teamIds)) {
            $existing = \App\Models\BasketballMatch::where('team_id', $teamIds[0])
                ->where('season_id', $seasonId)
                ->where('scheduled_at', '>=', $scheduledAt->copy()->subMinutes(120)->toDateTimeString())
                ->where('scheduled_at', '<=', $scheduledAt->copy()->addMinutes(120)->toDateTimeString())
                ->first();
        }

        if ($existing) {
            // Sloučíme metadata, aby se zachovalo např. external_id pokud tam už je
            $metadata = $existing->metadata ?? [];
            $metadata['legacy_z_id'] = (int) $old->id;
            $matchData['metadata'] = $metadata;

            $existing->update($matchData);
            $match = $existing;
        } else {
            $match = \App\Models\BasketballMatch::create($matchData);
        }

        $match->team_id = $teamIds[0] ?? null;
        $match->save();
    }

    protected function migrateTraining($old, $teamIds, $scheduledAt, $existing = null)
    {
        $teamIds = (array) $teamIds;
        $trainingData = [
            'location' => $old->adresa ?: 'Kbely',
            'starts_at' => $scheduledAt,
            'ends_at' => $scheduledAt->copy()->addMinutes(90),
            'notes' => "Původní ID: {$old->id}\nDruh: {$old->druh}",
            'metadata' => ['legacy_z_id' => (int) $old->id],
        ];

        if (! $existing && $scheduledAt && !empty($teamIds)) {
            $existing = \App\Models\Training::whereHas('teams', function ($q) use ($teamIds) {
                $q->whereIn('teams.id', $teamIds);
            })
                ->where('starts_at', '>=', $scheduledAt->copy()->subMinutes(60)->toDateTimeString())
                ->where('starts_at', '<=', $scheduledAt->copy()->addMinutes(60)->toDateTimeString())
                ->first();
        }

        if ($existing) {
            $metadata = $existing->metadata ?? [];
            $metadata['legacy_z_id'] = (int) $old->id;
            $trainingData['metadata'] = $metadata;

            $existing->update($trainingData);
            $training = $existing;
        } else {
            $training = \App\Models\Training::create($trainingData);
        }

        $training->teams()->syncWithoutDetaching($teamIds);
    }

    protected function migrateClubEvent($old, $teamIds, $scheduledAt, $existing = null)
    {
        $teamIds = (array) $teamIds;
        $event = $existing;

        if (! $event && $scheduledAt) {
            $event = \App\Models\ClubEvent::where('starts_at', '>=', $scheduledAt->copy()->subMinutes(60)->toDateTimeString())
                ->where('starts_at', '<=', $scheduledAt->copy()->addMinutes(60)->toDateTimeString())
                ->first();
        }

        if (! $event) {
            $event = new \App\Models\ClubEvent;
            $event->metadata = ['legacy_z_id' => (int) $old->id];
        } else {
            $metadata = $event->metadata ?? [];
            $metadata['legacy_z_id'] = (int) $old->id;
            $event->metadata = $metadata;
        }

        $event->title = [
            'cs' => $old->souper ?: 'Klubová akce',
            'en' => $old->souper ?: 'Club Event',
        ];
        $event->event_type = match ($old->druh) {
            'TUR' => 'tournament',
            'ALL' => 'all',
            default => 'other',
        };
        $event->location = $old->adresa ?: 'Kbely';
        $event->starts_at = $scheduledAt;
        $event->ends_at = $scheduledAt->copy()->addMinutes(120);
        $event->description = [
            'cs' => "Původní ID: {$old->id}\nSport: {$old->sport}",
            'en' => "Legacy ID: {$old->id}\nSport: {$old->sport}",
        ];
        $event->is_public = true;
        $event->save();

        $event->teams()->syncWithoutDetaching($teamIds);
    }
}
