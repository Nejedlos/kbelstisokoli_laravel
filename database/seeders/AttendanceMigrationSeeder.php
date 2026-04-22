<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class AttendanceMigrationSeeder extends Seeder
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
            $this->command->warn('Režim FRESH: Mažu existující docházku...');
            \Illuminate\Support\Facades\Schema::disableForeignKeyConstraints();
            \App\Models\Attendance::truncate();
            \Illuminate\Support\Facades\Schema::enableForeignKeyConstraints();
        }

        $this->command->info('Načítám data o docházce ze staré DB...');

        try {
            // 1. Mapování uživatelů
            $usersById = \App\Models\User::all()->mapWithKeys(function ($user) {
                $legacyId = $user->metadata['legacy_r_id'] ?? null;

                return $legacyId ? [$legacyId => $user] : [];
            });

            $usersByName = \App\Models\User::all()->keyBy('name');

            // 2. Mapování událostí
            $matches = \App\Models\BasketballMatch::all()->mapWithKeys(function ($match) {
                $legacyId = $match->metadata['legacy_z_id'] ?? null;

                return $legacyId ? [(int) $legacyId => $match] : [];
            });

            $trainings = \App\Models\Training::all()->mapWithKeys(function ($training) {
                $legacyId = $training->metadata['legacy_z_id'] ?? null;

                return $legacyId ? [(int) $legacyId => $training] : [];
            });

            $clubEvents = \App\Models\ClubEvent::all()->mapWithKeys(function ($event) {
                $legacyId = $event->metadata['legacy_z_id'] ?? null;

                return $legacyId ? [(int) $legacyId => $event] : [];
            });

            // 2.5 Mapování plátců (pro reálnou docházku)
            $platiciToRid = \Illuminate\Support\Facades\DB::connection('old_mysql')->table('web_platici')
                ->select('id', 'r_id')
                ->get()
                ->pluck('r_id', 'id');

            // 3. Migrace docházky (tabulka dochazka)
            $this->migrateRsvp($oldDb, $usersById, $matches, $trainings, $clubEvents);

            // 4. Migrace reality (tabulka web_realna_dochazka)
            $this->migrateActualAttendance($oldDb, $usersById, $usersByName, $matches, $trainings, $clubEvents, $platiciToRid);

            $this->command->info('Migrace docházky dokončena.');

        } catch (\Exception $e) {
            $this->command->error('Chyba při migraci docházky: '.$e->getMessage());
            $this->command->error($e->getTraceAsString());
        }
    }

    protected function migrateRsvp($oldDb, $usersById, $matches, $trainings, $clubEvents)
    {
        $this->command->info('Migruji docházku (plánovanou)...');
        $query = \Illuminate\Support\Facades\DB::connection('old_mysql')->table('dochazka');
        $total = $query->count();

        $bar = $this->command->getOutput()->createProgressBar($total);
        $bar->start();

        \Illuminate\Support\Facades\DB::disableQueryLog();

        $query->orderBy('id')->chunk(1000, function ($rsvps) use ($usersById, $matches, $trainings, $clubEvents, $bar) {
            $batch = [];
            $now = now();

            foreach ($rsvps as $rsvp) {
                $user = $usersById->get($rsvp->r_id);
                if (! $user) {
                    $bar->advance();

                    continue;
                }

                $event = $matches->get($rsvp->id_zap) ?: ($trainings->get($rsvp->id_zap) ?: $clubEvents->get($rsvp->id_zap));
                if (! $event) {
                    $bar->advance();

                    continue;
                }

                $plannedStatus = match ($rsvp->dochazka) {
                    '1', 'ucast', 'ano' => 'confirmed',
                    '0', 'omluva', 'ne', 'omluven' => 'declined',
                    default => 'pending',
                };

                $batch[] = [
                    'user_id' => $user->id,
                    'attendable_id' => $event->id,
                    'attendable_type' => get_class($event),
                    'planned_status' => $plannedStatus,
                    'actual_status' => null,
                    'is_mismatch' => false, // Zatím nevíme realitu
                    'note' => $rsvp->dochazka === 'omluven' ? 'Omluven ze starého systému' : null,
                    'internal_note' => null,
                    'responded_at' => \Carbon\Carbon::parse($rsvp->na),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                $bar->advance();
            }

            if (! empty($batch)) {
                try {
                    \Illuminate\Support\Facades\DB::table('attendances')->upsert(
                        $batch,
                        ['user_id', 'attendable_id', 'attendable_type'],
                        ['planned_status', 'note', 'responded_at', 'updated_at']
                    );
                } catch (\Exception $e) {
                    $this->command->error('Chyba při upsertu dávky: '.$e->getMessage());
                }
            }

            unset($batch);
            gc_collect_cycles();
        });

        $bar->finish();
        $this->command->info('');
    }

    protected function migrateActualAttendance($oldDb, $usersById, $usersByName, $matches, $trainings, $clubEvents, $platiciToRid)
    {
        $this->command->info('Migruji reálnou docházku (zápisy trenéra)...');
        $oldActual = \Illuminate\Support\Facades\DB::connection('old_mysql')->table('web_realna_dochazka')->get();

        $bar = $this->command->getOutput()->createProgressBar($oldActual->count());
        $bar->start();

        foreach ($oldActual as $actual) {
            $event = $matches->get($actual->zap_id) ?: ($trainings->get($actual->zap_id) ?: $clubEvents->get($actual->zap_id));
            if (! $event) {
                $bar->advance();

                continue;
            }

            // A. Přítomní (podle ID z web_platici)
            $platiciIds = array_filter(explode('-', $actual->dochazka));
            foreach ($platiciIds as $pId) {
                $rId = $platiciToRid->get($pId);
                if ($rId && ($user = $usersById->get($rId))) {
                    $this->updateActualStatus($user, $event, 'attended', $actual->datum);
                }
            }

            // A2. Přítomní (pokud jsou ve staré DB uloženi jako 1 v poli ucast nebo podobně - ošetření různých formátů)
            // Poznámka: Zadání zmiňuje ucast -> 1. Pokud je to v jiné tabulce, je to už ošetřeno v docházce výše.
            // Zde v realné docházce jsou ID plátců v pomlčkovém seznamu.

            // B. Nepřítomní bez omluvy (podle jména)
            $absentNames = array_filter(explode('-', $actual->nebili));
            foreach ($absentNames as $name) {
                if ($user = $usersByName->get(trim($name))) {
                    $this->updateActualStatus($user, $event, 'absent', $actual->datum);
                }
            }

            // C. Omluvení (podle jména)
            $excusedNames = array_filter(explode('-', ($actual->omluveno ?? '').'-'.($actual->pokuta_zrusena ?? '')));
            foreach ($excusedNames as $name) {
                if ($user = $usersByName->get(trim($name))) {
                    $this->updateActualStatus($user, $event, 'excused', $actual->datum);
                }
            }

            $bar->advance();
        }
        $bar->finish();
        $this->command->info('');
    }

    protected function updateActualStatus($user, $event, $status, $recordedAt = null)
    {
        $attendance = \App\Models\Attendance::where([
            'user_id' => $user->id,
            'attendable_id' => $event->id,
            'attendable_type' => get_class($event),
        ])->first();

        if ($attendance) {
            $attendance->actual_status = $status;
            if ($recordedAt) {
                $metadata = $attendance->metadata ?? [];
                $metadata['legacy_recorded_at'] = \Carbon\Carbon::createFromTimestamp($recordedAt)->toDateTimeString();
                $attendance->metadata = $metadata;
            }
            // is_mismatch se spočítá automaticky v booted() metodě při save()
            $attendance->save();
        } else {
            \App\Models\Attendance::create([
                'user_id' => $user->id,
                'attendable_id' => $event->id,
                'attendable_type' => get_class($event),
                'planned_status' => 'pending',
                'actual_status' => $status,
                'metadata' => $recordedAt ? [
                    'legacy_recorded_at' => \Carbon\Carbon::createFromTimestamp($recordedAt)->toDateTimeString(),
                ] : null,
            ]);
        }
    }
}
