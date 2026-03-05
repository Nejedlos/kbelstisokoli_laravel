<?php

namespace App\Services\Stats\Sync;

use App\Models\BasketballMatch;
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
    public function sync(Team $team, Season $season, array $matchData): BasketballMatch
    {
        $externalMatchId = $matchData['external_match_id'] ?? null;
        $scheduledAtStr = $matchData['scheduled_at'];
        $scheduledAt = $scheduledAtStr ? Carbon::parse($scheduledAtStr) : null;

        $homeTeamName = $matchData['home_team'];
        $awayTeamName = $matchData['away_team'];

        // Detekce soupeře a zda jsme doma
        // Poznámka: v realitě cz.basketball může být název týmu mírně odlišný od našeho interního jména,
        // ale pro potřeby identity key a hledání soupeře musíme být konzistentní.
        // Předpokládáme, že pokud tým v datech NENÍ náš tým, je to soupeř.
        // Pro lepší robustnost můžeme použít config['my_team_names']

        $isHome = $this->isMyTeam($homeTeamName, $team);
        $opponentName = $isHome ? $awayTeamName : $homeTeamName;

        $matchIdentityKey = MatchIdentityKey::make(
            $season->id,
            $team->slug,
            $scheduledAt,
            $isHome,
            $opponentName
        );

        $match = BasketballMatch::where('season_id', $season->id)
            ->where('team_id', $team->id)
            ->where('metadata->match_identity_key', $matchIdentityKey)
            ->first();

        // Pokud jsme nenašli podle identity key, zkusíme podle external_id (pokud ho máme)
        if (! $match && $externalMatchId) {
            $match = BasketballMatch::where('season_id', $season->id)
                ->where('metadata->external_id', $externalMatchId)
                ->first();
        }

        // Upsert soupeře
        $opponent = $this->opponentSyncService->sync($opponentName);

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
            $match->update($data);
        } else {
            \Log::info("Creating match for {$team->slug} vs {$opponentName}");
            $match = BasketballMatch::create($data);
        }

        return $match;
    }

    protected function isMyTeam(string $scrapedName, Team $team): bool
    {
        // Velmi jednoduchá heuristika: pokud obsahuje "Kbely" a náš tým taky
        // Lepší by bylo mít mapování názvů v external_team_mappings.metadata
        $scrapedNormalized = mb_strtolower($scrapedName);
        $myTeamNormalized = mb_strtolower($team->getTranslation('name', 'cs'));

        if (str_contains($scrapedNormalized, 'kbely')) {
            return true;
        }

        return $scrapedNormalized === $myTeamNormalized;
    }
}
