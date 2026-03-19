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
use App\Services\Stats\Clippers\CzBasketball\CzBasketballMatchDetailClipper;
use App\Services\Stats\Clippers\CzBasketball\CzBasketballMatchesListClipper;
use App\Services\Stats\Clippers\CzBasketball\CzBasketballTeamPageClipper;
use App\Services\Support\ConsoleService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ExternalStatsSyncService
{
    public function __construct(
        protected StatFetcherInterface $fetcher,
        protected RosterSyncService $rosterSyncService,
        protected MatchSyncService $matchSyncService,
        protected StatisticSyncService $statisticSyncService,
        protected StatNormalizerInterface $normalizer,
        protected PlayerSyncService $playerSyncService,
        protected CompetitionSyncService $competitionSyncService
    ) {}

    /**
     * Synchronizuje celou sezónu pro daný tým.
     *
     * @param  array  $options  [limit, force, fresh]
     */
    public function syncTeamSeason(int $teamId, int $seasonId, array $options = []): void
    {
        $parentRun = isset($options['parent_run_id']) ? ExternalImportRun::find($options['parent_run_id']) : null;

        if (ConsoleService::isStopped() || ($parentRun && ($parentRun->isCancelled() || $parentRun->status === 'skipped'))) {
            ConsoleService::log('Synchronizace týmu přeskočena (STOP flag nebo zrušeno/přeskočeno).', 'warning');

            return;
        }

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

        // Pokud sezóna není aktivní a již byla jednou úspěšně synchronizována, přeskakujeme ji
        // pokud není vynucen FORCE mode.
        if (! $season->is_active && $config->last_synced_at && ! ($options['force'] ?? false)) {
            ConsoleService::log("Přeskakuji neaktivní sezónu {$season->name} pro tým {$team->slug} (již synchronizováno {$config->last_synced_at->format('d.m.Y H:i')}).", 'info');
            return;
        }

        ConsoleService::log("Zahajuji synchronizaci týmu {$team->slug} pro sezónu {$season->name}".(($options['force'] ?? false) ? ' (FORCE mode)' : '').(($options['fresh'] ?? false) ? ' (FRESH mode)' : '').(($options['ai'] ?? false) ? ' (AI mode)' : ''), 'info');
        Log::info("Zahajuji synchronizaci týmu {$team->slug} pro sezónu {$season->name}");

        $errors = [];
        $parentRun = isset($options['parent_run_id']) ? ExternalImportRun::find($options['parent_run_id']) : null;

        // 1. Synchronizace soupisky (zjistí i URL soutěže)
        if ($options['sync_roster'] ?? true) {
            try {
                if ($parentRun) {
                    $parentRun->updateProgress(label: ($parentRun->current_item_label ?: 'Sync') . ': Soupiska');
                }
                ConsoleService::log('- Synchronizace soupisky...');
                $this->syncRoster($team, $season, $config, $options);
                ConsoleService::log('  Soupiska OK.', 'success');
            } catch (\Exception $e) {
                $errors[] = 'Soupiska: '.$e->getMessage();
                ConsoleService::log('  Chyba při synchronizaci soupisky: '.$e->getMessage(), 'error');
                Log::error('Chyba při synchronizaci soupisky: '.$e->getMessage());
            }
        }

        // 1b. Synchronizace dat soutěže (pokud je URL k dispozici)
        if ($config->competition_url && ($options['sync_competition'] ?? true)) {
            try {
                $this->competitionSyncService->sync($team, $season, $config, $options);
            } catch (\Exception $e) {
                $errors[] = 'Soutěž: '.$e->getMessage();
                Log::error('Chyba při synchronizaci soutěže: '.$e->getMessage());
            }
        }

        // 2. Synchronizace seznamu zápasů (z týmové stránky)
        if ($options['sync_matches'] ?? true) {
            try {
                if ($parentRun) {
                    $parentRun->updateProgress(label: ($parentRun->current_item_label ?: 'Sync') . ': Zápasy');
                }
                ConsoleService::log('- Synchronizace seznamu zápasů...');
                $this->syncMatchesList($team, $season, $config, $options);
                ConsoleService::log('  Seznam zápasů OK.', 'success');
            } catch (\Exception $e) {
                $errors[] = 'Zápasy: '.$e->getMessage();
                ConsoleService::log('  Chyba při synchronizaci seznamu zápasů: '.$e->getMessage(), 'error');
                Log::error('Chyba při synchronizaci seznamu zápasů: '.$e->getMessage());
            }
        }

        if ($parentRun) {
            $parentRun->updateProgress(label: ($parentRun->current_item_label ?: 'Sync') . ': Detaily zápasů');
        }

        // 3. Verifikace konzistence
        try {
            $this->matchSyncService->validateSeasonConsistency($team, $season, $config);
        } catch (\Exception $e) {
            Log::warning("Chyba při verifikaci konzistence sezóny: ".$e->getMessage());
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
            if (ConsoleService::isStopped() || $run->isCancelled() || $run->status === 'skipped') {
                throw new \Exception('Synchronizace soupisky zastavena uživatelem.');
            }

            $run->updateMetadata(['url' => $config->team_season_url]);
            $html = $this->fetcher->fetch($config->team_season_url, $run);

            if (ConsoleService::isStopped() || $run->isCancelled() || $run->status === 'skipped') {
                throw new \Exception('Synchronizace soupisky zastavena uživatelem po stažení HTML.');
            }

            ConsoleService::log("    - Staženo " . number_format(strlen($html) / 1024, 1) . " KB HTML.", 'debug');

            $aiOnly = config('external_sources.czbasketball.ai_only', env('CZBASKETBALL_AI_ONLY', false)) || ($options['ai'] ?? false);

            $clipper = app(CzBasketballTeamPageClipper::class);
            $clips = $clipper->clip($html, $config->team_season_url);
            ConsoleService::log("    - Extrahováno " . count($clips) . " fragmentů (clips) z HTML.", 'debug');

            // CNH a JSON Linky
            $cnh = $clipper->buildCnh($clips);
            $linksJson = $clipper->buildExtractedLinksJson($clips);

            // Uložit CNH a JSON pro debug/AI
            $year = $season->year ?? 'unknown';
            $basePath = "external/czbasketball/clips/{$config->external_team_id}/y{$year}";
            \Illuminate\Support\Facades\Storage::disk('local')->makeDirectory($basePath);
            \Illuminate\Support\Facades\Storage::disk('local')->put("{$basePath}/team_page_cnh.html", $cnh);
            \Illuminate\Support\Facades\Storage::disk('local')->put("{$basePath}/extracted_links.json", $linksJson);

            $rosterClip = collect($clips)->firstWhere('id', 'roster_table');
            $headerClip = collect($clips)->firstWhere('id', 'team_header');

            $run->updateMetadata([
                'html_size' => strlen($html),
                'cnh_size' => strlen($cnh),
                'clips_found' => count($clips),
                'clip_ids' => collect($clips)->pluck('id')->toArray(),
                'cnh_file' => "{$basePath}/team_page_cnh.html",
                'links_json_file' => "{$basePath}/extracted_links.json",
            ]);

            $usedAi = false;
            $data = null;
            $fragmentHtml = '';

            if ($aiOnly) {
                if (ConsoleService::isStopped()) {
                    throw new \Exception('AI normalizace soupisky zastavena uživatelem.');
                }

                if (!$rosterClip) {
                    throw new \Exception('Roster table clip not found for AI-only mode.');
                }

                $data = $this->normalizer->normalize($rosterClip->htmlFragment, [
                    'type' => 'roster',
                    'strict_schema' => $this->getRosterSchema(),
                    'context_links' => $linksJson,
                ]);
                $fragmentHtml = $rosterClip->htmlFragment;
                $usedAi = true;
            } else {
                try {
                    $extractor = app(TeamRosterExtractor::class);
                    $result = $extractor->extract($html);
                    $data = $result['data'];
                    $fragmentHtml = $result['fragment_html'];
                } catch (\Exception $e) {
                    Log::warning("DOM extractor selhal pro soupisku týmu {$team->slug}, zkouším AI fallback. Chyba: ".$e->getMessage());

                    if (!$rosterClip) {
                        throw new \Exception('DOM extractor failed and Roster table clip not found for AI fallback.');
                    }

                    $data = $this->normalizer->normalize($rosterClip->htmlFragment, [
                        'type' => 'roster',
                        'strict_schema' => $this->getRosterSchema(),
                        'context_links' => $linksJson,
                    ]);
                    $fragmentHtml = $rosterClip->htmlFragment;
                    $usedAi = true;
                }
            }

            // Integrace TEAM HEADER (volitelně)
            if ($headerClip) {
                try {
                    $headerData = $aiOnly
                        ? $this->normalizer->normalize($headerClip->htmlFragment, ['type' => 'team_header', 'strict_schema' => $this->getTeamHeaderSchema()])
                        : app(\App\Services\Stats\Extractors\CzBasketball\TeamHeaderExtractor::class)->extract($html, [
                            'external_season_year' => $config->external_season_year
                        ])['data'];

                    if ($headerData && isset($headerData->metadata['team_name'])) {
                        $run->updateMetadata(['team_name_external' => $headerData->metadata['team_name']]);

                        // Aktualizace konfigurace o metadata týmu
                        $configMetadata = $config->metadata ?? [];
                        $configMetadata['coach'] = $headerData->metadata['coach'] ?? $configMetadata['coach'] ?? null;
                        $configMetadata['assistants'] = $headerData->metadata['assistants'] ?? $configMetadata['assistants'] ?? [];
                        $configMetadata['manager'] = $headerData->metadata['manager'] ?? $configMetadata['manager'] ?? null;
                        $configMetadata['venue'] = $headerData->metadata['venue'] ?? $configMetadata['venue'] ?? null;
                        $configMetadata['website'] = $headerData->metadata['website'] ?? $configMetadata['website'] ?? null;
                        $configMetadata['last_full_header_sync_at'] = now()->toDateTimeString();

                        $config->update(['metadata' => array_filter($configMetadata)]);

                        if (!empty($headerData->metadata['competition']) && empty($config->competition_label)) {
                            $config->update(['competition_label' => $headerData->metadata['competition']]);
                        }

                        if (!empty($headerData->metadata['competition_url']) && empty($config->competition_url)) {
                            // Zajistíme, aby URL byla absolutní, pokud je relativní
                            $compUrl = $headerData->metadata['competition_url'];
                            if (str_starts_with($compUrl, '/')) {
                                $compUrl = 'https://cz.basketball' . $compUrl;
                            }
                            $config->update(['competition_url' => $compUrl]);
                        }
                    }
                } catch (\Exception $e) {
                    Log::warning("Nepodařilo se extrahovat team header: " . $e->getMessage());
                }
            }

            $hash = hash('sha256', $fragmentHtml);

            if ($run->isIdenticalToLast($hash) && ! ($options['force'] ?? false) && ! ($options['fresh'] ?? false)) {
                ConsoleService::log("    - Obsah soupisky se nezměnil (hash match), přeskakuji import.", 'info');
                $run->skip();

                return;
            }

            $run->update([
                'content_hash' => $hash,
                'metadata' => array_merge($run->metadata ?? [], [
                    'used_dom_extractor' => ! $usedAi,
                    'used_ai' => $usedAi,
                    'ai_only_mode' => $aiOnly,
                ]),
            ]);

            if ($usedAi) {
                $run->update(['status' => 'partial_failed', 'error_summary' => 'DOM extractor failed or AI-only used, used AI.']);
                if ($data->metadata) {
                    $run->updateMetadata($data->metadata);
                }
            }

            $this->rosterSyncService->syncWithData($config, $data);
            ConsoleService::log("    - Synchronizováno " . count($data->rows) . " hráčů do databáze.", 'success');

            // Link following: Hráči
            if (!empty($rosterClip->links) && ($options['follow_players'] ?? false)) {
                $playerLinks = collect($rosterClip->links)
                    ->filter(fn($l) => !empty($m = preg_match('/\/hrac\/(\d+)/', ($l['url'] ?? ($l['href'] ?? '')), $matches)))
                    ->take(10);

                foreach ($playerLinks as $link) {
                    // Tady by šlo spustit sync pro detail hráče, pokud bychom ho měli implementovaný
                    $logUrl = $link['url'] ?? ($link['href'] ?? '');
                    Log::debug("Found player link to follow: {$logUrl}");
                }
            }

            $run->finish([
                'extracted_count' => count($data->rows),
                'imported_count' => count($data->rows),
            ]);
        } catch (\Exception $e) {
            if (isset($html)) {
                $sanitized = $this->normalizer->sanitizeHtml($html);
                \Illuminate\Support\Facades\Storage::disk('local')->put("debug_html/run_{$run->id}.html", $sanitized);
                $run->updateMetadata(['debug_html_file' => "debug_html/run_{$run->id}.html"]);
            }
            $run->fail($e);
            throw $e;
        }
    }

    /**
     * Synchronizuje seznam zápasů a naplánuje detailní synchronizaci.
     */
    protected function syncMatchesList(Team $team, Season $season, ExternalTeamSeasonConfig $config, array $options): void
    {
        if (ConsoleService::isStopped()) {
            throw new \Exception('Synchronizace seznamu zápasů zastavena uživatelem.');
        }

        \Log::info("START: syncMatchesList for team {$team->slug}");
        $run = ExternalImportRun::start('czbasketball', $season->id, $team->id, 'matches_list', $config->external_team_id);

        try {
            $run->updateMetadata(['url' => $config->matches_list_url]);
            $html = $this->fetcher->fetch($config->matches_list_url, $run);

            if (ConsoleService::isStopped() || $run->isCancelled() || $run->status === 'skipped') {
                throw new \Exception('Synchronizace seznamu zápasů zastavena uživatelem po stažení HTML.');
            }

            ConsoleService::log("    - Staženo " . number_format(strlen($html) / 1024, 1) . " KB HTML.", 'debug');

            $aiOnly = config('external_sources.czbasketball.ai_only', env('CZBASKETBALL_AI_ONLY', false)) || ($options['ai'] ?? false);

            $clipper = app(CzBasketballMatchesListClipper::class);
            $clips = $clipper->clip($html, $config->matches_list_url);

            // JSON Linky pro kontext
            $teamPageClipper = app(CzBasketballTeamPageClipper::class);
            $linksJson = $teamPageClipper->buildExtractedLinksJson($clips);

            $run->updateMetadata([
                'html_size' => strlen($html),
                'clips_found' => count($clips),
                'clip_ids' => collect($clips)->pluck('id')->toArray(),
                'links_json_file' => "external/czbasketball/clips/{$config->external_team_id}/y" . ($season->year ?? 'unknown') . "/matches_list_links.json",
            ]);

            // Uložit linky
            $year = $season->year ?? 'unknown';
            $basePath = "external/czbasketball/clips/{$config->external_team_id}/y{$year}";
            \Illuminate\Support\Facades\Storage::disk('local')->makeDirectory($basePath);
            \Illuminate\Support\Facades\Storage::disk('local')->put("{$basePath}/matches_list_links.json", $linksJson);

            $usedAi = false;
            $allRows = [];
            $fragmentHtml = '';

            if ($aiOnly) {
                if (empty($clips)) {
                    throw new \Exception('No matches list clips found for AI-only mode.');
                }

                foreach ($clips as $clip) {
                    $data = $this->normalizer->normalize($clip->htmlFragment, [
                        'type' => 'matches_list',
                        'strict_schema' => $this->getMatchesListSchema(),
                        'context_links' => $linksJson,
                    ]);
                    if ($data->metadata) {
                        $run->updateMetadata($data->metadata);
                    }
                    $allRows = array_merge($allRows, $data->rows);
                    $fragmentHtml .= $clip->htmlFragment;
                }
                $usedAi = true;
                $finalData = new \App\Services\Stats\DTO\NormalizedTableDTO('Matches List', [], $allRows, ['ai_normalized' => true]);
            } else {
                try {
                    \Log::info("Extracting matches for {$team->slug}");
                    $extractor = app(MatchesListExtractor::class);
                    $result = $extractor->extract($html);
                    $finalData = $result['data'];
                    $fragmentHtml = $result['fragment_html'];

                    // AUTO AI FALLBACK logic
                    if (empty($finalData->rows) && strlen($html) > 500) {
                        throw new \Exception('DOM extractor returned zero matches, but HTML looks valid.');
                    }

                    // (zjednodušeno pro stručnost multi_edit, ale zachovávám principy)
                } catch (\Exception $e) {
                    Log::warning("DOM extractor selhal pro seznam zápasů týmu {$team->slug}, zkouším AI fallback. Chyba: ".$e->getMessage());

                    if (empty($clips)) {
                        throw new \Exception('DOM extractor failed and no matches list clips found for AI fallback.');
                    }

                    foreach ($clips as $clip) {
                        $data = $this->normalizer->normalize($clip->htmlFragment, [
                            'type' => 'matches_list',
                            'strict_schema' => $this->getMatchesListSchema(),
                            'context_links' => $linksJson,
                        ]);
                        if ($data->metadata) {
                            $run->updateMetadata($data->metadata);
                        }
                        $allRows = array_merge($allRows, $data->rows);
                        $fragmentHtml .= $clip->htmlFragment;
                    }
                    $usedAi = true;
                    $finalData = new \App\Services\Stats\DTO\NormalizedTableDTO('Matches List', [], $allRows, ['ai_normalized' => true]);
                }
            }

            $hash = hash('sha256', $fragmentHtml);

            if ($run->isIdenticalToLast($hash) && ! ($options['force'] ?? false) && ! ($options['fresh'] ?? false)) {
                ConsoleService::log("    - Obsah seznamu zápasů se nezměnil (hash match).", 'info');
                $run->skip();
            } else {
                $run->update([
                    'content_hash' => $hash,
                    'metadata' => array_merge($run->metadata ?? [], [
                        'used_dom_extractor' => ! $usedAi,
                        'used_ai' => $usedAi,
                        'ai_only_mode' => $aiOnly,
                        'force' => $options['force'] ?? false,
                        'fresh' => $options['fresh'] ?? false,
                    ]),
                ]);

                if ($usedAi) {
                    $run->update(['status' => 'partial_failed', 'error_summary' => 'DOM extractor failed or AI-only used, used AI.']);
                }

                \Log::info("Syncing matches for {$team->slug}, count: ".count($finalData->rows));
                foreach ($finalData->rows as $row) {
                    $rowValues = $row->values;
                    $rowValues['scheduled_at'] ??= null;
                    $rowValues['home_team'] ??= 'Unknown';
                    $rowValues['away_team'] ??= 'Unknown';
                    $rowValues['score'] ??= null;
                    $rowValues['status'] ??= 'planned';
                    $rowValues['external_match_id'] ??= $rowValues['id'] ?? $rowValues['match_external_id'] ?? null;

                    $this->matchSyncService->sync($team, $season, $rowValues, $run);
                }

                // Link following: Detaily zápasů
                if (($options['follow_matches'] ?? true) && !empty($clips)) {
                    $allMatchLinks = collect($clips)
                        ->flatMap(fn($c) => $c->links)
                        ->filter(fn($l) => str_contains($l['url'] ?? ($l['href'] ?? ''), '/zapas/'));
                    Log::debug("Found " . $allMatchLinks->count() . " match links to follow from clips.");
                }

                $run->finish([
                    'extracted_count' => count($finalData->rows),
                    'imported_count' => count($finalData->rows),
                ]);
            }

            // Naplánování detailů zápasů
            if ($options['sync_details'] ?? true) {
                $this->dispatchMatchDetailJobs($team, $season, $options);
            }

        } catch (\Exception $e) {
            if (isset($html)) {
                $sanitized = $this->normalizer->sanitizeHtml($html);
                \Illuminate\Support\Facades\Storage::disk('local')->put("debug_html/run_{$run->id}.html", $sanitized);
                $run->updateMetadata(['debug_html_file' => "debug_html/run_{$run->id}.html"]);
            }
            $run->fail($e);
            throw $e;
        }
    }

    /**
     * Rozdělí detailní synchronizaci zápasů do fronty.
     */
    protected function dispatchMatchDetailJobs(Team $team, Season $season, array $options): void
    {
        $excesive = $options['excesive'] ?? false;
        $force = ($options['force'] ?? false) || ($options['fresh'] ?? false) || $excesive;
        $defaultLimit = $force ? 100 : 15;
        if ($excesive) {
            $defaultLimit = 1000; // Prakticky bez limitu pro jednu sezónu
        }
        $limit = $options['maxMatchDetails'] ?? $options['limit'] ?? $defaultLimit;
        $recentOnly = ($options['recentOnly'] ?? false) && !$excesive;

        ConsoleService::log("    - Parametry detailů: limit=$limit, force=".($force ? 'true' : 'false').", recentOnly=".($recentOnly ? 'true' : 'false').", excesive=".($excesive ? 'true' : 'false'), 'debug');

        $query = BasketballMatch::where('team_id', $team->id)
            ->where('season_id', $season->id)
            ->whereIn('status', ['finished', 'planned', 'scheduled', 'played'])
            ->where('metadata', 'like', '%"external_id":%');

        if ($recentOnly) {
            $days = config('external_sources.czbasketball.limits.recent_match_days', 3);
            $query->where('scheduled_at', '>=', now()->subDays($days)->toDateTimeString());
        }

        if (! $force) {
            $boxscoreSet = DB::table('statistic_sets')->where('slug', 'match-boxscore')->first();
            $boxscoreSetId = $boxscoreSet?->id;

            // Zápasy bez statistik nebo ty, které byly synchronizovány před více než 24 hodinami
            $query->where(function ($q) use ($boxscoreSetId) {
                if ($boxscoreSetId) {
                    $q->whereNotExists(function ($sub) use ($boxscoreSetId) {
                        $sub->select(DB::raw(1))
                            ->from('statistic_rows')
                            ->whereColumn('statistic_rows.basketball_match_id', 'matches.id')
                            ->where('statistic_rows.statistic_set_id', $boxscoreSetId);
                    })->orWhereNotNull('metadata'); // Dočasně vybereme s metadaty, profiltrujeme v PHP
                } else {
                    $q->whereNotNull('metadata');
                }
            });
        }

        // Načteme dostatečný počet zápasů pro filtraci v PHP (abychom se vyhnuli JSON query v SQL)
        $matches = $query->limit($limit)->get();
        $totalMatches = $matches->count();

        ConsoleService::log("    - Nalezeno $totalMatches zápasů splňujících kritéria pro detailní synchronizaci.", 'info');

        $dispatchedCount = 0;
        foreach ($matches as $match) {
            $matchExtId = $match->metadata['external_id'] ?? 'N/A';

            if (ConsoleService::isStopped()) {
                ConsoleService::log('Plánování detailů zápasů zastaveno uživatelem.', 'warning');
                break;
            }

            if ($dispatchedCount >= $limit) {
                break;
            }

            if (! $force) {
                $lastSynced = $match->metadata['last_synced_at'] ?? null;
                if ($lastSynced && \Illuminate\Support\Carbon::parse($lastSynced)->gt(now()->subDay())) {
                    ConsoleService::log("    - Zápas {$match->id} ($matchExtId) přeskočen (naposledy synchronizováno: $lastSynced)", 'debug');
                    continue;
                }
            }

            ConsoleService::log("    - Plánuji detail zápasu: ID {$match->id} ($matchExtId)", 'debug');
            $job = SyncMatchDetailJob::dispatch($match->id, $options);

            // Pokud používáme sync frontu, přidáme malou pauzu, aby se ulevilo CPU a API
            if (config('queue.default') === 'sync' || env('QUEUE_CONNECTION') === 'sync') {
                usleep(500000); // 0.5s pauza mezi zápasy v sync módu
            } else {
                // V asynchronní frontě můžeme přidat delay, aby se joby nespustily všechny naráz (Throttling)
                $job->delay(now()->addSeconds($dispatchedCount * 2));
            }

            ConsoleService::log("    -> Naplánován detail zápasu: ID {$match->id} ({$match->metadata['external_id']})", 'debug');
            $dispatchedCount++;
        }

        ConsoleService::log("    - Celkem naplánováno $dispatchedCount detailních synchronizací.", 'success');
    }

    /**
     * Synchronizuje detaily konkrétního zápasu (boxscore).
     */
    public function syncMatchDetail(int $matchId, array $options = []): void
    {
        $parentRunId = $options['parent_run_id'] ?? null;
        $parentRun = $parentRunId ? ExternalImportRun::find($parentRunId) : null;

        if (ConsoleService::isStopped() || ($parentRun && ($parentRun->isCancelled() || $parentRun->status === 'skipped'))) {
            // Log::info('syncMatchDetail přeskočen - STOP flag nebo zrušeno/přeskočeno');
            return;
        }

        $match = BasketballMatch::with(['team', 'season'])->findOrFail($matchId);
        $externalMatchId = $match->metadata['external_id'] ?? null;

        if (! $externalMatchId) {
            return;
        }

        // Pokud je zápas v neaktivní sezóně a již máme metadata o boxscore (indikátor úspěšného stažení detailu),
        // přeskakujeme synchronizaci detailu, pokud není vynucen FORCE mode.
        if (! $match->season->is_active && isset($match->metadata['boxscore_synced_at']) && ! ($options['force'] ?? false)) {
            return;
        }

        \Log::info("START: syncMatchDetail for match {$matchId} (ext_id: {$externalMatchId})");
        $url = 'https://cz.basketball/zapas/'.$externalMatchId;
        $run = ExternalImportRun::start('czbasketball', $match->season_id, $match->team_id, 'match_detail', $externalMatchId);
        $run->updateMetadata(['url' => $url]);

        if ($options['force'] ?? false) {
            $run->updateMetadata(['force' => true]);
        }
        if ($options['fresh'] ?? false) {
            $run->updateMetadata(['fresh' => true]);
        }

        try {
            $html = $this->fetcher->fetch($url, $run);

            if (ConsoleService::isStopped() || $run->isCancelled() || $run->status === 'skipped' || ($parentRun && ($parentRun->isCancelled() || $parentRun->status === 'skipped'))) {
                throw new \Exception('Synchronizace detailu zápasu zastavena uživatelem po stažení HTML.');
            }

            $aiOnly = config('external_sources.czbasketball.ai_only', env('CZBASKETBALL_AI_ONLY', false)) || ($options['ai'] ?? false);

            $clipper = app(CzBasketballMatchDetailClipper::class);
            $clips = $clipper->clip($html, $url);

            $boxscoreClips = collect($clips)->filter(fn($c) => str_starts_with($c->id, 'boxscore_'));

            $run->updateMetadata([
                'html_size' => strlen($html),
                'clips_found' => count($clips),
                'clip_ids' => collect($clips)->pluck('id')->toArray(),
            ]);

            $usedAi = false;
            $tables = [];
            $fragmentHtml = '';

            if ($aiOnly) {
                if ($boxscoreClips->isEmpty()) {
                    throw new \Exception('No boxscore clips found for AI-only mode.');
                }

                foreach ($boxscoreClips as $clip) {
                    $data = $this->normalizer->normalize($clip->htmlFragment, [
                        'type' => 'match_boxscore',
                        'strict_schema' => $this->getBoxscoreSchema(),
                    ]);
                    $tables[] = $data;
                    $fragmentHtml .= $clip->htmlFragment;
                }
                $usedAi = true;
                $mainData = $tables[0] ?? null;
            } else {
                try {
                    $extractor = app(MatchDetailBoxscoreExtractor::class);
                    $result = $extractor->extract($html);
                    $mainData = $result['data'];
                    $fragmentHtml = $result['fragment_html'];
                    $tables = $result['tables'] ?? [$mainData];
                } catch (\Exception $e) {
                    Log::warning("DOM extractor selhal pro zápas $externalMatchId. Chyba: ".$e->getMessage());

                    if ($boxscoreClips->isNotEmpty()) {
                        Log::info("Zkouším AI fallback pro zápas $externalMatchId.");
                        foreach ($boxscoreClips as $clip) {
                            $data = $this->normalizer->normalize($clip->htmlFragment, [
                                'type' => 'match_boxscore',
                                'strict_schema' => $this->getBoxscoreSchema(),
                            ]);
                            $tables[] = $data;
                            $fragmentHtml .= $clip->htmlFragment;
                        }
                        $usedAi = true;
                        $mainData = $tables[0] ?? null;
                    } elseif ($match->scheduled_at && $match->scheduled_at->isFuture()) {
                        // Pokud je zápas v budoucnu a nemáme klipy pro AI, je to v pořádku,
                        // pokud se aspoň podaří extrahovat metadata (hlavička, srovnání atd.)
                        // Ale extractor už selhal, tak zkusíme jestli aspoň něco nevrátil (neměl by vyhazovat výjimku pokud jen chybí tabulky)
                        // V MatchDetailBoxscoreExtractor jsem to upravil aby nevyhazoval výjimku při chybějících tabulkách
                        Log::info("Budoucí zápas $externalMatchId bez boxscore, pokračuji s prázdnými tabulkami.");
                    } else {
                        throw new \Exception('DOM extractor failed and no boxscore clips found for AI fallback.');
                    }
                }
            }

            $hash = hash('sha256', $fragmentHtml);
            if ($run->isIdenticalToLast($hash) && ! ($options['force'] ?? false) && ! ($options['fresh'] ?? false)) {
                ConsoleService::log("    - Obsah detailu zápasu se nezměnil (hash match).", 'info');
                $run->skip();

                return;
            }

            $run->update([
                'content_hash' => $hash,
                'metadata' => array_merge($run->metadata ?? [], [
                    'used_dom_extractor' => ! $usedAi,
                    'used_ai' => $usedAi,
                    'ai_only_mode' => $aiOnly,
                ]),
            ]);

            if ($usedAi) {
                $run->update(['status' => 'partial_failed', 'error_summary' => 'DOM extractor failed or AI-only used, used AI.']);
            }

            // Vždy smažeme staré statistiky zápasu před importem nových (aby nedocházelo k duplikacím při opakované synchronizaci)
            $this->statisticSyncService->clearMatchBoxscore($match, $run);

            // Předběžná příprava metadat z hlavičky pro detekci týmů v tabulkách
            $header = $mainData->metadata['header'] ?? [];
            Log::info("DEBUG SYNC: Header found", ['header_keys' => array_keys($header)]);
            $matchMetadata = $match->metadata ?? [];
            $matchMetadata['last_synced_at'] = now()->toDateTimeString();

            foreach ($tables as $tableData) {
                $this->statisticSyncService->syncMatchBoxscore($match, $tableData, $run);

                // OSVĚŽENÍ: syncMatchBoxscore mění metadata přímo v DB (opponent_boxscore),
                // musíme je znovu načíst z DB, abychom je nepřepsali na konci metody zastaralými lokálními daty.
                $freshMatch = DB::table('matches')->where('id', $match->id)->first();
                if ($freshMatch && $freshMatch->metadata) {
                    $matchMetadata = json_decode($freshMatch->metadata, true) ?: $matchMetadata;
                }

                // Pokud tabulka obsahuje sumární řádek, uložíme ho do metadat zápasu pro rychlý přístup
                $totalRow = collect($tableData->rows)->first(fn($r) => !empty($r->metadata['is_total']));
                if ($totalRow) {
                    $tableName = mb_strtolower($tableData->name);
                    $homeName = mb_strtolower($header['home_team'] ?? '');
                    $awayName = mb_strtolower($header['away_team'] ?? '');

                    if ($homeName && str_contains($tableName, $homeName)) {
                        $matchMetadata['stats_home'] = $totalRow->values;
                    } elseif ($awayName && str_contains($tableName, $awayName)) {
                        $matchMetadata['stats_away'] = $totalRow->values;
                    } else {
                        // Fallback podle pořadí (první je obvykle domácí)
                        $index = array_search($tableData, $tables);
                        if ($index === 0 && !isset($matchMetadata['stats_home'])) {
                            $matchMetadata['stats_home'] = $totalRow->values;
                        } elseif ($index === 1 && !isset($matchMetadata['stats_away'])) {
                            $matchMetadata['stats_away'] = $totalRow->values;
                        }
                    }
                }
            }

            // Aktualizace metadat a skóre zápasu z extrahované hlavičky
            if (!empty($header['periods_text'])) {
                $matchMetadata['periods'] = $header['periods_text'];
            }
            if (!empty($header['periods'])) {
                $matchMetadata['periods_detailed'] = $header['periods'];
            }
            if (!empty($header['venue'])) {
                $matchMetadata['venue'] = $header['venue'];
                if (empty($match->location)) {
                    $match->location = $header['venue'];
                }
            }
            if (!empty($header['referees'])) {
                $matchMetadata['referees'] = $header['referees'];
            }
            if (!empty($header['attendance'])) {
                $matchMetadata['attendance'] = $header['attendance'];
            }
            if (!empty($header['commissioner'])) {
                $matchMetadata['commissioner'] = $header['commissioner'];
            }
            if (!empty($header['scheduled_at'])) {
                // Aktualizujeme čas zápasu, pokud je v detailu nalezen (bývá přesnější než v seznamu)
                $match->scheduled_at = $header['scheduled_at'];
            }

            // Uložení nejlepších hráčů
            $bestPlayers = $mainData->metadata['best_players'] ?? [];
            if (!empty($bestPlayers)) {
                $matchMetadata['best_players'] = $bestPlayers;
                // Zpracování fotografií nejlepších hráčů (pokud jsou naši)
                $this->processBestPlayerPhotos($bestPlayers, $run);
            }

            // Uložení srovnání týmů a posledních zápasů
            if (!empty($mainData->metadata['team_comparison'])) {
                $matchMetadata['team_comparison'] = $mainData->metadata['team_comparison'];
            }
            if (!empty($mainData->metadata['last_matches'])) {
                $matchMetadata['last_matches'] = $mainData->metadata['last_matches'];
            }
            if (!empty($mainData->metadata['mutual_matches'])) {
                $matchMetadata['mutual_matches'] = $mainData->metadata['mutual_matches'];
            }

            // Označíme, že synchronizace detailu proběhla v pořádku (včetně boxscore pokud byly nalezeny tabulky)
            $matchMetadata['boxscore_synced_at'] = now()->toDateTimeString();

            $updateData = ['metadata' => $matchMetadata];
            Log::info("DEBUG SYNC: Match metadata before update", ['metadata_keys' => array_keys($matchMetadata)]);

            if (!empty($header['score']) && preg_match('/(\d+)\s*:\s*(\d+)/', $header['score'], $scoreMatches)) {
                $scoreHome = (int) $scoreMatches[1];
                $scoreAway = (int) $scoreMatches[2];

                // Aktualizujeme skóre jen pokud je validní a buď chybí, nebo jsme v force/fresh módu
                if (($options['force'] ?? false) || ($options['fresh'] ?? false) || ($match->score_home === null && $match->score_away === null)) {
                    $updateData['score_home'] = $scoreHome;
                    $updateData['score_away'] = $scoreAway;
                    $updateData['status'] = 'finished'; // Sjednoceno na 'finished' dle předchozích úkolů
                }
            } elseif ($match->scheduled_at && $match->scheduled_at->isFuture()) {
                // Pro budoucí zápasy bez skóre v hlavičce zajistíme, aby skóre bylo null
                $updateData['score_home'] = null;
                $updateData['score_away'] = null;
                $updateData['status'] = 'scheduled';
            }

            $match->update($updateData);

            // Po úspěšné synchronizaci detailu (i pro budoucí zápasy) vyvoláme přepočet predikce
            if (class_exists(\App\Jobs\ComputeMatchPredictionJob::class)) {
                \App\Jobs\ComputeMatchPredictionJob::dispatch($match->id);
                Log::info("Dispatched ComputeMatchPredictionJob for match {$match->id} after detail sync.");
            }

            $run->finish([
                'extracted_count' => count($mainData?->rows ?? []),
                'imported_count' => count($mainData?->rows ?? []),
            ]);

        } catch (\Exception $e) {
            if (isset($html)) {
                $sanitized = $this->normalizer->sanitizeHtml($html);
                \Illuminate\Support\Facades\Storage::disk('local')->put("debug_html/run_{$run->id}.html", $sanitized);
                $run->updateMetadata(['debug_html_file' => "debug_html/run_{$run->id}.html"]);
            }
            $run->fail($e);
            throw $e;
        }
    }

    /**
     * Zpracuje fotografie nejlepších hráčů a uloží je do jejich portfolia.
     */
    protected function processBestPlayerPhotos(array $bestPlayers, ExternalImportRun $run): void
    {
        foreach ($bestPlayers as $playerData) {
            $extId = $playerData['external_id'] ?? null;
            $photoUrl = $playerData['photo_url'] ?? null;

            if (!$extId || !$photoUrl) {
                continue;
            }

            // Hledáme uživatele podle externího ID (mapování v external_mappings)
            $user = \App\Models\User::whereHas('externalMappings', function ($q) use ($extId) {
                $q->where('source_key', 'czbasketball')
                  ->where('external_id', $extId);
            })->first();

            if (!$user) {
                // Zkusíme najít podle jména, pokud je to náš tým (Kbely)
                if (str_contains(mb_strtolower($playerData['team'] ?? ''), 'kbely')) {
                    $nameParts = explode(' ', (string) ($playerData['name'] ?? ''));
                    if (count($nameParts) >= 2) {
                        $user = \App\Models\User::where('last_name', 'like', (string) $nameParts[count($nameParts)-1])
                            ->where('first_name', 'like', (string) $nameParts[0])
                            ->first();
                    }
                }
            }

            if ($user) {
                // Pokud je to náš hráč, synchronizujeme alespoň tuto fotografii
                // Hloubková synchronizace detailu (historie atd.) se provádí samostatně
                $this->playerSyncService->syncPhoto($user, $photoUrl);
            }
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

    protected function getTeamHeaderSchema(): string
    {
        return '{
  "team_name": "string",
  "season_year": "int|null",
  "competition": "string|null",
  "extras": {
    "coach": "string|null",
    "venue": "string|null"
  }
}';
    }

    protected function getRosterSchema(): string
    {
        return '{
  "rows": [
    {
      "player_external_id": "string|null (extract from /hrac/{id})",
      "player_name": "string",
      "birth_year": "int|null",
      "position": "string|null",
      "jersey": "string|null"
    }
  ],
  "warnings": ["string"]
}';
    }

    protected function getMatchesListSchema(): string
    {
        return '{
  "rows": [
    {
      "match_external_id": "string|null (extract from /zapas/{id})",
      "scheduled_at": "string|null (YYYY-MM-DD HH:MM)",
      "home_team": "string",
      "away_team": "string",
      "is_home_for_team": "boolean|null",
      "opponent_name": "string",
      "score_home": "int|null",
      "score_away": "int|null",
      "status": "planned|completed|postponed|cancelled|unknown",
      "round": "string|null"
    }
  ],
  "warnings": ["string"]
}';
    }

    protected function getBoxscoreSchema(): string
    {
        return '{
  "team_label": "string",
  "rows": [
    {
      "player_external_id": "string|null (extract from /hrac/{id})",
      "player_name": "string",
      "values": {
        "pts": "int",
        "minutes": "int|null",
        "fg2_made": "int|null",
        "fg2_att": "int|null",
        "fg3_made": "int|null",
        "fg3_att": "int|null",
        "ft_made": "int|null",
        "ft_att": "int|null",
        "fouls": "int|null",
        "rebounds_total": "int|null",
        "assists": "int|null",
        "efficiency": "int|null",
        "plus_minus": "int|null"
      }
    }
  ],
  "warnings": ["string"]
}';
    }
}
