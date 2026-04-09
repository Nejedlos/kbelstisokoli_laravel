<?php

namespace App\Console\Commands;

use App\Models\ExternalTeamSeasonConfig;
use App\Models\Season;
use App\Services\Stats\Contracts\StatFetcherInterface;
use App\Services\Stats\Extractors\CzBasketball\TeamRosterExtractor;
use App\Services\Stats\Extractors\CzBasketball\PlayerDetailExtractor;
use App\Services\Stats\Sync\PlayerSyncService;
use App\Services\Stats\Sync\RosterSyncService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class AppSyncPlayerPhotosCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-player-photos
                            {--team_id= : Synchronizovat pouze konkrétní tým (interní ID)}
                            {--season_id= : Synchronizovat pouze konkrétní sezónu (interní ID)}
                            {--force : Vynutit stažení i pokud už fotka existuje}
                            {--delay=1 : Pauza mezi týmy v sekundách}
                            {--per-player-delay-ms=200 : Pauza mezi hráči v milisekundách}
                            {--batch-size= : Omezit počet konfigurací (tým+sezóna)}
                            {--matches : Projít i nejlepší hráče v odehraných zápasech}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Hromadně stáhne a uloží fotografie hráčů ze soupisek a nejlepších hráčů ze zápasů.';

    /**
     * Execute the console command.
     */
    public function handle(
        TeamRosterExtractor $extractor,
        PlayerDetailExtractor $playerDetailExtractor,
        PlayerSyncService $playerSyncService,
        RosterSyncService $rosterSyncService,
        StatFetcherInterface $fetcher
    ) {
        $query = ExternalTeamSeasonConfig::query()->where('is_enabled', true);

        if ($teamId = $this->option('team_id')) {
            $query->where('team_id', $teamId);
        }

        if ($seasonId = $this->option('season_id')) {
            $query->where('season_id', $seasonId);
        }

        if ($limit = $this->option('batch-size')) {
            $query->limit((int) $limit);
        }

        $configs = $query->with(['team', 'season'])->get();

        if ($configs->isEmpty()) {
            $this->warn('Nenalezeny žádné povolené konfigurace týmů/sezón.');
            return 0;
        }

        $this->info("Zahajuji stahování fotek pro {$configs->count()} konfigurací...");

        $force = $this->option('force');
        $delay = (int) $this->option('delay');
        $playerDelay = max(0, (int) $this->option('per-player-delay-ms'));
        $syncMatches = $this->option('matches');

        foreach ($configs as $config) {
            $this->info("Zpracovávám: {$config->team->name} ({$config->season->name})");

            // --- 1. Synchronizace ze soupisek ---
            $this->syncFromRoster($config, $extractor, $playerDetailExtractor, $playerSyncService, $rosterSyncService, $fetcher, $force, $playerDelay);

            // --- 2. Synchronizace ze zápasů (nejlepší hráči) ---
            if ($syncMatches) {
                $this->syncFromMatches($config, $playerSyncService, $rosterSyncService, $force, $playerDelay);
            }

            if ($delay > 0) {
                sleep($delay);
            }

            $this->refreshState();
        }

        $this->info("Hotovo.");
        return 0;
    }

    protected function syncFromRoster($config, $extractor, $playerDetailExtractor, $playerSyncService, $rosterSyncService, $fetcher, $force, $playerDelay)
    {
        if (empty($config->team_season_url)) {
            $this->warn("  - Chybí URL soupisky, přeskakuji.");
            return;
        }

        try {
            $this->info("  - Načítám soupisku: {$config->team_season_url}");
            $html = $fetcher->fetch($config->team_season_url);
            $extracted = $extractor->extract($html);
            $tableDto = $extracted['data'] ?? null;

            if (!$tableDto || empty($tableDto->rows)) {
                $this->warn("    - Soupiska je prázdná nebo nebyla nalezena.");
                return;
            }

            $this->info("    - Nalezeno " . count($tableDto->rows) . " záznamů na soupisce.");

            $count = 0;
            foreach ($tableDto->rows as $row) {
                $extId = $row->playerId;
                $name = $row->values['player_name'] ?? $row->rowLabel ?? null;
                $photoUrl = $row->values['photo_url'] ?? null;

                if (!$extId || !$name) continue;

                if (!$photoUrl) {
                    try {
                        $detailHtml = $fetcher->fetch("https://cz.basketball/hrac/{$extId}");
                        $detail = $playerDetailExtractor->extract($detailHtml);
                        $photoUrl = $detail['data']['photo_url'] ?? null;
                    } catch (\Throwable $e) {}
                }

                if (!$photoUrl) continue;

                $user = $rosterSyncService->findOrCreateUserForExternalPlayer($extId, $name, $config);
                if ($user) {
                    $playerSyncService->syncPhoto($user, $photoUrl, $force);
                    $count++;
                    if ($playerDelay > 0) usleep($playerDelay * 1000);
                }
            }
            $this->info("    - Staženo/ověřeno {$count} fotek ze soupisky.");
        } catch (\Exception $e) {
            $this->error("    - Chyba při synchronizaci soupisky: " . $e->getMessage());
        }
    }

    protected function syncFromMatches($config, $playerSyncService, $rosterSyncService, $force, $playerDelay)
    {
        $this->info("  - Procházím nejlepší hráče v zápasech týmu {$config->team->name}...");

        $matches = \App\Models\BasketballMatch::where('team_id', $config->team_id)
            ->where('season_id', $config->season_id)
            ->whereNotNull('metadata')
            ->get();

        $count = 0;
        foreach ($matches as $match) {
            $bestPlayers = $match->metadata['best_players_external'] ?? $match->metadata['best_players'] ?? [];
            if (empty($bestPlayers)) continue;

            foreach ($bestPlayers as $category => $data) {
                if (!is_array($data)) continue;

                $toProcess = [];
                if (isset($data['home']) && is_array($data['home'])) $toProcess[] = $data['home'];
                if (isset($data['away']) && is_array($data['away'])) $toProcess[] = $data['away'];

                foreach ($toProcess as $playerData) {
                    $extId = $playerData['external_id'] ?? null;
                    $name = $playerData['name'] ?? null;
                    $photoUrl = $playerData['photo_url'] ?? null;

                    if (!$extId || !$name || !$photoUrl) continue;

                    $user = $rosterSyncService->findOrCreateUserForExternalPlayer($extId, $name, $config);
                    if ($user) {
                        $playerSyncService->syncPhoto($user, $photoUrl, $force);
                        $count++;
                        if ($playerDelay > 0) usleep($playerDelay * 1000);
                    }
                }
            }
        }
        $this->info("    - Staženo/ověřeno {$count} fotek z nejlepších hráčů zápasů.");
    }

    /**
     * Vyčistí stav aplikace pro úsporu paměti.
     */
    protected function refreshState(): void
    {
        gc_collect_cycles();
        \DB::connection()->flushQueryLog();
        \DB::connection()->disableQueryLog();
    }
}
