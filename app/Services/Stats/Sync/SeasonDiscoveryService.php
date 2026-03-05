<?php

namespace App\Services\Stats\Sync;

use App\Jobs\Stats\SyncTeamSeasonJob;
use App\Models\ExternalTeamMapping;
use App\Models\ExternalTeamSeasonConfig;
use App\Models\Season;
use App\Models\Team;

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
                // Kontrola, zda je sezóna "prázdná" nebo zda vynucujeme re-discovery
                if (! $force && ! $this->statusService->isEmpty($team->id, $season->id)) {
                    continue;
                }

                $candidates = $this->suggestCandidates($season, $options);
                $winner = null;

                foreach ($candidates as $y) {
                    $verifyResult = $this->verifier->verify($mapping->external_team_id, $y);

                    if ($verifyResult['isValid']) {
                        $winner = $verifyResult;
                        break;
                    }

                    // Malý delay mezi pokusy, abychom nebyli zablokováni
                    usleep(500000); // 0.5s
                }

                if ($winner) {
                    if (! $dryRun) {
                        $this->saveConfig($team, $season, $winner);
                        if ($syncAfter) {
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
                    $results[] = [
                        'team' => $team->slug,
                        'season' => $season->name,
                        'status' => 'not found',
                        'tried' => $candidates,
                    ];
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
                'matches_list_url' => $matchesUrl,
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
