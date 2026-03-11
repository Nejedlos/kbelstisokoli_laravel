<?php

namespace App\Actions\Season;

use App\Models\Season;
use App\Models\UserSeasonConfig;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RenewSeasonAction
{
    /**
     * Provede hromadnou obnovu sezóny.
     *
     * @param int|null $targetSeasonId Cílová sezóna (pokud není uvedena, použije se očekávaná aktuální)
     * @param int|null $sourceSeasonId Zdrojová sezóna (pokud není uvedena, použije se předchozí k cílové)
     * @return array Výsledek operace [created => int, updated => int]
     */
    public function execute(?int $targetSeasonId = null, ?int $sourceSeasonId = null): array
    {
        if (!$targetSeasonId) {
            $expectedName = Season::getExpectedCurrentSeasonName();
            $targetSeason = Season::firstOrCreate(['name' => $expectedName]);
            $targetSeasonId = $targetSeason->id;
        } else {
            $targetSeason = Season::findOrFail($targetSeasonId);
        }

        if (!$sourceSeasonId) {
            $prevName = Season::getPreviousSeasonNameFrom($targetSeason->name);
            $sourceSeason = Season::where('name', $prevName)->first();

            if (!$sourceSeason) {
                // Zkusíme najít jakoukoli předchozí sezónu, pokud tato neexistuje
                $sourceSeason = Season::where('id', '!=', $targetSeasonId)
                    ->orderBy('name', 'desc')
                    ->first();
            }

            $sourceSeasonId = $sourceSeason?->id;
        }

        if (!$sourceSeasonId) {
            Log::warning("RenewSeasonAction: Žádná zdrojová sezóna nenalezena pro cílovou sezónu {$targetSeason->name}");
            return ['created' => 0, 'updated' => 0];
        }

        $sourceConfigs = UserSeasonConfig::where('season_id', $sourceSeasonId)->get();

        $created = 0;
        $updated = 0;

        DB::transaction(function () use ($sourceConfigs, $targetSeasonId, &$created, &$updated) {
            foreach ($sourceConfigs as $config) {
                $record = UserSeasonConfig::updateOrCreate(
                    [
                        'user_id' => $config->user_id,
                        'season_id' => $targetSeasonId,
                    ],
                    [
                        'financial_tariff_id' => $config->financial_tariff_id,
                        'opening_balance' => 0, // Vždy začínáme s nulou, pokud to není ručně upraveno
                        'track_attendance' => $config->track_attendance,
                    ]
                );

                if ($record->wasRecentlyCreated) {
                    $created++;
                } else {
                    $updated++;
                }
            }
        });

        Log::info("RenewSeasonAction: Obnova sezóny {$targetSeason->name} dokončena. Vytvořeno: {$created}, Aktualizováno: {$updated}.");

        return [
            'created' => $created,
            'updated' => $updated,
        ];
    }
}
