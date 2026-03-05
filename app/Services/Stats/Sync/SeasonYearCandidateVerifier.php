<?php

namespace App\Services\Stats\Sync;

use App\Services\Stats\Contracts\StatFetcherInterface;
use App\Services\Stats\Extractors\CzBasketball\MatchesListExtractor;
use App\Services\Stats\Extractors\CzBasketball\TeamRosterExtractor;

class SeasonYearCandidateVerifier
{
    public function __construct(
        protected StatFetcherInterface $fetcher,
        protected TeamRosterExtractor $rosterExtractor,
        protected MatchesListExtractor $matchesExtractor
    ) {}

    /**
     * Ověří, zda je kandidát y validní pro daný tým.
     */
    public function verify(int $externalTeamId, int $y): array
    {
        $evidence = [];
        $confidence = 0;

        // 1. Zkusíme soupisku (Team Page)
        $teamUrl = "https://cz.basketball/tym/{$externalTeamId}?y={$y}";
        try {
            $teamHtml = $this->fetcher->fetch($teamUrl);
            $rosterResult = $this->rosterExtractor->extract($teamHtml, [
                'external_team_id' => $externalTeamId,
                'external_season_year' => $y,
            ]);
            $rosterDto = $rosterResult['data'];

            if (count($rosterDto->rows) >= 3) {
                $confidence += 40;
                $evidence[] = 'Soupiska nalezena: '.count($rosterDto->rows).' hráčů.';
            } elseif (count($rosterDto->rows) >= 1) {
                $confidence += 20;
                $evidence[] = 'Soupiska nalezena (malá): '.count($rosterDto->rows).' hráč(ů).';
            }
        } catch (\Exception $e) {
            $evidence[] = 'Roster fetch error: '.$e->getMessage();
        }

        // 2. Zkusíme seznam zápasů (Matches List)
        // Pozor: subdoména může být smo.cz.basketball nebo cz.basketball
        $subdomains = ['smo', 'www', ''];
        foreach ($subdomains as $sub) {
            $prefix = $sub ? "{$sub}." : '';
            $matchesUrl = "https://{$prefix}cz.basketball/zapasy?c={$externalTeamId}&y={$y}";

            try {
                $matchesHtml = $this->fetcher->fetch($matchesUrl);
                $matchesResult = $this->matchesExtractor->extract($matchesHtml, [
                    'external_team_id' => $externalTeamId,
                    'external_season_year' => $y,
                ]);
                $matchesDto = $matchesResult['data'];

                if (count($matchesDto->rows) > 0) {
                    $confidence += 60;
                    $evidence[] = 'Zápasy nalezeny: '.count($matchesDto->rows)." na URL {$matchesUrl}.";
                    break; // Našli jsme validní URL
                }
            } catch (\Exception $e) {
                $evidence[] = "Matches fetch error for {$matchesUrl}: ".$e->getMessage();
            }
        }

        return [
            'isValid' => $confidence >= 40,
            'confidence' => min($confidence, 100),
            'evidence' => $evidence,
            'y' => $y,
            'matched_url' => $matchesUrl ?? null,
        ];
    }
}
