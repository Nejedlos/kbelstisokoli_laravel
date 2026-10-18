<?php

namespace App\Services\Stats\Sync;

use App\Models\BasketballMatch;
use App\Models\ExternalImportRun;
use App\Models\ExternalTeamSeasonConfig;
use App\Models\Season;
use App\Models\Team;
use App\Services\Matches\MatchWriteService;
use App\Support\MatchIdentityKey;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class MatchSyncService
{
    public function __construct(
        protected OpponentSyncService $opponentSyncService,
        protected MatchWriteService $matchWriteService
    ) {}

    /**
     * Synchronizuje zápas.
     *
     * @param  array  $matchData  [scheduled_at, home_team, away_team, score, status, external_match_id]
     */
    public function sync(Team $team, Season $season, array $matchData, ?ExternalImportRun $run = null, array $options = []): BasketballMatch
    {
        return $this->matchWriteService->withImportTeamLocks($team->id, function () use ($team, $season, $matchData, $run, $options) {
            return $this->syncLocked($team, $season, $matchData, $run, $options);
        });
    }

    private function syncLocked(Team $team, Season $season, array $matchData, ?ExternalImportRun $run, array $options): BasketballMatch
    {
        if ($options['conservative_matching'] ?? false) {
            if (empty($matchData['external_match_id']) || empty($matchData['scheduled_at']) || ! $season->containsDate(Carbon::parse($matchData['scheduled_at']))) {
                throw new \RuntimeException('Conservative match sync requires an external ID and a date in the configured season.');
            }
            $config = ExternalTeamSeasonConfig::where('team_id', $team->id)->where('season_id', $season->id)->first();
            $externalTeamId = (string) ($config?->external_team_id ?? '');
            $sourceName = mb_strtolower(trim($config?->team_name_in_source ?? ''));
            $home = $matchData['home_team'] ?? '';
            $away = $matchData['away_team'] ?? '';
            $homeId = (string) ($matchData['home_team_external_id'] ?? '');
            $awayId = (string) ($matchData['away_team_external_id'] ?? '');
            if ($externalTeamId !== '' && ($homeId !== '' || $awayId !== '')) {
                $homeMatches = $homeId === $externalTeamId;
                $awayMatches = $awayId === $externalTeamId;
            } else {
                $homeMatches = $this->isMyTeam($home, $team) || ($sourceName !== '' && mb_strtolower(trim($home)) === $sourceName);
                $awayMatches = $this->isMyTeam($away, $team) || ($sourceName !== '' && mb_strtolower(trim($away)) === $sourceName);
            }
            if ($homeMatches === $awayMatches) {
                throw new \RuntimeException('Source match does not identify exactly one configured team.');
            }
            $sourceIsHome = $homeMatches;
        }

        $externalMatchId = $matchData['external_match_id'] ?? null;
        $scheduledAtStr = $matchData['scheduled_at'] ?? null;
        $scheduledAt = null;
        if ($scheduledAtStr) {
            try {
                $scheduledAt = Carbon::parse($scheduledAtStr);

                // Kontrola a případná oprava sezóny na základě data zápasu
                if (! $season->containsDate($scheduledAt)) {
                    $correctSeason = Season::forDate($scheduledAt);

                    if ($correctSeason) {
                        \Log::info("MatchSync: Redirecting match from season {$season->name} to {$correctSeason->name} based on date {$scheduledAt->toDateString()}");
                        $season = $correctSeason;
                    } else {
                        \Log::warning("MatchSync: Match date {$scheduledAt->toDateString()} does not belong to provided season {$season->name}, and no suitable season was found. Proceeding anyway (may cause issues).");
                    }
                }
            } catch (\Exception $e) {
                \Log::warning("Failed to parse scheduled_at: {$scheduledAtStr} for match ".($matchData['external_match_id'] ?? 'unknown'));
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

        $isHome = $sourceIsHome ?? $isHome;

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

        if (! $scheduledAt) {
            throw new \RuntimeException('Match sync requires an explicit valid date; undated fixtures cannot be safely identified.');
        }
        $match = $this->matchWriteService->resolveImported($team->id, $season->id, $externalMatchId === null ? null : (string) $externalMatchId, $scheduledAt, $isHome, $opponent->id);

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
            'match_type' => $match ? $match->match_type : 'mistrovske',
            'team_id' => $match ? $match->team_id : $team->id,
            'season_id' => $season->id,
            'opponent_id' => $match ? $match->opponent_id : $opponent->id,
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
                || (! empty($m->metadata['boxscore_synced_at'] ?? null));
        }) ?: $dups->first();

        foreach ($dups as $m) {
            if ($m->id === $primary->id) {
                continue;
            }

            // Přesunout pivoty týmů
            $teamIds = $m->teams()->pluck('teams.id')->all();
            if (! empty($teamIds)) {
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
            if (! $primary->scheduled_at && $m->scheduled_at) {
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
        if (! $primary) {
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
            if (! empty($teamIds)) {
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

            if (! in_array($primary->status, ['finished', 'played', 'completed']) && in_array($m->status, ['finished', 'played', 'completed'])) {
                $primary->status = 'finished';
                \Log::info("MatchSync (merge_candidate): Transferred status 'finished' from duplicate match ID {$m->id} to primary ID {$primary->id}");
            }

            // Sjednotit další pole pokud chybí na primárním
            if (! $primary->scheduled_at && $m->scheduled_at) {
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
    public function validateSeasonConsistency(Team $team, Season $season, ExternalTeamSeasonConfig $config): array
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
