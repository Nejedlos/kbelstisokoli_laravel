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
        $configIds = UserSeasonConfig::whereIn('season_id', $pastSeasonIds)->pluck('id')->toArray();

        $allUnpaidMembershipCharges = FinanceCharge::where('status', '!=', 'paid')
            ->where('status', '!=', 'cancelled')
            ->where('charge_type', 'membership_fee')
            ->get();

        $membershipChargeIdsToUpdate = $allUnpaidMembershipCharges->filter(function ($charge) use ($configIds) {
            $chargeConfigId = $charge->metadata['season_config_id'] ?? null;
            return $chargeConfigId && in_array($chargeConfigId, $configIds);
        })->pluck('id')->toArray();

        $countMembership = count($membershipChargeIdsToUpdate);
        $this->info("Nalezeno {$countMembership} neuhrazených členských příspěvků v minulých sezónách.");

        // ... (zbytek logiky zůstává podobný, ale použijeme IDs)

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
                ->where('charge_type', '!=', 'membership_fee')
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

            DB::transaction(function () use ($membershipChargeIdsToUpdate, $otherChargesToUpdate) {
                $note = "\n[ARCHIVACE] Automaticky zaplaceno - archivace staré sezóny (Junie)";

                if (!empty($membershipChargeIdsToUpdate)) {
                    FinanceCharge::whereIn('id', $membershipChargeIdsToUpdate)->update([
                        'status' => 'paid',
                        'notes_internal' => DB::raw("CONCAT(COALESCE(notes_internal, ''), '$note')")
                    ]);
                }

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
