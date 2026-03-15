<?php

namespace App\Console\Commands;

use App\Models\FinanceCharge;
use App\Models\Season;
use App\Models\UserSeasonConfig;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FinanceArchiveOldCharges extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'finance:archive-old-charges {--dry-run : Pouze vypíše počet změn, ale nic neuloží}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Označí všechny neuzavřené předpisy z předchozích sezón jako zaplacené.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $activeSeason = Season::where('is_active', true)->first();

        if (! $activeSeason) {
            $this->error('Nenalezena žádná aktivní sezóna! Nastavte prosím aktivní sezónu v tabulce seasons.');

            return self::FAILURE;
        }

        $this->info("Aktuální aktivní sezóna: {$activeSeason->name}");

        // Rozhodné datum: 1. září roku začátku aktivní sezóny
        // Formát sezóny je obvykle YYYY/YYYY
        $parts = explode('/', str_replace('-', '/', $activeSeason->name));
        $activeSeasonStartYear = (int) $parts[0];
        $thresholdDate = Carbon::create($activeSeasonStartYear, 9, 1, 0, 0, 0);

        $this->info("Rozhodné datum pro archivaci starých sezón: {$thresholdDate->toDateString()}");

        $inactiveSeasonIds = Season::where('is_active', false)
            ->where('name', '<', $activeSeason->name) // Jen ty starší než aktuální
            ->pluck('id');

        $this->info('ID neaktivních (starších) sezón k archivaci: '.$inactiveSeasonIds->implode(', '));

        // 1. Předpisy navázané na staré sezónní konfigurace
        $configIds = UserSeasonConfig::whereIn('season_id', $inactiveSeasonIds)->pluck('id');

        $query = FinanceCharge::whereIn('status', ['open', 'partially_paid', 'overdue'])
            ->where(function ($q) use ($configIds) {
                // Hledáme v JSON metadatech (season_config_id)
                foreach ($configIds as $id) {
                    $q->orWhereJsonContains('metadata->season_config_id', $id);
                }
            });

        $countConfigs = $query->count();
        $this->info("Nalezeno {$countConfigs} neuzavřených členských předpisů ze starých sezón.");

        if (! $this->option('dry-run') && $countConfigs > 0) {
            $query->update([
                'status' => 'paid',
                'notes_internal' => DB::raw("CONCAT(COALESCE(notes_internal, ''), '\n[ARCHIVACE] Automaticky zaplaceno - archivace staré sezóny (Junie)')"),
            ]);
            $this->info('Členské předpisy byly aktualizovány.');
        }

        // 2. Ostatní předpisy (např. pokuty) starší než rozhodné datum
        $queryOthers = FinanceCharge::whereIn('status', ['open', 'partially_paid', 'overdue'])
            ->where('due_date', '<', $thresholdDate)
            ->where(function($q) use ($configIds) {
                // Nechceme ty, které jsme už zpracovali výše (pokud by tam byla shoda)
                foreach ($configIds as $id) {
                    $q->whereJsonDoesntContain('metadata->season_config_id', $id);
                }
            });

        $countOthers = $queryOthers->count();
        $this->info("Nalezeno {$countOthers} ostatních neuzavřených předpisů (např. pokuty) starších než rozhodné datum.");

        if (! $this->option('dry-run') && $countOthers > 0) {
            $queryOthers->update([
                'status' => 'paid',
                'notes_internal' => DB::raw("CONCAT(COALESCE(notes_internal, ''), '\n[ARCHIVACE] Automaticky zaplaceno - staré datum splatnosti (Junie)')"),
            ]);
            $this->info('Ostatní staré předpisy byly aktualizovány.');
        }

        if ($this->option('dry-run')) {
            $this->warn('Byl použit --dry-run, v databázi nebyly provedeny žádné změny.');
        }

        return self::SUCCESS;
    }
}
