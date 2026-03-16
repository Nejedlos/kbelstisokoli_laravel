<?php

namespace App\Services\Stats\Sync;

use App\Models\CompetitionStanding;
use App\Models\ExternalImportRun;
use App\Models\ExternalTeamSeasonConfig;
use App\Models\Season;
use App\Models\Team;
use App\Services\Stats\Contracts\StatFetcherInterface;
use App\Services\Stats\Extractors\CzBasketball\CompetitionScheduleExtractor;
use App\Services\Stats\Extractors\CzBasketball\CompetitionStandingExtractor;
use App\Services\Support\ConsoleService;
use Illuminate\Support\Facades\Log;

class CompetitionSyncService
{
    public function __construct(
        protected StatFetcherInterface $fetcher,
        protected MatchSyncService $matchSyncService
    ) {}

    /**
     * Synchronizuje data ze stránky soutěže.
     */
    public function sync(Team $team, Season $season, ExternalTeamSeasonConfig $config, array $options = []): void
    {
        if (empty($config->competition_url)) {
            Log::info("Competition URL not set for team {$team->slug}, skipping competition sync.");
            return;
        }

        ConsoleService::log("- Synchronizace dat soutěže...");

        $run = ExternalImportRun::start('czbasketball', $season->id, $team->id, 'competition_page', $config->external_team_id);
        $run->updateMetadata(['url' => $config->competition_url]);

        try {
            // 1. Stažení hlavní stránky soutěže (pro tabulku pořadí)
            $html = $this->fetcher->fetch($config->competition_url, $run);

            // 1a. Extrakce tabulky pořadí (Standings)
            $standingExtractor = app(CompetitionStandingExtractor::class);
            $standingResult = $standingExtractor->extract($html);
            $standingData = $standingResult['data'];

            // 1b. Uložit kompletní tabulku pořadí
            $this->saveFullStandings($standingData->rows, $season, $config);

            // 2. Extrakce rozpisu (Schedule)
            $scheduleExtractor = app(CompetitionScheduleExtractor::class);

            // Zkusíme dotáhnout kompletní rozpis pro náš tým pomocí parametru ?t=[external_id]
            // cz.basketball díky tomu vygeneruje stránku se všemi zápasy daného týmu napříč fázemi.
            $allMatches = [];
            if ($config->external_team_id) {
                $separator = str_contains($config->competition_url, '?') ? '&' : '?';
                $teamSpecificUrl = $config->competition_url . $separator . 't=' . $config->external_team_id;

                ConsoleService::log("    - Stahuji kompletní rozpis týmu ({$teamSpecificUrl})", 'debug');
                try {
                    $teamHtml = $this->fetcher->fetch($teamSpecificUrl, $run);
                    $teamScheduleResult = $scheduleExtractor->extract($teamHtml);
                    $allMatches = $teamScheduleResult['data']->rows;
                    ConsoleService::log("    - Z týmového rozpisu získáno " . count($allMatches) . " zápasů.", 'debug');
                } catch (\Exception $e) {
                    Log::warning("Nepodařilo se stáhnout týmový rozpis soutěže: " . $e->getMessage());
                }
            }

            // Pokud nemáme zápasy z týmové URL (nebo ji nemáme), vezmeme ty z hlavní stránky
            if (empty($allMatches)) {
                $scheduleResult = $scheduleExtractor->extract($html);
                $allMatches = $scheduleResult['data']->rows;
            }

            // 3. Najdeme náš tým v tabulce pořadí pro "kontrolní součet"
            $teamNameInSource = $config->team_name_in_source ?: $team->name;
            $officialStanding = null;

            // Zkusíme zjistit suffix (A, B, C, ...) našeho týmu
            preg_match('/\b([a-gA-G])\b/', mb_strtolower($teamNameInSource), $mMy);
            $suffixMy = isset($mMy[1]) ? mb_strtolower($mMy[1]) : null;

            foreach ($standingData->rows as $row) {
                $sourceTeamName = mb_strtolower($row->values['team_name']);

                $found = false;
                if (str_contains($sourceTeamName, 'kbely')) {
                    if ($suffixMy) {
                        preg_match('/\b([a-gA-G])\b/', $sourceTeamName, $mScraped);
                        $suffixScraped = isset($mScraped[1]) ? mb_strtolower($mScraped[1]) : null;
                        if ($suffixScraped === $suffixMy) {
                            $found = true;
                        }
                    } else {
                        // Pokud nemáme suffix v názvu, tak stačí že to jsou Kbely (pro A tým)
                        if (!preg_match('/\b([a-gA-G])\b/', $sourceTeamName)) {
                            $found = true;
                        }
                    }
                } elseif (str_contains($sourceTeamName, mb_strtolower($teamNameInSource))) {
                    $found = true;
                }

                if ($found) {
                    $officialStanding = $row->values;
                    break;
                }
            }

            if ($officialStanding) {
                $metadata = $config->metadata ?? [];
                $metadata['official_standing'] = $officialStanding;
                $metadata['official_standing_synced_at'] = now()->toDateTimeString();
                $config->update(['metadata' => $metadata]);

                ConsoleService::log("    Oficiální tabulka: Z:{$officialStanding['gp']}, V:{$officialStanding['w']}, P:{$officialStanding['l']}, Pořadí:{$officialStanding['rank']}.", 'debug');
            } else {
                ConsoleService::log("    Varování: Tým '{$teamNameInSource}' nebyl nalezen v tabulce soutěže.", 'warning');
            }

            // 4. Synchronizace zápasů z rozpisu soutěže (včetně těch, které třeba nejsou v seznamu zápasů týmu)
            $importedCount = 0;
            $searchKeywords = ['kbely'];
            if ($config->team_name_in_source) {
                $searchKeywords[] = mb_strtolower($config->team_name_in_source);
            }

            // Zkusíme zjistit suffix (A, B, C, ...) našeho týmu
            preg_match('/\b([a-gA-G])\b/', mb_strtolower($team->name), $mMy);
            $suffixMy = isset($mMy[1]) ? mb_strtolower($mMy[1]) : null;

            foreach ($allMatches as $row) {
                $homeTeam = mb_strtolower($row->values['home_team']);
                $awayTeam = mb_strtolower($row->values['away_team']);

                $isOurMatch = false;
                foreach ($searchKeywords as $keyword) {
                    if (str_contains($homeTeam, $keyword) || str_contains($awayTeam, $keyword)) {
                        $isOurMatch = true;
                        break;
                    }
                }

                // Pokud to vypadá na náš tým (obsahuje Kbely), prověříme suffix
                if ($isOurMatch && $suffixMy) {
                    $matchText = str_contains($homeTeam, 'kbely') ? $homeTeam : $awayTeam;
                    preg_match('/\b([a-gA-G])\b/', $matchText, $mScraped);
                    $suffixScraped = isset($mScraped[1]) ? mb_strtolower($mScraped[1]) : null;

                    if ($suffixScraped !== $suffixMy) {
                        $isOurMatch = false;
                    }
                }

                if ($isOurMatch) {
                    $this->matchSyncService->sync($team, $season, $row->values, $run);
                    $importedCount++;
                }
            }

            ConsoleService::log("    Z rozpisu soutěže zpracováno {$importedCount} zápasů týmu.", 'debug');

            $run->finish([
                'extracted_count' => count($allMatches),
                'imported_count' => $importedCount,
            ]);

        } catch (\Exception $e) {
            $run->fail($e);
            Log::error("Chyba při synchronizaci soutěže pro tým {$team->slug}: " . $e->getMessage());
            // Při chybě soutěže nezastavujeme celou synchronizaci, jen zalogujeme
        }
    }

    /**
     * Uloží kompletní tabulku pořadí soutěže.
     */
    protected function saveFullStandings(array $rows, Season $season, ExternalTeamSeasonConfig $config): void
    {
        $competitionName = $config->competition_label ?: ($config->metadata['competition'] ?? null);

        foreach ($rows as $row) {
            CompetitionStanding::updateOrCreate(
                [
                    'season_id' => $season->id,
                    'competition_url' => $config->competition_url,
                    'team_name' => $row->values['team_name'],
                ],
                [
                    'competition_name' => $competitionName,
                    'rank' => $row->values['rank'],
                    'gp' => $row->values['gp'],
                    'w' => $row->values['w'],
                    'l' => $row->values['l'],
                    'score' => $row->values['score'],
                    'points' => $row->values['points'],
                    'metadata' => array_diff_key($row->values, array_flip(['team_name', 'rank', 'gp', 'w', 'l', 'score', 'points'])),
                ]
            );
        }
    }
}
