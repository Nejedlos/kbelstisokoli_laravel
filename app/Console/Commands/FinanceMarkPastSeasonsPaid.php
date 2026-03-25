<?php

namespace App\Console\Commands;

use App\Models\FinanceCharge;
use App\Models\Season;
use App\Models\UserSeasonConfig;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FinanceMarkPastSeasonsPaid extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:finance-mark-past-seasons-paid
                            {--dry-run : Neprovádět skutečné změny v databázi}
                            {--force : Potvrdit akci bez ptaní}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Označí všechny neuhrazené předpisy (finance) v minulých sezónách jako zaplacené';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $force = $this->option('force');

        $activeSeason = Season::where('is_active', true)->first();
        if (!$activeSeason) {
            $this->error('Nebyla nalezena žádná aktivní sezóna.');
            return 1;
        }

        $this->info("Aktivní sezóna: {$activeSeason->name}");

        // Minulé sezóny jsou ty, které mají is_active = false A jejich jméno je "menší" než u aktivní sezóny
        // Nebo prostě všechny neaktivní sezóny, pokud se předpokládá, že budoucí neexistují nebo jsou prázdné.
        // Pro jistotu vezmeme ty, co nejsou aktivní a jsou historicky před ní.
        $pastSeasons = Season::where('is_active', false)
            ->where('name', '<', $activeSeason->name)
            ->get();

        if ($pastSeasons->isEmpty()) {
            $this->warn('Nebyly nalezeny žádné minulé sezóny.');
            return 0;
        }

        $this->info("Nalezeno " . $pastSeasons->count() . " minulých sezón: " . $pastSeasons->pluck('name')->implode(', '));

        if (!$force && !$dryRun && !$this->confirm('Opravdu chcete označit všechny předpisy v těchto sezónách jako zaplacené?')) {
            $this->info('Akce zrušena.');
            return 0;
        }

        $pastSeasonIds = $pastSeasons->pluck('id')->toArray();

        // 1. Předpisy typu membership_fee propojené přes user_season_configs
        $membershipChargesQuery = FinanceCharge::where('status', '!=', 'paid')
            ->where('status', '!=', 'cancelled')
            ->where('charge_type', 'membership_fee')
            ->whereIn('user_id', function ($query) use ($pastSeasonIds) {
                $query->select('user_id')
                    ->from('user_season_configs')
                    ->whereIn('season_id', $pastSeasonIds);
            })
            ->whereRaw("json_extract(metadata, '$.season_config_id') IN (SELECT id FROM user_season_configs WHERE season_id IN (" . implode(',', $pastSeasonIds) . "))");

        // Pozor: sqlite syntax pro json_extract se může lišit, v Laravelu je lepší použít ->whereJsonIn
        // Ale season_config_id je v metadata, tak zkusíme standardní Laravel whereJsonIn

        $membershipCharges = FinanceCharge::where('status', '!=', 'paid')
            ->where('status', '!=', 'cancelled')
            ->where('charge_type', 'membership_fee')
            ->where(function ($query) use ($pastSeasonIds) {
                // Najdeme předpisy, které mají v metadata.season_config_id ID konfigurace, která patří do minulé sezóny
                $configIds = UserSeasonConfig::whereIn('season_id', $pastSeasonIds)->pluck('id')->toArray();
                if (empty($configIds)) {
                    $query->whereRaw('1=0');
                } else {
                    $query->whereIn('metadata->season_config_id', $configIds);
                }
            });

        $countMembership = $membershipCharges->count();
        $this->info("Nalezeno {$countMembership} neuhrazených členských příspěvků v minulých sezónách.");

        // 2. Předpisy (včetně pokut), které spadají do minulých sezón podle due_date
        // Pro každou minulou sezónu určíme rozsah dat
        $otherChargesCount = 0;
        $otherChargesToUpdate = collect();

        foreach ($pastSeasons as $season) {
            $normalized = Season::normalizeName($season->name);
            if (!str_contains($normalized, '/')) continue;
            [$startYear, $endYear] = explode('/', $normalized);

            // Sezóna začíná 1.8. a končí 31.7. následujícího roku (dle containsDate v Season.php)
            $start = "{$startYear}-08-01";
            $end = "{$endYear}-07-31";

            $charges = FinanceCharge::where('status', '!=', 'paid')
                ->where('status', '!=', 'cancelled')
                ->whereBetween('due_date', [$start, $end])
                // Abychom nepočítali dvakrát ty membership_fee, které jsme už našli přes config
                ->where(function($q) use ($membershipCharges) {
                     $q->where('charge_type', '!=', 'membership_fee');
                })
                ->get();

            $otherChargesToUpdate = $otherChargesToUpdate->concat($charges);
        }

        $countOther = $otherChargesToUpdate->count();
        $this->info("Nalezeno {$countOther} ostatních neuhrazených předpisů (pokuty atd.) v minulých sezónách.");

        $totalCount = $countMembership + $countOther;

        if ($totalCount === 0) {
            $this->info('Žádné předpisy k aktualizaci.');
            return 0;
        }

        if ($dryRun) {
            $this->info("[DRY-RUN] Bylo by aktualizováno {$totalCount} předpisů.");
        } else {
            $this->info("Aktualizuji {$totalCount} předpisů...");

            DB::transaction(function () use ($membershipCharges, $otherChargesToUpdate) {
                $note = "\n[ARCHIVACE] Automaticky zaplaceno - archivace staré sezóny (Junie)";

                $membershipCharges->update([
                    'status' => 'paid',
                    'notes_internal' => DB::raw("CONCAT(COALESCE(notes_internal, ''), '$note')")
                ]);

                foreach ($otherChargesToUpdate as $charge) {
                    $charge->update([
                        'status' => 'paid',
                        'notes_internal' => ($charge->notes_internal ?? '') . $note
                    ]);
                }
            });

            $this->info("Hotovo. Všechny předpisy v minulých sezónách byly označeny jako zaplacené.");
        }

        return 0;
    }
}
