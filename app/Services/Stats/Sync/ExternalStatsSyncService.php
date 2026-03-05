<?php

namespace App\Services\Stats\Sync;

use App\Jobs\Stats\SyncMatchDetailJob;
use App\Models\BasketballMatch;
use App\Models\ExternalImportRun;
use App\Models\ExternalTeamSeasonConfig;
use App\Models\Season;
use App\Models\Team;
use App\Services\Stats\Contracts\StatFetcherInterface;
use App\Services\Stats\Contracts\StatNormalizerInterface;
use App\Services\Stats\Extractors\CzBasketball\MatchDetailBoxscoreExtractor;
use App\Services\Stats\Extractors\CzBasketball\MatchesListExtractor;
use App\Services\Stats\Extractors\CzBasketball\TeamRosterExtractor;
use App\Services\Support\ConsoleService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ExternalStatsSyncService
{
    public function __construct(
        protected StatFetcherInterface $fetcher,
        protected RosterSyncService $rosterSyncService,
        protected MatchSyncService $matchSyncService,
        protected StatisticSyncService $statisticSyncService,
        protected StatNormalizerInterface $normalizer
    ) {}

    /**
     * Synchronizuje celou sezónu pro daný tým.
     *
     * @param  array  $options  [limit, force, fresh]
     */
    public function syncTeamSeason(int $teamId, int $seasonId, array $options = []): void
    {
        $config = ExternalTeamSeasonConfig::where('team_id', $teamId)
            ->where('season_id', $seasonId)
            ->where('is_enabled', true)
            ->first();

        if (! $config) {
            ConsoleService::log("ExternalTeamSeasonConfig nebyl nalezen nebo je deaktivován pro Tým: $teamId, Sezóna: $seasonId", 'warning');
            Log::warning("ExternalTeamSeasonConfig nebyl nalezen nebo je deaktivován pro Tým: $teamId, Sezóna: $seasonId");

            return;
        }

        $team = Team::findOrFail($teamId);
        $season = Season::findOrFail($seasonId);

        ConsoleService::log("Zahajuji synchronizaci týmu {$team->slug} pro sezónu {$season->name}".(($options['force'] ?? false) ? ' (FORCE mode)' : '').(($options['fresh'] ?? false) ? ' (FRESH mode)' : '').(($options['ai'] ?? false) ? ' (AI mode)' : ''), 'info');
        Log::info("Zahajuji synchronizaci týmu {$team->slug} pro sezónu {$season->name}");

        $errors = [];

        // 1. Synchronizace soupisky
        if ($options['sync_roster'] ?? true) {
            try {
                ConsoleService::log('- Synchronizace soupisky...');
                $this->syncRoster($team, $season, $config, $options);
                ConsoleService::log('  Soupiska OK.', 'success');
            } catch (\Exception $e) {
                $errors[] = 'Soupiska: '.$e->getMessage();
                ConsoleService::log('  Chyba při synchronizaci soupisky: '.$e->getMessage(), 'error');
                Log::error('Chyba při synchronizaci soupisky: '.$e->getMessage());
            }
        }

        // 2. Synchronizace seznamu zápasů
        if ($options['sync_matches'] ?? true) {
            try {
                ConsoleService::log('- Synchronizace seznamu zápasů...');
                $this->syncMatchesList($team, $season, $config, $options);
                ConsoleService::log('  Seznam zápasů OK.', 'success');
            } catch (\Exception $e) {
                $errors[] = 'Zápasy: '.$e->getMessage();
                ConsoleService::log('  Chyba při synchronizaci seznamu zápasů: '.$e->getMessage(), 'error');
                Log::error('Chyba při synchronizaci seznamu zápasů: '.$e->getMessage());
            }
        }

        $config->update(['last_synced_at' => now()]);

        if (! empty($errors)) {
            throw new \Exception('Synchronizace dokončena s chybami: '.implode('; ', $errors));
        }
    }

    /**
     * Synchronizuje soupisku týmu.
     */
    protected function syncRoster(Team $team, Season $season, ExternalTeamSeasonConfig $config, array $options = []): void
    {
        $run = ExternalImportRun::start('czbasketball', $season->id, $team->id, 'team_page', $config->external_team_id);
        if ($options['force'] ?? false) {
            $run->updateMetadata(['force' => true]);
        }
        if ($options['fresh'] ?? false) {
            $run->updateMetadata(['fresh' => true]);
        }

        try {
            $html = $this->fetcher->fetch($config->team_season_url, $run);
            $extractor = app(TeamRosterExtractor::class);

            $usedAiFallback = false;
            if ($options['ai'] ?? false) {
                $data = $this->normalizer->normalize($html, ['type' => 'roster']);
                $fragmentHtml = $html;
                $usedAiFallback = true;
                $result = ['data' => $data, 'fragment_html' => $fragmentHtml];
            } else {
                try {
                    $result = $extractor->extract($html);
                    $data = $result['data'];
                    $fragmentHtml = $result['fragment_html'];
                } catch (\Exception $e) {
                    Log::warning("DOM extractor selhal pro soupisku týmu {$team->slug}, zkouším AI fallback. Chyba: ".$e->getMessage());
                    $data = $this->normalizer->normalize($html, ['type' => 'roster']);
                    $fragmentHtml = $html;
                    $usedAiFallback = true;
                    $result = ['data' => $data, 'fragment_html' => $fragmentHtml];
                }
            }

            $hash = hash('sha256', $fragmentHtml);

            if ($run->isIdenticalToLast($hash) && ! ($options['force'] ?? false) && ! ($options['fresh'] ?? false)) {
                $run->skip();

                return;
            }

            $run->update([
                'content_hash' => $hash,
                'metadata' => array_merge($run->metadata ?? [], [
                    'used_dom_extractor' => ! $usedAiFallback,
                    'used_ai_fallback' => $usedAiFallback,
                ]),
            ]);

            if ($usedAiFallback) {
                $run->update(['status' => 'partial_failed', 'error_summary' => 'DOM extractor failed, used AI fallback.']);
            }

            $this->rosterSyncService->syncWithData($config, $data);

            $run->finish([
                'extracted_count' => count($data->rows),
                'imported_count' => count($data->rows),
            ]);
        } catch (\Exception $e) {
            $run->fail($e);
            throw $e;
        }
    }

    /**
     * Synchronizuje seznam zápasů a naplánuje detailní synchronizaci.
     */
    protected function syncMatchesList(Team $team, Season $season, ExternalTeamSeasonConfig $config, array $options): void
    {
        \Log::info("START: syncMatchesList for team {$team->slug}");
        $run = ExternalImportRun::start('czbasketball', $season->id, $team->id, 'matches_list', $config->external_team_id);

        try {
            $html = $this->fetcher->fetch($config->matches_list_url, $run);
            $extractor = app(MatchesListExtractor::class);

            $usedAiFallback = false;
            if ($options['ai'] ?? false) {
                $data = $this->normalizer->normalize($html, ['type' => 'matches_list']);
                $fragmentHtml = $html;
                $usedAiFallback = true;
                $result = ['data' => $data, 'fragment_html' => $fragmentHtml];
            } else {
                try {
                    \Log::info("Extracting matches for {$team->slug}");
                    $result = $extractor->extract($html);
                    $data = $result['data'];
                    $fragmentHtml = $result['fragment_html'];
                    \Log::info('Extracted '.count($data->rows)." matches for {$team->slug}");
                    ConsoleService::log('  Extrahováno '.count($data->rows).' zápasů ze seznamu.', 'info');
                } catch (\Exception $e) {
                    $data = $this->normalizer->normalize($html, ['type' => 'matches_list']);
                    $fragmentHtml = $html;
                    $usedAiFallback = true;
                    $result = ['data' => $data, 'fragment_html' => $fragmentHtml];
                }
            }

            $hash = hash('sha256', $fragmentHtml);

            if ($run->isIdenticalToLast($hash) && ! ($options['force'] ?? false) && ! ($options['fresh'] ?? false)) {
                $run->skip();
            } else {
                $run->update([
                    'content_hash' => $hash,
                    'metadata' => array_merge($run->metadata ?? [], [
                        'used_dom_extractor' => ! $usedAiFallback,
                        'used_ai_fallback' => $usedAiFallback,
                        'force' => $options['force'] ?? false,
                        'fresh' => $options['fresh'] ?? false,
                    ]),
                ]);

                if ($usedAiFallback) {
                    $run->update(['status' => 'partial_failed', 'error_summary' => 'DOM extractor failed, used AI fallback.']);
                }

                \Log::info("Syncing matches for {$team->slug}, count: ".count($data->rows));
                foreach ($data->rows as $row) {
                    $this->matchSyncService->sync($team, $season, $row->values, $run);
                }
                $run->finish([
                    'extracted_count' => count($data->rows),
                    'imported_count' => count($data->rows),
                ]);
            }

            // Naplánování detailů zápasů
            if ($options['sync_details'] ?? true) {
                $this->dispatchMatchDetailJobs($team, $season, $options);
            }

        } catch (\Exception $e) {
            $run->fail($e);
            throw $e;
        }
    }

    /**
     * Rozdělí detailní synchronizaci zápasů do fronty.
     */
    protected function dispatchMatchDetailJobs(Team $team, Season $season, array $options): void
    {
        $limit = $options['maxMatchDetails'] ?? $options['limit'] ?? 15;
        $force = $options['force'] ?? false;
        $recentOnly = $options['recentOnly'] ?? false;

        $query = BasketballMatch::where('team_id', $team->id)
            ->where('season_id', $season->id)
            ->where('status', 'completed')
            ->where('metadata', 'LIKE', '%"external_id":%');

        if ($recentOnly) {
            $days = config('external_sources.czbasketball.limits.recent_match_days', 3);
            $query->where('scheduled_at', '>=', now()->subDays($days)->toDateTimeString());
        }

        if (! $force) {
            $boxscoreSetId = DB::table('statistic_sets')->where('slug', 'match-boxscore')->value('id');

            // Zápasy bez statistik nebo ty, které byly synchronizovány před více než 24 hodinami
            $query->where(function ($q) use ($boxscoreSetId) {
                $q->whereNotExists(function ($sub) use ($boxscoreSetId) {
                    $sub->select(DB::raw(1))
                        ->from('statistic_rows')
                        ->whereColumn('statistic_rows.basketball_match_id', 'matches.id')
                        ->where('statistic_rows.statistic_set_id', $boxscoreSetId);
                })->orWhereNotNull('metadata'); // Dočasně vybereme s metadaty, profiltrujeme v PHP
            });
        }

        // Načteme dostatečný počet zápasů pro filtraci v PHP (abychom se vyhnuli JSON query v SQL)
        $matches = $query->limit(100)->get();

        $dispatchedCount = 0;
        foreach ($matches as $match) {
            if ($dispatchedCount >= $limit) {
                break;
            }

            if (! $force) {
                $lastSynced = $match->metadata['last_synced_at'] ?? null;
                if ($lastSynced && \Illuminate\Support\Carbon::parse($lastSynced)->gt(now()->subDay())) {
                    continue;
                }
            }

            SyncMatchDetailJob::dispatch($match->id, $options);
            ConsoleService::log("    -> Naplánován detail zápasu: ID {$match->id} ({$match->metadata['external_id']})", 'debug');
            $dispatchedCount++;
        }
    }

    /**
     * Synchronizuje detaily konkrétního zápasu (boxscore).
     */
    public function syncMatchDetail(int $matchId, array $options = []): void
    {
        $match = BasketballMatch::with(['team', 'season'])->findOrFail($matchId);
        $externalMatchId = $match->metadata['external_id'] ?? null;

        if (! $externalMatchId) {
            return;
        }

        $url = 'https://cz.basketball/zapas/'.$externalMatchId;
        $run = ExternalImportRun::start('czbasketball', $match->season_id, $match->team_id, 'match_detail', $externalMatchId);
        if ($options['force'] ?? false) {
            $run->updateMetadata(['force' => true]);
        }
        if ($options['fresh'] ?? false) {
            $run->updateMetadata(['fresh' => true]);
        }

        try {
            $html = $this->fetcher->fetch($url, $run);
            $extractor = app(MatchDetailBoxscoreExtractor::class);

            $usedAiFallback = false;
            if ($options['ai'] ?? false) {
                $data = $this->normalizer->normalize($html, ['type' => 'match_boxscore']);
                $fragmentHtml = $html;
                $usedAiFallback = true;
                $result = [
                    'data' => $data,
                    'fragment_html' => $fragmentHtml,
                    'tables' => $data->metadata['all_tables_dto'] ?? [$data],
                ];

                // Pokud máme v metadatech all_tables (v polích), musíme je převést zpět na DTO pro cyklus níže
                if (isset($data->metadata['all_tables'])) {
                    $result['tables'] = [];
                    foreach ($data->metadata['all_tables'] as $tableData) {
                        $rows = [];
                        foreach ($tableData['rows'] ?? [] as $row) {
                            $rows[] = new \App\Services\Stats\DTO\NormalizedRowDTO(
                                values: $row['values'] ?? [],
                                playerId: null,
                                rowLabel: $row['rowLabel'] ?? null,
                                metadata: $row['metadata'] ?? []
                            );
                        }
                        $result['tables'][] = new \App\Services\Stats\DTO\NormalizedTableDTO(
                            name: $tableData['name'] ?? 'Boxscore',
                            columns: $tableData['columns'] ?? [],
                            rows: $rows,
                            metadata: $tableData['metadata'] ?? []
                        );
                    }
                }
            } else {
                try {
                    $result = $extractor->extract($html);
                    $data = $result['data'];
                    $fragmentHtml = $result['fragment_html'];
                } catch (\Exception $e) {
                    Log::warning("DOM extractor selhal pro zápas $externalMatchId, zkouším AI fallback. Chyba: ".$e->getMessage());
                    $data = $this->normalizer->normalize($html, ['type' => 'match_boxscore']);
                    $fragmentHtml = $html;
                    $usedAiFallback = true;
                    $result = ['data' => $data, 'fragment_html' => $fragmentHtml, 'tables' => [$data]];
                }
            }

            $hash = hash('sha256', $fragmentHtml);
            if ($run->isIdenticalToLast($hash) && ! ($options['force'] ?? false) && ! ($options['fresh'] ?? false)) {
                $run->skip();

                return;
            }

            $run->update([
                'content_hash' => $hash,
                'metadata' => array_merge($run->metadata ?? [], [
                    'used_dom_extractor' => ! $usedAiFallback,
                    'used_ai_fallback' => $usedAiFallback,
                ]),
            ]);

            if ($usedAiFallback) {
                $run->update(['status' => 'partial_failed', 'error_summary' => 'DOM extractor failed, used AI fallback.']);
            }

            // Aktualizace skóre a stavu z detailu zápasu (pokud tam jsou)
            $header = $data->metadata['header'] ?? [];
            if (isset($header['score']) && preg_match('/(\d+)\s*:\s*(\d+)/', $header['score'], $m)) {
                $scoreHome = (int) $m[1];
                $scoreAway = (int) $m[2];

                if ($match->score_home !== $scoreHome || $match->score_away !== $scoreAway || $match->status !== 'completed') {
                    $oldValues = $match->only(['score_home', 'score_away', 'status']);
                    $match->update([
                        'score_home' => $scoreHome,
                        'score_away' => $scoreAway,
                        'status' => 'completed',
                    ]);
                    if ($run) {
                        $run->addLog('match_updated_from_detail', $match, $oldValues, $match->only(['score_home', 'score_away', 'status']));
                    }
                }
            }

            // Fresh mode: smazání starých statistik zápasu před importem nových
            if ($options['fresh'] ?? false) {
                $this->statisticSyncService->clearMatchBoxscore($match, $run);
            }

            $tables = $result['tables'] ?? [$data];
            foreach ($tables as $tableData) {
                $this->statisticSyncService->syncMatchBoxscore($match, $tableData, $run);
            }

            $run->finish([
                'extracted_count' => count($data->rows),
                'imported_count' => count($data->rows),
            ]);

        } catch (\Exception $e) {
            $run->fail($e);
            throw $e;
        }
    }

    /**
     * Provede náhled synchronizace bez uložení do DB.
     *
     * @return array [roster => NormalizedTableDTO, matches => NormalizedTableDTO]
     */
    public function previewSync(int $teamId, int $seasonId): array
    {
        $config = ExternalTeamSeasonConfig::where('team_id', $teamId)
            ->where('season_id', $seasonId)
            ->firstOrFail();

        $run = ExternalImportRun::start('czbasketball', $seasonId, $teamId, 'preview', $config->external_team_id);
        $run->updateMetadata(['dry_run' => true]);

        try {
            // Roster preview
            $rosterHtml = $this->fetcher->fetch($config->team_season_url, $run);
            $rosterResult = app(TeamRosterExtractor::class)->extract($rosterHtml);
            $rosterData = $rosterResult['data'];

            // Matches preview
            $matchesHtml = $this->fetcher->fetch($config->matches_list_url, $run);
            $matchesResult = app(MatchesListExtractor::class)->extract($matchesHtml);
            $matchesData = $matchesResult['data'];

            $run->finish([
                'extracted_count' => count($rosterData->rows) + count($matchesData->rows),
                'status' => 'success',
            ]);

            return [
                'roster' => $rosterData,
                'matches' => $matchesData,
            ];
        } catch (\Exception $e) {
            $run->fail($e);
            throw $e;
        }
    }
}
