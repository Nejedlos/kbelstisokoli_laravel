<?php

namespace App\Services\Stats\Sync;

use App\Models\BasketballMatch;
use App\Models\ExternalImportRun;
use App\Models\OpponentMergeSuggestion;
use App\Models\Season;
use App\Models\Team;
use App\Support\MatchIdentityKey;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class MatchSyncService
{
    public function __construct(
        protected OpponentSyncService $opponentSyncService
    ) {}

    /**
     * Normalizuje název soupeře pro fuzzy párování.
     */
    protected function normalizeOpponentName(string $name): string
    {
        // 1. Převedeme na malé písmena a odstraníme diakritiku
        $name = mb_strtolower(trim($name));
        $name = iconv('UTF-8', 'ASCII//TRANSLIT', $name);

        // 2. Odstraníme stop slova (běžné součásti názvů klubů, které nejsou unikátní)
        $stopWords = [
            'sokol', 'basket', 'praha', 'tj', 'sk', 'slavoj', 'sportovni',
            'klub', 'basketbal', 'basketball', 'bkc', 'bc', 'bk', 'bs',
            'slavia', 'sparta', 'u23', 'u19', 'u17', 'akademie', 'academy',
            'praha', 'mesto', 'ostrava', 'brno', 'plzen', 'liberec', 'olomouc',
        ];

        foreach ($stopWords as $word) {
            $name = preg_replace('/\b' . preg_quote($word, '/') . '\b/i', '', $name);
        }

        // 3. Odstraníme vše kromě písmen a čísel (včetně mezer)
        $name = preg_replace('/[^a-z0-9]/', '', $name);

        return $name;
    }

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

                // Kontrola a případná oprava sezóny na základě data zápasu
                if (!$season->containsDate($scheduledAt)) {
                    $correctSeason = Season::forDate($scheduledAt);

                    if ($correctSeason) {
                        \Log::info("MatchSync: Redirecting match from season {$season->name} to {$correctSeason->name} based on date {$scheduledAt->toDateString()}");
                        $season = $correctSeason;
                    } else {
                        \Log::warning("MatchSync: Match date {$scheduledAt->toDateString()} does not belong to provided season {$season->name}, and no suitable season was found. Proceeding anyway (may cause issues).");
                    }
                }
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
            $opponent->name
        );

        $match = null;

        // 1. Přednost má external_id (hledáme v rámci sezóny a týmu; zároveň deduplikujeme pokud je více záznamů)
        if ($externalMatchId) {
            // Pro kompatibilitu s DB bez JSON funkcí načteme zápasy týmu v sezóně a profiltrujeme v PHP
            $matchesInSeason = BasketballMatch::where('season_id', $season->id)
                ->where('team_id', $team->id)
                ->get();

            $matchesByExt = $matchesInSeason->filter(function ($m) use ($externalMatchId) {
                return ($m->metadata['external_id'] ?? null) == (string) $externalMatchId;
            })->sortBy('id');

            if ($matchesByExt->count() > 1) {
                $match = $this->mergeDuplicatesByExternalId($team, $season, (string) $externalMatchId, $run);
            } elseif ($matchesByExt->count() === 1) {
                $match = $matchesByExt->first();
            }

            // Pokud jsme stále nenašli, zkusíme najít zápas s tímto external_id KDEKOLIV v této sezóně (i pro jiný tým)
            if (! $match) {
                // Pozor: Zde by globální dotaz mohl být pomalý, ale v dané sezóně bývá jen pár set zápasů celkem.
                $globalMatch = BasketballMatch::where('season_id', $season->id)
                    ->get()
                    ->first(function ($m) use ($externalMatchId) {
                        return ($m->metadata['external_id'] ?? null) == (string) $externalMatchId;
                    });

                if ($globalMatch) {
                    \Log::info("MatchSync: Found match {$externalMatchId} under different team (ID {$globalMatch->team_id}), existing match ID: {$globalMatch->id}");

                    if ((int) $globalMatch->team_id === (int) $team->id) {
                        $match = $globalMatch;
                    }
                }
            }
        }

        // 2. Fallback na identity key (v rámci sezóny a týmu)
        if (! $match) {
            // Zápasy pro tým v sezóně už máme načtené z kroku 1, pokud bylo externalMatchId
            if (! isset($matchesInSeason)) {
                $matchesInSeason = BasketballMatch::where('season_id', $season->id)
                    ->where('team_id', $team->id)
                    ->get();
            }

            $match = $matchesInSeason->first(function ($m) use ($matchIdentityKey) {
                return ($m->metadata['match_identity_key'] ?? null) == (string) $matchIdentityKey;
            });
        }

        // 3. Poslední záchrana: vyhledání podle data, soupeře a směru zápasu (is_home)
        // Toto pomáhá identifikovat zápasy, které byly importovány z jiného zdroje (legacy)
        // a nemají v metadatech identity key ani external_id.
        // POZNÁMKA: Normalizujeme název soupeře pro lepší párování, pokud legacy název nesedí úplně přesně.
        if (! $match && $scheduledAt && $opponent) {
            $match = BasketballMatch::where('season_id', $season->id)
                ->where('team_id', $team->id)
                ->where(function ($q) use ($opponent, $opponentName) {
                    $q->where('opponent_id', $opponent->id)
                      ->orWhereHas('opponent', function ($sq) use ($opponentName) {
                          $sq->where('name', 'LIKE', '%' . $opponentName . '%');
                      });
                })
                ->where('is_home', $isHome)
                ->whereDate('scheduled_at', $scheduledAt->format('Y-m-d'))
                ->first();

            if ($match) {
                \Log::info("Match found by date/opponent/is_home fallback: {$match->id} for {$team->slug} vs {$opponentName} on {$scheduledAt->format('Y-m-d')}");
            }
        }

        // 4. NOVÉ KRITICKÉ PRAVIDLO: Tým nemůže hrát ve stejný den a čas dva zápasy.
        // Pokud existuje jakýkoliv zápas našeho týmu v tento čas (tolerance 120 min), je to on, bez ohledu na jméno soupeře.
        // DŮLEŽITÉ: Omezujeme na sezónu, abychom neaktualizovali staré zápasy z jiných sezón, které mají náhodou podobný čas.
        if (! $match && $scheduledAt) {
            $match = BasketballMatch::where('team_id', $team->id)
                ->where('season_id', $season->id)
                ->where('scheduled_at', '>=', $scheduledAt->copy()->subMinutes(120)->toDateTimeString())
                ->where('scheduled_at', '<=', $scheduledAt->copy()->addMinutes(120)->toDateTimeString())
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
                        // 2. Normalizovaná shoda (propojení "CTS Praha" vs "CTS Basket")
                        $normA = $this->normalizeOpponentName($opponentName);
                        $normB = $this->normalizeOpponentName($potentialOpponentName);
                        if ($normA !== '' && $normA === $normB) {
                            $sim = 95; // Velmi vysoká podobnost pokud sedí normalizované jméno
                        } else {
                            // 3. Levenshtein
                            $lev = levenshtein($nameA, $nameB);
                            $maxLen = max(strlen($nameA), strlen($nameB));
                            $sim = $maxLen > 0 ? (1 - ($lev / $maxLen)) * 100 : 0;
                        }
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

        // Zpracování statusu
        $status = $matchData['status'] ?? 'planned';

        // Zpracování skóre
        $scoreHome = $match ? $match->score_home : null;
        $scoreAway = $match ? $match->score_away : null;

        if (isset($matchData['score']) && preg_match('/(\d+)\s*:\s*(\d+)/', $matchData['score'], $m)) {
            $canHaveScore = ! in_array($status, ['planned', 'scheduled']);

            // Pokud je zápas v minulosti, může mít skóre i když je status planned (dočasně při synchronizaci)
            if ($scheduledAt && $scheduledAt->isPast()) {
                $canHaveScore = true;
            }

            if ($canHaveScore) {
                $scoreHome = (int) $m[1];
                $scoreAway = (int) $m[2];
            }
        }

        // Pokud je zápas již označen jako odehraný v DB a teď přišel status planned, ponecháme finished
        // (ochrana proti dočasným výpadkům výsledků v seznamu na webu)
        if ($match && in_array($match->status, ['finished', 'played', 'completed']) && in_array($status, ['planned', 'scheduled'])) {
            $status = $match->status === 'played' ? 'finished' : $match->status;

            // Pokud máme skóre v DB, ale teď přišlo prázdné, ponecháme to v DB (další úroveň ochrany k řádkům 284-285)
            if ($scoreHome === null && $match->score_home !== null) {
                $scoreHome = $match->score_home;
                $scoreAway = $match->score_away;
                \Log::info("MatchSync: Preserving existing score {$scoreHome}:{$scoreAway} for finished match ID {$match->id} because source provided no score in list.");
            }
        }

        // Finální určení statusu na základě skóre
        if (($scoreHome !== null && $scoreAway !== null) || in_array($status, ['played', 'completed'])) {
            $status = 'finished';
        }

        // Pokud je zápas v minulosti (před více než 2 hodinami od začátku), měl by být označen jako odehraný (finished), i když nemá skóre.
        // Důležité: kontrolujeme, zda je v minulosti i s rezervou, abychom nepřepínali právě probíhající zápasy.
        if (in_array($status, ['planned', 'scheduled']) && $scheduledAt && $scheduledAt->copy()->addHours(3)->isPast()) {
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

        // Finální deduplikační průchod pro jistotu (sloučí záznamy podle data/soupeře)
        $match = $this->mergeDuplicatesForMatch($match, $run);

        return $match;
    }

    /**
     * Sloučí duplicitní zápasy se stejným external_id v rámci daného týmu a sezóny.
     */
    public function mergeDuplicatesByExternalId(Team $team, Season $season, string $externalId, ?ExternalImportRun $run = null): ?BasketballMatch
    {
        $dups = BasketballMatch::where('season_id', $season->id)
            ->where('team_id', $team->id)
            ->get()
            ->filter(function ($m) use ($externalId) {
                return ($m->metadata['external_id'] ?? null) == (string) $externalId;
            })
            ->sortBy('id');

        if ($dups->count() <= 1) {
            return $dups->first();
        }

        // Vybereme primární záznam: preferujeme ten se skóre nebo finished/statistikami
        $primary = $dups->first(function (BasketballMatch $m) {
            return ($m->score_home !== null && $m->score_away !== null)
                || ($m->status === 'finished')
                || (!empty($m->metadata['boxscore_synced_at'] ?? null));
        }) ?: $dups->first();

        foreach ($dups as $m) {
            if ($m->id === $primary->id) {
                continue;
            }

            // Přesunout pivoty týmů
            $teamIds = $m->teams()->pluck('teams.id')->all();
            if (!empty($teamIds)) {
                $primary->teams()->syncWithoutDetaching($teamIds);
            }
            DB::table('basketball_match_team')->where('basketball_match_id', $m->id)->delete();

            // Přesunout statistic_rows
            DB::table('statistic_rows')->where('basketball_match_id', $m->id)
                ->update(['basketball_match_id' => $primary->id]);

            // Přesunout external_player_matches
            DB::table('external_player_matches')->where('basketball_match_id', $m->id)
                ->update(['basketball_match_id' => $primary->id]);

            // Přesunout skóre a status pokud na primárním chybí
            if ($primary->score_home === null && $m->score_home !== null) {
                $primary->score_home = $m->score_home;
                $primary->score_away = $m->score_away;
            }

            if ($primary->status !== 'finished' && $m->status === 'finished') {
                $primary->status = 'finished';
            }

            // Přesunout attendances (polymorfní) s deduplikací podle user_id
            $attendanceRows = DB::table('attendances')
                ->where('attendable_type', BasketballMatch::class)
                ->where('attendable_id', $m->id)
                ->get();

            foreach ($attendanceRows as $row) {
                $exists = DB::table('attendances')
                    ->where('attendable_type', BasketballMatch::class)
                    ->where('attendable_id', $primary->id)
                    ->where('user_id', $row->user_id)
                    ->exists();

                if ($exists) {
                    // Pokud existuje konflikt, smažeme duplicitní záznam z původního zápasu
                    DB::table('attendances')->where('id', $row->id)->delete();
                } else {
                    DB::table('attendances')->where('id', $row->id)->update(['attendable_id' => $primary->id]);
                }
            }

            // Sloučit metadata (primární má přednost)
            $metaPrimary = $primary->metadata ?? [];
            $metaOther = $m->metadata ?? [];
            $primary->metadata = array_replace_recursive($metaOther, $metaPrimary);

            // Sjednotit další pole pokud chybí na primárním
            if (!$primary->scheduled_at && $m->scheduled_at) {
                $primary->scheduled_at = $m->scheduled_at;
            }
            if ($primary->opponent_id === null && $m->opponent_id) {
                $primary->opponent_id = $m->opponent_id;
            }
            if ($primary->venue_id === null && $m->venue_id) {
                $primary->venue_id = $m->venue_id;
            }

            if ($run) {
                $run->addLog('merged_duplicate', $primary, ['merged_id' => $m->id], null);
            }

            $m->delete();
        }

        $primary->save();

        return $primary->fresh();
    }

    /**
     * Sloučí potenciální duplicitní záznamy podle data a soupeře do primárního zápasu.
     */
    protected function mergeDuplicatesForMatch(BasketballMatch $primary, ?ExternalImportRun $run = null): BasketballMatch
    {
        if (!$primary) {
            return $primary;
        }

        $query = BasketballMatch::where('season_id', $primary->season_id)
            ->where('team_id', $primary->team_id)
            ->where('id', '!=', $primary->id);

        if ($primary->scheduled_at) {
            $query->whereDate('scheduled_at', $primary->scheduled_at->toDateString());
        }

        if ($primary->opponent_id) {
            $query->where('opponent_id', $primary->opponent_id);
        }

        $candidates = $query->get();

        foreach ($candidates as $m) {
            // Bezpečná merge: slučujeme, pokud druhý záznam nemá jiné external_id než primary
            $primaryExt = $primary->metadata['external_id'] ?? null;
            $otherExt = $m->metadata['external_id'] ?? null;
            $isSafe = $otherExt === null || $otherExt === $primaryExt;
            if (! $isSafe) {
                continue;
            }

            // Přesunout pivoty a data
            $teamIds = $m->teams()->pluck('teams.id')->all();
            if (!empty($teamIds)) {
                $primary->teams()->syncWithoutDetaching($teamIds);
            }
            DB::table('basketball_match_team')->where('basketball_match_id', $m->id)->delete();

            DB::table('statistic_rows')->where('basketball_match_id', $m->id)
                ->update(['basketball_match_id' => $primary->id]);

            DB::table('external_player_matches')->where('basketball_match_id', $m->id)
                ->update(['basketball_match_id' => $primary->id]);

            // Přesunout attendances (polymorfní) s deduplikací podle user_id
            $attendanceRows = DB::table('attendances')
                ->where('attendable_type', BasketballMatch::class)
                ->where('attendable_id', $m->id)
                ->get();

            foreach ($attendanceRows as $row) {
                $exists = DB::table('attendances')
                    ->where('attendable_type', BasketballMatch::class)
                    ->where('attendable_id', $primary->id)
                    ->where('user_id', $row->user_id)
                    ->exists();

                if ($exists) {
                    DB::table('attendances')->where('id', $row->id)->delete();
                } else {
                    DB::table('attendances')->where('id', $row->id)->update(['attendable_id' => $primary->id]);
                }
            }

            $metaPrimary = $primary->metadata ?? [];
            $metaOther = $m->metadata ?? [];
            $primary->metadata = array_replace_recursive($metaOther, $metaPrimary);

            // Přesunout skóre a status pokud na primárním chybí
            if ($primary->score_home === null && $m->score_home !== null) {
                $primary->score_home = $m->score_home;
                $primary->score_away = $m->score_away;
                \Log::info("MatchSync (merge_candidate): Transferred score {$m->score_home}:{$m->score_away} from duplicate match ID {$m->id} to primary ID {$primary->id}");
            }

            if (!in_array($primary->status, ['finished', 'played', 'completed']) && in_array($m->status, ['finished', 'played', 'completed'])) {
                $primary->status = 'finished';
                \Log::info("MatchSync (merge_candidate): Transferred status 'finished' from duplicate match ID {$m->id} to primary ID {$primary->id}");
            }

            // Sjednotit další pole pokud chybí na primárním
            if (!$primary->scheduled_at && $m->scheduled_at) {
                $primary->scheduled_at = $m->scheduled_at;
            }
            if ($primary->opponent_id === null && $m->opponent_id) {
                $primary->opponent_id = $m->opponent_id;
            }

            if ($run) {
                $run->addLog('merged_candidate', $primary, ['merged_id' => $m->id], null);
            }

            $m->delete();
        }

        $primary->save();

        return $primary->fresh();
    }

    /**
     * Ověří konzistenci lokálních dat s oficiální tabulkou ze zdroje.
     */
    public function validateSeasonConsistency(Team $team, Season $season, \App\Models\ExternalTeamSeasonConfig $config): array
    {
        $official = $config->metadata['official_standing'] ?? null;
        if (! $official) {
            return ['status' => 'unknown', 'message' => 'Oficiální tabulka není k dispozici.'];
        }

        $myStats = BasketballMatch::where('team_id', $team->id)
            ->where('season_id', $season->id)
            ->where('status', 'finished')
            ->selectRaw('COUNT(*) as gp,
                         SUM(CASE WHEN (is_home = 1 AND score_home > score_away) OR (is_home = 0 AND score_away > score_home) THEN 1 ELSE 0 END) as w,
                         SUM(CASE WHEN (is_home = 1 AND score_home < score_away) OR (is_home = 0 AND score_away < score_home) THEN 1 ELSE 0 END) as l')
            ->first();

        // Přetypování na int, protože DB může vrátit string
        $localGp = (int) $myStats->gp;
        $localW = (int) $myStats->w;
        $localL = (int) $myStats->l;

        $gpMatch = ($localGp == $official['gp']);
        $wMatch = ($localW == $official['w']);
        $lMatch = ($localL == $official['l']);

        $isConsistent = $gpMatch && $wMatch && $lMatch;

        $metadata = $config->metadata ?? [];
        $metadata['consistency'] = [
            'is_consistent' => $isConsistent,
            'last_check_at' => now()->toDateTimeString(),
            'local' => ['gp' => $localGp, 'w' => $localW, 'l' => $localL],
            'official' => ['gp' => (int) $official['gp'], 'w' => (int) $official['w'], 'l' => (int) $official['l']],
        ];
        $config->update(['metadata' => $metadata]);

        if (! $isConsistent) {
            \Log::warning("Nekonzistence dat pro tým {$team->slug} v sezóně {$season->name}. Lokální: Z:{$localGp}, V:{$localW}, P:{$localL}. Oficiální: Z:{$official['gp']}, V:{$official['w']}, P:{$official['l']}.");

            return ['status' => 'inconsistent', 'details' => $metadata['consistency']];
        }

        return ['status' => 'consistent'];
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
