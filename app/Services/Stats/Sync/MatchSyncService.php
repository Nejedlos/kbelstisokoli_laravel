<?php

namespace App\Services\Stats\Sync;

use App\Models\BasketballMatch;
use App\Models\ExternalImportRun;
use App\Models\OpponentMergeSuggestion;
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
        $scheduledAtStr = $matchData['scheduled_at'] ?? null;
        $scheduledAt = null;
        if ($scheduledAtStr) {
            try {
                $scheduledAt = Carbon::parse($scheduledAtStr);
            } catch (\Exception $e) {
                \Log::warning("Failed to parse scheduled_at: {$scheduledAtStr} for match " . ($matchData['external_match_id'] ?? 'unknown'));
            }
        }

        $homeTeamName = $matchData['home_team'] ?? 'Unknown';
        $awayTeamName = $matchData['away_team'] ?? 'Unknown';

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

        // 1. Přednost má external_id (hledáme v rámci sezóny napříč týmy pro případ overlapu)
        if ($externalMatchId) {
            $match = BasketballMatch::where('season_id', $season->id)
                ->where('metadata', 'LIKE', '%"external_id":"' . $externalMatchId . '"%')
                ->first();
        }

        // 2. Fallback na identity key (v rámci sezóny a týmu)
        if (! $match) {
            $match = BasketballMatch::where('season_id', $season->id)
                ->where('team_id', $team->id)
                ->where('metadata', 'LIKE', '%"match_identity_key":"' . $matchIdentityKey . '"%')
                ->first();
        }

        // 3. Poslední záchrana: vyhledání podle data, soupeře a směru zápasu (is_home)
        // Toto pomáhá identifikovat zápasy, které byly importovány z jiného zdroje (legacy)
        // a nemají v metadatech identity key ani external_id.
        if (! $match && $scheduledAt && $opponent) {
            $match = BasketballMatch::where('season_id', $season->id)
                ->where('team_id', $team->id)
                ->where('opponent_id', $opponent->id)
                ->where('is_home', $isHome)
                ->whereDate('scheduled_at', $scheduledAt->format('Y-m-d'))
                ->first();

            if ($match) {
                \Log::info("Match found by date/opponent/is_home fallback: {$match->id} for {$team->slug} vs {$opponentName} on {$scheduledAt->format('Y-m-d')}");
            }
        }

        // 4. NOVÉ KRITICKÉ PRAVIDLO: Tým nemůže hrát ve stejný den a čas dva zápasy.
        // Pokud existuje jakýkoliv zápas našeho týmu v tento čas (tolerance 120 min), je to on, bez ohledu na jméno soupeře.
        if (! $match && $scheduledAt) {
            $match = BasketballMatch::where('team_id', $team->id)
                ->where('scheduled_at', '>=', $scheduledAt->copy()->subMinutes(120))
                ->where('scheduled_at', '<=', $scheduledAt->copy()->addMinutes(120))
                ->first();

            if ($match) {
                \Log::info("Match matched by team and close time (New rule): ID {$match->id}, diff: " . abs($match->scheduled_at->diffInMinutes($scheduledAt)) . " min, old opponent: " . ($match->opponent->name ?? 'None') . ", new opponent: {$opponentName}");
            }
        }

        // 5. Detekce duplicit pro ručně vytvořené zápasy s podobným názvem soupeře (pokud nebyl nalezen přesný)
        if (! $match && $scheduledAt) {
            $potentialMatches = BasketballMatch::where('season_id', $season->id)
                ->where('team_id', $team->id)
                ->where('is_home', $isHome)
                ->whereDate('scheduled_at', $scheduledAt->format('Y-m-d'))
                ->with('opponent')
                ->get();

            foreach ($potentialMatches as $potential) {
                // Pokud už má external_id jinde, tak to pravděpodobně není on
                if (! empty($potential->metadata['external_id']) && ($potential->metadata['external_id'] != $externalMatchId)) {
                    continue;
                }

                $potentialOpponentName = $potential->opponent?->name;
                if ($potentialOpponentName) {
                    $nameA = mb_strtolower(trim($opponentName));
                    $nameB = mb_strtolower(trim($potentialOpponentName));

                    // 1. Přesná shoda po ořezání
                    if ($nameA === $nameB) {
                        $sim = 100;
                    } else {
                        // 2. Levenshtein
                        $lev = levenshtein($nameA, $nameB);
                        $maxLen = max(strlen($nameA), strlen($nameB));
                        $sim = $maxLen > 0 ? (1 - ($lev / $maxLen)) * 100 : 0;
                    }

                    $isMatch = ($sim > 70);

                    // 3. Robustnější kontrola pro podřetězce (např. "TJ ČSA" vs "TJ ČSA Praha")
                    if (! $isMatch && (strlen($nameA) > 3 && strlen($nameB) > 3)) {
                        if (str_contains($nameA, $nameB) || str_contains($nameB, $nameA)) {
                            // Pokud jeden obsahuje druhý, je to pravděpodobně shoda, pokud se neliší v suffixu týmu (A/B/C)
                            $isMatch = true;

                            // Ale pozor na suffixy (pokud má jeden B a druhý C, tak to není stejný tým)
                            preg_match('/\b([a-g])\b$/', $nameA, $suffixA);
                            preg_match('/\b([a-g])\b$/', $nameB, $suffixB);
                            if (isset($suffixA[1]) && isset($suffixB[1]) && $suffixA[1] !== $suffixB[1]) {
                                $isMatch = false;
                            }
                        }
                    }

                    if ($isMatch) {
                        // Kontrola zamítnutých merge návrhů mezi soupeři
                        if ($opponent->id != $potential->opponent_id) {
                            $isRejected = OpponentMergeSuggestion::where('status', 'rejected')
                                ->where(function ($query) use ($opponent, $potential) {
                                    $query->where(function ($q) use ($opponent, $potential) {
                                        $q->where('source_opponent_id', $opponent->id)
                                            ->where('target_opponent_id', $potential->opponent_id);
                                    })->orWhere(function ($q) use ($opponent, $potential) {
                                        $q->where('source_opponent_id', $potential->opponent_id)
                                            ->where('target_opponent_id', $opponent->id);
                                    });
                                })->exists();

                            if ($isRejected) {
                                \Log::info("Match fuzzy-match skipped: Opponent merge was previously rejected by user between {$opponent->id} and {$potential->opponent_id}");
                                continue;
                            }
                        }

                        $match = $potential;
                        \Log::info("Match detected as potential duplicate by similar opponent name: {$match->id} (Sim: {$sim}%, {$potentialOpponentName} vs {$opponentName})");
                        break;
                    }
                }
            }
        }

        // Zpracování skóre
        $scoreHome = null;
        $scoreAway = null;
        if (isset($matchData['score']) && preg_match('/(\d+)\s*:\s*(\d+)/', $matchData['score'], $m)) {
            $scoreHome = (int) $m[1];
            $scoreAway = (int) $m[2];
        }

        // Zpracování statusu
        $status = $matchData['status'] ?? 'planned';
        if (($scoreHome !== null && $scoreAway !== null) || $status === 'played' || $status === 'completed') {
            $status = 'finished';
        }

        // Pokud je zápas v minulosti, měl by být označen jako odehraný (finished), i když nemá skóre
        if ($status === 'planned' && $scheduledAt && $scheduledAt->isPast()) {
            $status = 'finished';
        }

        $data = [
            'match_type' => 'mistrovske',
            'team_id' => $team->id,
            'season_id' => $season->id,
            'opponent_id' => $opponent->id,
            'scheduled_at' => $scheduledAt,
            'is_home' => $isHome,
            'status' => $status,
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
            $metadata['season_external_match_id'] = $externalMatchId;
        }

        $data['metadata'] = $metadata;

        if ($match) {
            $oldValues = $match->only(['status', 'score_home', 'score_away', 'scheduled_at', 'opponent_id']);
            $match->update($data);
            $newValues = $match->only(['status', 'score_home', 'score_away', 'scheduled_at', 'opponent_id']);

            // Sync teams to pivot table
            if (! $match->teams->contains($team->id)) {
                $match->teams()->syncWithoutDetaching([$team->id]);
            }

            if ($run && $match->wasChanged(['status', 'score_home', 'score_away', 'scheduled_at', 'opponent_id'])) {
                $run->addLog('updated', $match, $oldValues, $newValues);
            }
        } else {
            \Log::info("Creating match for {$team->slug} vs {$opponentName}");
            $match = BasketballMatch::create($data);

            // Sync teams to pivot table
            $match->teams()->sync([$team->id]);

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
