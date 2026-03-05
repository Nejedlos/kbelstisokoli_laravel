<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SeasonsNormalize extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:seasons-normalize {--dry-run : Neprovádět změny v databázi}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Normalizuje názvy sezón a slučuje duplicity';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        // Musíme brát sezóny jednu po druhé, protože při mazání by se mohl porušit iterátor
        $seasons = \App\Models\Season::orderBy('id')->get();
        $normalizedMap = [];

        $this->info("Kontrola " . $seasons->count() . " sezón...");

        foreach ($seasons as $season) {
            // Kontrola existence, pokud byla smazána v rámci merge
            if (!\App\Models\Season::find($season->id)) {
                continue;
            }

            $oldName = $season->name;
            $newName = \App\Models\Season::normalizeName($oldName);

            if ($oldName !== $newName) {
                // Pokud už existuje sezóna se stejným cílovým jménem, musíme ji sloučit
                $existing = \App\Models\Season::where('name', $newName)->first();
                if ($existing && $existing->id !== $season->id) {
                    $this->error("Přejmenování ID {$season->id} '{$oldName}' -> '{$newName}' narazilo na existující ID {$existing->id}");
                    if (!$dryRun) {
                        $this->mergeSeasons($season->id, $existing->id);
                        continue;
                    }
                } else {
                    $this->warn("Sezóna ID {$season->id}: '{$oldName}' -> '{$newName}'");
                    if (!$dryRun) {
                        $season->update(['name' => $newName]);
                    }
                }
            }

            // Druhá fáze: kontrola duplicit v mapě (pro případ, že už tam jsou dvě sezóny se stejným formátem)
            if (isset($normalizedMap[$newName])) {
                $targetSeasonId = $normalizedMap[$newName];
                $sourceSeasonId = $season->id;

                $this->error("DUPLICITA: Sezóna ID {$sourceSeasonId} ('{$newName}') je duplicitní k ID {$targetSeasonId}");

                if (!$dryRun) {
                    $this->mergeSeasons($sourceSeasonId, $targetSeasonId);
                }
            } else {
                $normalizedMap[$newName] = $season->id;
            }
        }

        $this->info("Hotovo.");
    }

    protected function mergeSeasons(int $sourceId, int $targetId): void
    {
        $this->info("Slučuji data z ID {$sourceId} do ID {$targetId}...");

        // Tabulky, které mají season_id
        $tables = [
            'basketball_matches',
            'external_team_season_configs',
            'statistic_rows',
            'external_entity_mappings',
            'external_import_runs',
            'user_season_configs',
        ];

        foreach ($tables as $table) {
            if (!\Illuminate\Support\Facades\Schema::hasTable($table)) {
                $this->line("- Tabulka {$table} neexistuje, přeskakuji.");
                continue;
            }

            $count = \Illuminate\Support\Facades\DB::table($table)
                ->where('season_id', $sourceId)
                ->update(['season_id' => $targetId]);

            if ($count > 0) {
                $this->line("- Aktualizováno {$count} záznamů v tabulce {$table}");
            }
        }

        // Smazat původní sezónu
        \App\Models\Season::find($sourceId)->delete();
        $this->info("- Původní sezóna ID {$sourceId} smazána.");
    }
}
