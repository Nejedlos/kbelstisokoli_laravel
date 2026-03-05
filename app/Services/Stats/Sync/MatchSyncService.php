<?php

namespace App\Services\Stats\Sync;

use App\Models\BasketballMatch;
use App\Models\ExternalImportRun;
use App\Models\Season;
use App\Models\Team;
use App\Support\MatchIdentityKey;
use Illuminate\Support\Carbon;

class MatchSyncService
{
    public function __construct(
        protected OpponentSyncService $opponentSyncService
    ) {}

    /**
     * Synchronizuje zápas.
     *
     * @param  array  $matchData  [scheduled_at, home_team, away_team, score, status, external_match_id]
     */
    public function sync(Team $team, Season $season, array $matchData, ?ExternalImportRun $run = null): BasketballMatch
    {
        $externalMatchId = $matchData['external_match_id'] ?? null;
        $scheduledAtStr = $matchData['scheduled_at'];
        $scheduledAt = $scheduledAtStr ? Carbon::parse($scheduledAtStr) : null;

        $homeTeamName = $matchData['home_team'];
        $awayTeamName = $matchData['away_team'];

        // Detekce, zda jsme domácí a kdo je soupeř
        $isHome = $this->isMyTeam($homeTeamName, $team);

        // Pokud to nevypadá, že jsme domácí, zkusíme jestli nejsme hosté
        if (! $isHome) {
            $isAway = $this->isMyTeam($awayTeamName, $team);
            if (! $isAway) {
                // Pokud nejsme ani jedno, tak jsme asi v koncích s automatickou detekcí podle jména,
                // ale budeme předpokládat, že jsme hosté, pokud homeTeam neobsahuje 'Kbely'
                $isHome = str_contains(mb_strtolower($homeTeamName), 'kbely') && ! str_contains(mb_strtolower($awayTeamName), 'kbely');
            } else {
                $isHome = false;
            }
        }

        $opponentName = $isHome ? $awayTeamName : $homeTeamName;
        $opponentExternalId = $isHome ? ($matchData['away_team_external_id'] ?? null) : ($matchData['home_team_external_id'] ?? null);

        // Upsert soupeře (včetně externích ID pokud budou k dispozici)
        $opponent = $this->opponentSyncService->sync($opponentName, null, $opponentExternalId);

        $matchIdentityKey = MatchIdentityKey::make(
            $season->id,
            $team->slug,
            $scheduledAt,
            $isHome,
            $opponentName
        );

        $match = null;

        // 1. Přednost má external_id
        if ($externalMatchId) {
            $match = BasketballMatch::where('season_id', $season->id)
                ->where('team_id', $team->id)
                ->where('metadata', 'LIKE', '%"external_id":"' . $externalMatchId . '"%')
                ->first();
        }

        // 2. Fallback na identity key
        if (! $match) {
            $match = BasketballMatch::where('season_id', $season->id)
                ->where('team_id', $team->id)
                ->where('metadata', 'LIKE', '%"match_identity_key":"' . $matchIdentityKey . '"%')
                ->first();
        }

        // Zpracování skóre
        $scoreHome = null;
        $scoreAway = null;
        if (isset($matchData['score']) && preg_match('/(\d+)\s*:\s*(\d+)/', $matchData['score'], $m)) {
            $scoreHome = (int) $m[1];
            $scoreAway = (int) $m[2];
        }

        $data = [
            'team_id' => $team->id,
            'season_id' => $season->id,
            'opponent_id' => $opponent->id,
            'scheduled_at' => $scheduledAt,
            'is_home' => $isHome,
            'status' => $matchData['status'] ?? 'planned',
            'score_home' => $scoreHome,
            'score_away' => $scoreAway,
        ];

        // Metadata
        $metadata = $match ? ($match->metadata ?? []) : [];
        $metadata['source'] = 'czbasketball';
        $metadata['match_identity_key'] = $matchIdentityKey;
        $metadata['last_synced_at'] = now()->toDateTimeString();
        if ($externalMatchId) {
            $metadata['external_id'] = $externalMatchId;
        }

        $data['metadata'] = $metadata;

        if ($match) {
            $oldValues = $match->only(['status', 'score_home', 'score_away', 'scheduled_at', 'opponent_id']);
            $match->update($data);
            $newValues = $match->only(['status', 'score_home', 'score_away', 'scheduled_at', 'opponent_id']);

            if ($run && $match->wasChanged(['status', 'score_home', 'score_away', 'scheduled_at', 'opponent_id'])) {
                $run->addLog('updated', $match, $oldValues, $newValues);
            }
        } else {
            \Log::info("Creating match for {$team->slug} vs {$opponentName}");
            $match = BasketballMatch::create($data);
            if ($run) {
                $run->addLog('created', $match, null, $match->only(['status', 'score_home', 'score_away', 'scheduled_at', 'opponent_id']));
            }
        }

        return $match;
    }

    protected function isMyTeam(string $scrapedName, Team $team): bool
    {
        $scrapedNormalized = mb_strtolower(trim($scrapedName));
        $myTeamCs = mb_strtolower(trim($team->getTranslation('name', 'cs')));
        $myTeamEn = mb_strtolower(trim($team->getTranslation('name', 'en')));

        // 1. Přesná shoda
        if ($scrapedNormalized === $myTeamCs || $scrapedNormalized === $myTeamEn) {
            return true;
        }

        // 2. Pokud obsahuje "Kbely", musíme rozlišit písmeno týmu (C, E, ...)
        if (str_contains($scrapedNormalized, 'kbely')) {
            // Pokud synchronizujeme Sokol Kbely C, tak hledáme "C" v názvu z webu
            // Většinou je to "Sokol Kbely C" nebo "Kbely C"
            preg_match('/\b([a-gA-G])\b/', $scrapedNormalized, $mScraped);
            preg_match('/\b([a-gA-G])\b/', $myTeamCs, $mMy);

            $suffixScraped = isset($mScraped[1]) ? mb_strtolower($mScraped[1]) : null;
            $suffixMy = isset($mMy[1]) ? mb_strtolower($mMy[1]) : null;

            if ($suffixScraped === $suffixMy) {
                return true;
            }
        }

        // Fallback na starý způsob, pokud vše ostatní selže
        return $scrapedNormalized === $myTeamCs;
    }
}
