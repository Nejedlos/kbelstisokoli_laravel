<?php

namespace App\Console\Commands;

use App\Models\ExternalTeamSeasonConfig;
use App\Models\ExternalImportRun;
use App\Models\Season;
use App\Models\Team;
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
                            {team? : Slug týmu (např. muzi-e) nebo interní ID}
                            {season? : Název sezóny (např. 2025/2026) nebo interní ID}
                            {--team_id= : Synchronizovat pouze konkrétní tým (interní ID) - DEPRECATED: použijte argument}
                            {--season_id= : Synchronizovat pouze konkrétní sezónu (interní ID) - DEPRECATED: použijte argument}
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
        $teamInput = $this->argument('team') ?? $this->option('team_id');
        $seasonInput = $this->argument('season') ?? $this->option('season_id');

        $query = ExternalTeamSeasonConfig::query();

        $team = null;
        $season = null;

        if ($teamInput) {
            $team = Team::where('id', $teamInput)->orWhere('slug', $teamInput)->first();
            if ($team) {
                $query->where('team_id', $team->id);
            } else {
                $this->error("Tým '{$teamInput}' nebyl nalezen.");
                return 0;
            }
        }

        if ($seasonInput) {
            $season = Season::where('id', $seasonInput)->orWhere('name', $seasonInput)->first();
            if ($season) {
                $query->where('season_id', $season->id);
            } else {
                $this->error("Sezóna '{$seasonInput}' nebyla nalezena.");
                return 0;
            }
        }

        if (!$teamInput && !$seasonInput) {
            $query->where('is_enabled', true);
        }

        $configs = $query->with(['team', 'season'])->get();

        if ($configs->isEmpty()) {
            if ($team && $season) {
                // Pokus o automatickou konfiguraci, pokud máme mapování týmu
                $this->info("Hledám mapování pro tým {$team->name} a sezónu {$season->name}...");
                $mapping = $team->externalMappings()->where('source_key', 'czbasketball')->first();

                if ($mapping && $mapping->external_team_id) {
                    $year = (int) substr($season->name, 0, 4);
                    if ($year < 2000) {
                        $year = date('Y');
                    }

                    $url = "https://cz.basketball/team/{$mapping->external_team_id}?sezona={$year}";
                    $matchesUrl = "https://cz.basketball/team/{$mapping->external_team_id}/zapasyliste?sezona={$year}";

                    $this->info("Vytvářím automatickou (dočasnou) konfiguraci s URL: {$url}");

                    $config = ExternalTeamSeasonConfig::create([
                        'source_key' => 'czbasketball',
                        'team_id' => $team->id,
                        'season_id' => $season->id,
                        'external_team_id' => $mapping->external_team_id,
                        'external_season_year' => $year,
                        'team_season_url' => $url,
                        'matches_list_url' => $matchesUrl,
                        'competition_url' => '',
                        'competition_label' => '',
                        'team_name_in_source' => $team->name,
                        'is_enabled' => true,
                        'metadata' => [
                            'auto_created' => true,
                            'created_at_sync' => now()->toDateTimeString(),
                        ],
                    ]);

                    $configs = collect([$config->load(['team', 'season'])]);
                } else {
                    $this->warn('Nenalezeny žádné povolené konfigurace týmů/sezón a ani mapování pro automatické vytvoření.');
                    return 0;
                }
            } else {
                $this->warn('Nenalezeny žádné povolené konfigurace týmů/sezón.');
                return 0;
            }
        }

        if ($limit = $this->option('batch-size')) {
            $configs = $configs->take((int) $limit);
        }

        $this->info("Zahajuji stahování fotek pro {$configs->count()} konfigurací...");

        $mainRun = ExternalImportRun::start(
            'czbasketball',
            $season?->id ?? $this->option('season_id') ?? Season::where('is_active', true)->first()?->id ?? 0,
            $team?->id ?? $this->option('team_id'),
            'photo_sync_command',
            null
        );
        $mainRun->update(['total_count' => $configs->count()]);

        $force = $this->option('force');
        $delay = (int) $this->option('delay');
        $playerDelay = max(0, (int) $this->option('per-player-delay-ms'));
        $syncMatches = $this->option('matches');

        $bar = $this->output->createProgressBar($configs->count());
        $bar->start();

        foreach ($configs as $index => $config) {
            // Kontrola zrušení
            if ($mainRun->refresh()->status === 'cancelled') {
                $this->warn('Synchronizace byla zrušena uživatelem.');
                break;
            }

            $mainRun->updateProgress($index + 1, $configs->count(), "Tým: {$config->team->name}");

            $this->newLine();
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
            $bar->advance();
        }

        $bar->finish();
        $mainRun->finish();
        $this->newLine();
        $this->info("Hotovo. Logy naleznete v storage/logs/laravel.log");
        $this->info("Tip: Pokud na produkci chybí fotky, prověřte cestu: " . public_path('uploads'));
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
                    $playerSyncService->syncPhoto($user, $photoUrl, $force, [
                        'season_id' => $config->season_id,
                        'team_id' => $config->team_id,
                        'added_from' => 'command_roster_sync'
                    ]);
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

            $matchLabel = "Zápas #{$match->id}";
            if (isset($match->metadata['match_number'])) {
                $matchLabel .= " (č. {$match->metadata['match_number']})";
            }
            $this->info("    - {$matchLabel}");

            foreach ($bestPlayers as $category => $data) {
                if (!is_array($data)) continue;

                // Určíme, která strana je naše a která soupeřova
                // $match->is_home: true = home je náš, false = away je náš
                if (isset($data['home']) && is_array($data['home'])) {
                    $playerData = $data['home'];
                    $isOur = $match->is_home;
                    $this->processMatchPlayer($playerData, $isOur, $config, $playerSyncService, $rosterSyncService, $force, $playerDelay, $count);
                }

                if (isset($data['away']) && is_array($data['away'])) {
                    $playerData = $data['away'];
                    $isOur = !$match->is_home;
                    $this->processMatchPlayer($playerData, $isOur, $config, $playerSyncService, $rosterSyncService, $force, $playerDelay, $count);
                }
            }
        }
        $this->info("    - Staženo/ověřeno {$count} fotek z nejlepších hráčů zápasů.");
    }

    protected function processMatchPlayer($playerData, $isOur, $config, $playerSyncService, $rosterSyncService, $force, $playerDelay, &$count)
    {
        $extId = $playerData['external_id'] ?? null;
        $name = $playerData['name'] ?? null;
        $photoUrl = $playerData['photo_url'] ?? null;

        if (!$extId || !$name || !$photoUrl) return;

        if ($isOur) {
            $user = $rosterSyncService->findOrCreateUserForExternalPlayer($extId, $name, $config);
            if ($user) {
                $playerSyncService->syncPhoto($user, $photoUrl, $force, [
                    'season_id' => $config->season_id,
                    'team_id' => $config->team_id,
                    'added_from' => 'command_match_sync'
                ]);
                $count++;
            }
        } else {
            $playerSyncService->syncOpponentPhoto($extId, $photoUrl, $force);
            $count++;
        }

        if ($playerDelay > 0) usleep($playerDelay * 1000);
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
