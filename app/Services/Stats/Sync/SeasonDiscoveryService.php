<?php

namespace App\Services\Stats\Sync;

use App\Jobs\Stats\SyncTeamSeasonJob;
use App\Models\ExternalTeamMapping;
use App\Models\ExternalTeamSeasonConfig;
use App\Models\Season;
use App\Models\Team;
use App\Services\Support\ConsoleService;

class SeasonDiscoveryService
{
    public function __construct(
        protected SeasonDataStatusService $statusService,
        protected SeasonYearCandidateVerifier $verifier
    ) {}

    /**
     * Spustí proces vyhledávání chybějících konfigurací sezón.
     */
    public function discover(?string $teamSlug = null, ?string $seasonName = null, array $options = []): array
    {
        $results = [];
        $teams = $teamSlug
            ? Team::where('slug', $teamSlug)->get()
            : Team::whereHas('externalMappings')->get();

        $seasons = $seasonName
            ? Season::where('name', $seasonName)->get()
            : Season::orderBy('name', 'desc')->get();

        $dryRun = $options['dry_run'] ?? false;
        $syncAfter = $options['sync'] ?? false;
        $force = $options['force'] ?? false;

        foreach ($teams as $team) {
            $mapping = ExternalTeamMapping::where('team_id', $team->id)
                ->where('source_key', 'czbasketball')
                ->first();

            if (! $mapping) {
                continue;
            }

            foreach ($seasons as $season) {
                $config = ExternalTeamSeasonConfig::where('team_id', $team->id)
                    ->where('season_id', $season->id)
                    ->first();

                // Pokud už konfiguraci máme a nemáme vynuceno force, přeskočíme discovery
                if (! $force && $config) {
                    // Pokud je sezóna "plná", rozhodně ji neřešíme
                    if (! $this->statusService->isEmpty($team->id, $season->id)) {
                        continue;
                    }

                    // Pokud je sezóna "prázdná", ale konfiguraci máme, taky ji standardně neřešíme (není "missing")
                    // Ale pokud uživatel chce zkusit, jestli neexistuje lepší 'y', tak by musel dát force.
                    ConsoleService::log("Sezóna {$season->name} pro tým {$team->slug} již má konfiguraci (y={$config->external_season_year}), přeskakuji discovery.", 'debug');
                    continue;
                }

                // Pokud je sezóna "plná" (má data), tak ji rozhodně neřešíme bez force
                if (! $force && ! $this->statusService->isEmpty($team->id, $season->id)) {
                    continue;
                }

                ConsoleService::log("Zkouším discovery pro tým {$team->slug} a sezónu {$season->name}...", 'info');

                $candidates = $this->suggestCandidates($season, $options);
                $winner = null;

                foreach ($candidates as $y) {
                    ConsoleService::log("  - Prověřuji parametr y={$y}...", 'debug');
                    $verifyResult = $this->verifier->verify($mapping->external_team_id, $y);

                    if ($verifyResult['isValid']) {
                        $winner = $verifyResult;
                        $evidenceStr = implode(', ', $winner['evidence']);
                        ConsoleService::log("  Found! y={$y} (confidence: {$winner['confidence']}, evidence: {$evidenceStr})", 'success');
                        break;
                    }

                    // Malý delay mezi pokusy, abychom nebyli zablokováni
                    usleep(500000); // 0.5s
                }

                if ($winner) {
                    if (! $dryRun) {
                        $isNew = ! $config;
                        $this->saveConfig($team, $season, $winner);

                        if ($isNew) {
                            ConsoleService::log("  Vytvořena nová konfigurace pro sezónu {$season->name} (y={$winner['y']}).", 'success');
                        } else {
                            ConsoleService::log("  Aktualizována konfigurace pro sezónu {$season->name} (y={$winner['y']}).", 'success');
                        }

                        if ($syncAfter) {
                            ConsoleService::log("  Spouštím synchronizaci dat...", 'info');
                            SyncTeamSeasonJob::dispatch($team->id, $season->id);
                        }
                    }
                    $results[] = [
                        'team' => $team->slug,
                        'season' => $season->name,
                        'y' => $winner['y'],
                        'status' => $dryRun ? 'found (dry-run)' : 'created/updated',
                        'confidence' => $winner['confidence'],
                    ];
                } else {
                    ConsoleService::log("  Pro sezónu {$season->name} nebyl nalezen žádný validní rok (zkoušeno: ".implode(', ', $candidates).").", 'warning');
                    $results[] = [
                        'team' => $team->slug,
                        'season' => $season->name,
                        'status' => 'not found',
                        'tried' => $candidates,
                    ];

                    ConsoleService::log("  Zastavuji vyhledávání starších sezón pro tým {$team->slug}.", 'info');
                    break;
                }

                // Delay mezi sezónami
                sleep(1);
            }
        }

        return $results;
    }

    /**
     * Navrhne kandidáty pro parametr y na základě názvu sezóny.
     */
    protected function suggestCandidates(Season $season, array $options = []): array
    {
        if (isset($options['years'])) {
            // range formát "2010..2025"
            if (str_contains($options['years'], '..')) {
                [$start, $end] = explode('..', $options['years']);

                return range((int) $start, (int) $end);
            }

            return array_map('intval', explode(',', $options['years']));
        }

        $candidates = [];

        // 1. Normalizujeme název sezóny pro spolehlivější parsování
        $normalized = Season::normalizeName($season->name);

        // 2. Hledání roku v názvu (např. 2024/2025 -> 2024)
        if (preg_match('/(\d{4})/', $normalized, $matches)) {
            $year = (int) $matches[1];
            $candidates[] = $year;

            // Přidáme okolní roky jako fallback
            $candidates[] = $year - 1;
            $candidates[] = $year + 1;
        }

        return array_unique($candidates);
    }

    /**
     * Uloží nalezenou konfiguraci.
     */
    protected function saveConfig(Team $team, Season $season, array $data): void
    {
        $externalTeamId = ExternalTeamMapping::where('team_id', $team->id)->value('external_team_id');
        $y = $data['y'];
        $matchesUrl = $data['matched_url'];

        ExternalTeamSeasonConfig::updateOrCreate(
            [
                'source_key' => 'czbasketball',
                'team_id' => $team->id,
                'season_id' => $season->id,
            ],
            [
                'external_team_id' => $externalTeamId,
                'external_season_year' => $y,
                'team_season_url' => "https://cz.basketball/tym/{$externalTeamId}?y={$y}",
                'matches_list_url' => "https://cz.basketball/tym/{$externalTeamId}?y={$y}",
                'is_enabled' => true,
                'metadata' => [
                    'discovered_by' => 'season_discover',
                    'discovered_at' => now()->toIso8601String(),
                    'confidence' => $data['confidence'],
                    'evidence' => $data['evidence'],
                ],
            ]
        );
    }
}
