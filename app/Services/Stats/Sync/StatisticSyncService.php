<?php

namespace App\Services\Stats\Sync;

use App\Models\BasketballMatch;
use App\Models\ExternalEntityMapping;
use App\Models\ExternalImportRun;
use App\Models\StatisticRow;
use App\Models\StatisticSet;
use App\Models\User;
use App\Services\Stats\DTO\NormalizedTableDTO;
use App\Services\Support\ConsoleService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StatisticSyncService
{
    public function __construct(
        protected StatisticSetService $statisticSetService
    ) {}

    /**
     * Uloží jeden řádek statistiky (vhodné pro legacy import nebo manuální vklad).
     */
    public function saveRow(StatisticSet $set, \App\Services\Stats\DTO\NormalizedRowDTO $row, array $context = [], ?ExternalImportRun $run = null): StatisticRow
    {
        $playerId = $context['player_id'] ?? null;
        $matchId = $context['basketball_match_id'] ?? null;
        $teamId = $context['team_id'] ?? null;
        $seasonId = $context['season_id'] ?? null;

        $contentHash = $context['source_metadata']['content_hash'] ?? null;

        $attributes = [
            'statistic_set_id' => $set->id,
            'basketball_match_id' => $matchId,
            'player_id' => $playerId,
            'row_label' => $playerId ? null : $row->rowLabel,
            'season_id' => $seasonId,
            'team_id' => $teamId,
        ];

        $values = [
            'values' => $row->values,
            'source_metadata' => array_merge(
                $row->metadata ?? [],
                $context['source_metadata'] ?? []
            ),
        ];

        $query = StatisticRow::where($attributes);

        $statRow = $query->get()->first(function ($r) use ($contentHash) {
            if (! $contentHash) {
                return true;
            }

            return ($r->source_metadata['content_hash'] ?? null) == (string) $contentHash;
        });

        if ($statRow) {
            $oldValues = $statRow->only(['values']);
            $statRow->update($values);
            if ($run && $statRow->wasChanged('values')) {
                $run->addLog('updated', $statRow, $oldValues, $statRow->only(['values']), "Updated stats for " . ($playerId ? "player ID $playerId" : $row->rowLabel));
            }
        } else {
            $statRow = StatisticRow::create(array_merge($attributes, $values));
            if ($run) {
                $run->addLog('created', $statRow, null, $statRow->only(['values']), "Created stats for " . ($playerId ? "player ID $playerId" : $row->rowLabel));
            }
        }

        return $statRow;
    }

    /**
     * Smaže boxscore statistik pro konkrétní zápas.
     */
    public function clearMatchBoxscore(BasketballMatch $match, ?ExternalImportRun $run = null): void
    {
        $this->statisticSetService->ensureBaseSets();
        $set = StatisticSet::where('slug', StatisticSetService::MATCH_BOXSCORE_SET)->first();

        if (! $set) {
            return;
        }

        // PŘIDÁNO: Používáme retry(5) kvůli chybě 1615 (Prepared statement needs to be re-prepared), která se objevuje na produkci.
        $count = retry(5, function () use ($set, $match) {
            return StatisticRow::where('statistic_set_id', $set->id)
                ->where('basketball_match_id', $match->id)
                ->delete();
        }, 200, function ($e) {
            Log::warning("clearMatchBoxscore retry due to error 1615 (or other): " . $e->getMessage());
            return $e instanceof \Illuminate\Database\QueryException;
        });

        if ($run && $count > 0) {
            $run->addLog('deleted', null, null, null, "FRESH mode: Smazáno $count existujících řádků statistik pro zápas.");
        }
    }

    /**
     * Synchronizuje statistiky z boxscoru zápasu.
     */
    public function syncMatchBoxscore(BasketballMatch $match, NormalizedTableDTO $data, ?ExternalImportRun $run = null): void
    {
        $this->statisticSetService->ensureBaseSets();
        $set = StatisticSet::where('slug', StatisticSetService::MATCH_BOXSCORE_SET)->first();

        if (! $set) {
            throw new \Exception('Statistic set for boxscore not found.');
        }

        // Vypnutí query logu pro úsporu paměti při hromadných operacích
        DB::connection()->disableQueryLog();

        // PŘIDÁNO: Menší transakce pro metadata
        DB::transaction(function () use ($match, $data) {
            $isOurTeam = true;
            $currentTeamId = $match->team_id;

            // Znovu načteme aktuální metadata z DB pro jistotu (pro atomicitu v transakci)
            $freshMatch = DB::table('matches')->where('id', $match->id)->lockForUpdate()->first();
            $metaRaw = $freshMatch->metadata;
            $matchMetadata = is_array($metaRaw) ? $metaRaw : (json_decode($metaRaw ?? '[]', true) ?: []);
            $metaChanged = false;

            if (isset($data->metadata['header'])) {
                $matchMetadata['match_header'] = $data->metadata['header'];
                $metaChanged = true;
            }
            if (isset($data->metadata['best_players'])) {
                $matchMetadata['best_players'] = $data->metadata['best_players'];
                $metaChanged = true;
            }
            if (isset($data->metadata['team_comparison'])) {
                $matchMetadata['team_comparison'] = $data->metadata['team_comparison'];
                $metaChanged = true;
            }

            if ($metaChanged) {
                DB::table('matches')->where('id', $match->id)->update([
                    'metadata' => json_encode($matchMetadata)
                ]);
            }
        });

        $isOurTeam = true;
        // Znovu detekujeme, zda je to náš tým (pro logiku řádků)
        $teamNameValue = $match->team->getTranslation('name', 'cs') ?: ($match->team->name ?: '');
        if (is_array($teamNameValue)) {
            $teamNameValue = $teamNameValue['cs'] ?? ($teamNameValue['en'] ?? (reset($teamNameValue) ?: ''));
        }

        $possibleOurNames = [
            $this->normalizeForComparison($teamNameValue),
        ];

        $externalName = \App\Models\ExternalTeamSeasonConfig::where('team_id', $match->team_id)
            ->where('season_id', $match->season_id)
            ->value('team_name_in_source');

        if ($externalName) {
            $possibleOurNames[] = $this->normalizeForComparison($externalName);
        }

        $tableTeamName = $this->normalizeForComparison($data->name);
        $matched = false;
        foreach ($possibleOurNames as $ourName) {
            if (str_contains($tableTeamName, $ourName) || str_contains($ourName, $tableTeamName)) {
                $matched = true;
                break;
            }
        }

        if (! $matched) {
            if (! str_contains(mb_strtolower($data->name), mb_strtolower($teamNameValue)) &&
                (! $externalName || ! str_contains(mb_strtolower($data->name), mb_strtolower($externalName)))) {
                $isOurTeam = false;
            }
        }

        // Pokud je to soupeř, uložíme ho do metadat
        if (! $isOurTeam) {
            DB::transaction(function () use ($match, $data) {
                $freshMatch = DB::table('matches')->where('id', $match->id)->lockForUpdate()->first();
                $metaRaw = $freshMatch->metadata;
                $matchMetadata = is_array($metaRaw) ? $metaRaw : (json_decode($metaRaw ?? '[]', true) ?: []);
                $matchMetadata['opponent_boxscore'] = $data->toArray();

                DB::table('matches')->where('id', $match->id)->update([
                    'metadata' => json_encode($matchMetadata)
                ]);
            });
            return;
        }

        // Pro náš tým synchronizujeme řádky - každý řádek v samostatné transakci pro odolnost
        foreach ($data->rows as $row) {
            DB::transaction(function () use ($match, $row, $set, $run, $isOurTeam) {
                $externalPlayerId = $row->metadata['external_player_id'] ?? $row->playerId;
                $playerName = $row->rowLabel;

                // Párování hráče
                $playerId = $this->findInternalPlayerId($externalPlayerId, $match->season_id, 'czbasketball');

                // Ghost hráč creation logic
                if (! $playerId && $externalPlayerId && $playerName) {
                    $config = \App\Models\ExternalTeamSeasonConfig::where('team_id', $match->team_id)
                        ->where('season_id', $match->season_id)
                        ->first();

                    if ($config) {
                        $user = $this->ensureUserExists($externalPlayerId, $playerName, $config);
                        $playerId = $user->id;
                    }
                }

                $attributes = [
                    'statistic_set_id' => $set->id,
                    'basketball_match_id' => $match->id,
                    'team_id' => $match->team_id,
                    'player_id' => $playerId,
                ];

                $rowValues = $row->values;
                if (!isset($rowValues['efficiency']) && !isset($rowValues['valuation'])) {
                    $rowValues['efficiency'] = $this->calculateEfficiencyFromValues($rowValues);
                }

                $values = [
                    'season_id' => $match->season_id,
                    'row_label' => $playerName,
                    'values' => $rowValues,
                    'source_metadata' => array_merge($row->metadata ?? [], [
                        'source' => 'czbasketball',
                        'match_external_id' => $match->metadata['external_id'] ?? null,
                        'player_external_id' => $externalPlayerId,
                        'scraped_at' => now()->toDateTimeString(),
                        'is_opponent' => false,
                    ]),
                ];

                $statRow = StatisticRow::where($attributes);
                if (! $playerId) {
                    $statRow->where('row_label', $playerName);
                }
                $statRow = $statRow->first();
                if ($statRow) {
                    $oldValues = $statRow->only(['values']);
                    $statRow->update($values);
                    if ($run && $statRow->wasChanged('values')) {
                        $run->addLog('updated', $statRow, $oldValues, $statRow->only(['values']), "Boxscore update: " . ($playerId ? "Player ID $playerId" : $playerName));
                    }
                } else {
                    $statRow = StatisticRow::create(array_merge($attributes, $values));
                    if ($run) {
                        $run->addLog('created', $statRow, null, $statRow->only(['values']), "Boxscore create: " . ($playerId ? "Player ID $playerId" : $playerName));
                    }
                }

                if ($playerId) {
                    // Generování automatických pokut za TH
                    try {
                        app(\App\Services\Finance\FinanceAutomationService::class)->processThFines($statRow);
                    } catch (\Exception $e) {
                        if ($run) {
                            $run->addLog('error', $statRow, null, null, "Chyba při generování pokut za TH: " . $e->getMessage());
                        }
                    }

                    $this->updateExternalPlayerMatch(
                        userId: $playerId,
                        externalMatchId: (string) ($match->external_id ?: ($match->metadata['external_id'] ?? null)),
                        externalPlayerId: $externalPlayerId,
                        rowValues: $row->values,
                        rowMetadata: $row->metadata ?? [],
                        matchInfo: [
                            'match_date' => $match->date,
                            'competition_label' => $match->metadata['match_header']['competition'] ?? $match->metadata['competition_label'] ?? null,
                            'opponent_name' => $match->metadata['match_header']['opponent'] ?? null,
                            'source_key' => 'czbasketball',
                        ]
                    );
                }
            });
        }

        $this->recomputePlayerSummaries($match->season_id);
        $this->recomputeTeamSummary($match->season_id, $match->team_id);
    }

    /**
     * Zajistí existenci uživatele pro externí ID (včetně vytvoření ghosta).
     */
    protected function ensureUserExists(string $externalId, string $name, \App\Models\ExternalTeamSeasonConfig $config): User
    {
        // Zkusíme najít mapping
        $mapping = ExternalEntityMapping::where([
            'source_key' => $config->source_key,
            'entity_type' => 'player',
            'external_id' => $externalId,
        ])->first();

        if ($mapping && $mapping->internal_id) {
            return User::findOrFail($mapping->internal_id);
        }

        // Použijeme RosterSyncService pro vytvoření (původně přes reflexi, nyní public)
        return app(\App\Services\Stats\Sync\RosterSyncService::class)
            ->findOrCreateUserForExternalPlayer($externalId, $name, $config);
    }

    /**
     * Najde interní ID uživatele podle externího ID a sezóny.
     */
    protected function findInternalPlayerId(?string $externalId, int $seasonId, string $sourceKey): ?int
    {
        if (! $externalId) {
            return null;
        }

        // Hledáme v mapování (které plní RosterSyncService)
        $mapping = ExternalEntityMapping::where([
            'source_key' => $sourceKey,
            'entity_type' => 'player',
            'external_id' => $externalId,
        ])->first();

        return $mapping?->internal_id;
    }

    /**
     * Přepočítá sezónní souhrny pro všechny hráče v dané sezóně.
     */
    public function recomputePlayerSummaries(int $seasonId): void
    {
        $boxscoreSet = StatisticSet::where('slug', StatisticSetService::MATCH_BOXSCORE_SET)->first();
        $summarySet = StatisticSet::where('slug', StatisticSetService::PLAYER_SEASON_SUMMARY_SET)->first();

        if (! $boxscoreSet || ! $summarySet) {
            ConsoleService::log("Přepočet hráčů zrušen: Chybí definice statistik (boxscore nebo summary).", 'warning');
            return;
        }

        // NEJDŘÍVE: Smažeme všechny stávající sumáře pro tuto sezónu, abychom odstranili případné sirotky a duplicity.
        // PŘIDÁNO: Používáme retry(5) kvůli chybě 1615 (Prepared statement needs to be re-prepared), která se objevuje na produkci.
        retry(5, function () use ($summarySet, $seasonId) {
            StatisticRow::where('statistic_set_id', $summarySet->id)
                ->where('season_id', $seasonId)
                ->delete();
        }, 200, function ($e) {
            Log::warning("recomputePlayerSummaries delete retry due to error 1615 (or other): " . $e->getMessage());
            return $e instanceof \Illuminate\Database\QueryException;
        });

        // Najdeme všechny hráče, kteří mají záznam v boxscoru pro tuto sezónu
        $playerIds = StatisticRow::where('statistic_set_id', $boxscoreSet->id)
            ->where('season_id', $seasonId)
            ->whereNotNull('player_id')
            ->distinct()
            ->pluck('player_id');

        $count = $playerIds->count();
        if ($count === 0) {
            ConsoleService::log("  - Žádní spárovaní hráči se statistikami pro sezónu ID $seasonId nenalezeni.", 'info');
            return;
        }

        ConsoleService::log("  - Přepočítávám sezónní souhrny pro $count hráčů...", 'info');

        // Přepočítáme souhrny per hráč (za celou sezónu napříč týmy)
        foreach ($playerIds as $playerId) {
            // Použijeme menší transakci pro každého hráče a jeho týmy, aby se uvolnila paměť a zámky
            DB::transaction(function () use ($playerId, $boxscoreSet, $summarySet, $seasonId) {
                $rows = StatisticRow::where('statistic_set_id', $boxscoreSet->id)
                    ->where('season_id', $seasonId)
                    ->where('player_id', $playerId)
                    ->get();

                if ($rows->isEmpty()) {
                    return;
                }

                // Vytvoříme souhrny per hráč (za celou sezónu napříč týmy)
                $summaryData = $this->aggregateRows($rows);

                StatisticRow::create([
                    'statistic_set_id' => $summarySet->id,
                    'player_id' => $playerId,
                    'season_id' => $seasonId,
                    'team_id' => null, // Globální souhrn pro sezónu
                    'values' => $summaryData,
                    'source_metadata' => [
                        'last_computed_at' => now()->toDateTimeString(),
                        'source' => 'aggregation_global',
                    ],
                ]);

                // PŘIDÁNO: Přepočítáme souhrny i pro jednotlivé týmy (kvůli rankingům v rámci týmu)
                $teamIds = $rows->pluck('team_id')->unique()->filter();
                foreach ($teamIds as $teamId) {
                    $teamRows = $rows->where('team_id', $teamId);
                    $teamSummaryData = $this->aggregateRows($teamRows);

                    StatisticRow::create([
                        'statistic_set_id' => $summarySet->id,
                        'player_id' => $playerId,
                        'season_id' => $seasonId,
                        'team_id' => $teamId,
                        'values' => $teamSummaryData,
                        'source_metadata' => [
                            'last_computed_at' => now()->toDateTimeString(),
                            'source' => 'aggregation_team',
                        ],
                    ]);
                }
            });

            // Pravidelné promazání query logu a garbage collection při dlouhém cyklu
            if ($playerId % 10 === 0) {
                gc_collect_cycles();
            }
        }
        ConsoleService::log("    - Hotovo ($count hráčů).", 'success');
    }

    /**
     * Přepočítá sezónní souhrn pro tým.
     */
    public function recomputeTeamSummary(int $seasonId, int $teamId): void
    {
        $teamSummarySet = StatisticSet::where('slug', StatisticSetService::TEAM_SEASON_SUMMARY_SET)->first();

        if (! $teamSummarySet) {
            ConsoleService::log("Přepočet týmu zrušen: Chybí definice TEAM_SEASON_SUMMARY_SET.", 'warning');
            return;
        }

        // Agregace zápasů pro tým
        $matches = BasketballMatch::where('season_id', $seasonId)
            ->where('team_id', $teamId)
            ->whereNotNull('score_home')
            ->whereNotNull('score_away')
            ->get();

        $gp = $matches->count();

        if ($gp === 0) {
            ConsoleService::log("    - Žádné dokončené zápasy pro tým ID $teamId v sezóně ID $seasonId.", 'info');
        }

        $wins = 0;
        $losses = 0;
        $ptsFor = 0;
        $ptsAgainst = 0;

        foreach ($matches as $match) {
            $myScore = $match->is_home ? $match->score_home : $match->score_away;
            $oppScore = $match->is_home ? $match->score_away : $match->score_home;

            $ptsFor += (int) $myScore;
            $ptsAgainst += (int) $oppScore;

            if ($myScore > $oppScore) {
                $wins++;
            } elseif ($myScore < $oppScore) {
                $losses++;
            }
        }

        StatisticRow::updateOrCreate(
            [
                'statistic_set_id' => $teamSummarySet->id,
                'team_id' => $teamId,
                'season_id' => $seasonId,
            ],
            [
                'values' => [
                    'gp' => $gp,
                    'wins' => $wins,
                    'losses' => $losses,
                    'pts_for' => $ptsFor,
                    'pts_against' => $ptsAgainst,
                    'pts_avg' => $gp > 0 ? round($ptsFor / $gp, 1) : 0,
                    'pts_against_avg' => $gp > 0 ? round($ptsAgainst / $gp, 1) : 0,
                ],
                'source_metadata' => [
                    'last_computed_at' => now()->toDateTimeString(),
                    'source' => 'aggregation',
                ],
            ]
        );

        if ($gp > 0) {
            ConsoleService::log("    - Přepočítán tým (GP: $gp, W: $wins, L: $losses).", 'success');
        }
    }

    /**
     * Pomocná metoda pro agregaci řádků statistik.
     */
    protected function aggregateRows($rows): array
    {
        $gp = $rows->count();
        $totals = [
            'pts' => 0,
            'minutes' => 0,
            'fg2_made' => 0,
            'fg2_att' => 0,
            'fg3_made' => 0,
            'fg3_att' => 0,
            'ft_made' => 0,
            'ft_att' => 0,
            'efficiency' => 0,
            'rebounds_total' => 0,
            'assists' => 0,
            'steals' => 0,
            'turnovers' => 0,
            'blocks' => 0,
            'fouls' => 0,
            'fouls_drawn' => 0,
        ];

        foreach ($rows as $row) {
            foreach ($totals as $key => $val) {
                $rawVal = $row->values[$key] ?? 0;

                // Fallback pro efektivitu (valuation vs efficiency) a manuální výpočet
                if ($key === 'efficiency' && (float)$rawVal === 0.0) {
                    $rawVal = $row->values['valuation'] ?? $this->calculateEfficiencyFromValues($row->values);
                }

                // Robustnější parsování (podpora pro lomítka "X/Y")
                if (is_string($rawVal) && str_contains($rawVal, '/')) {
                    $parts = explode('/', $rawVal);
                    $made = (float) trim($parts[0]);
                    $att = (float) trim($parts[1]);

                    if ($key === 'fg2_made') { $totals['fg2_made'] += $made; $totals['fg2_att'] += $att; }
                    elseif ($key === 'fg3_made') { $totals['fg3_made'] += $made; $totals['fg3_att'] += $att; }
                    elseif ($key === 'ft_made') { $totals['ft_made'] += $made; $totals['ft_att'] += $att; }
                    else { $totals[$key] += $made; }
                } else {
                    $totals[$key] += (float) $rawVal;
                }
            }
        }

        return [
            'gp' => $gp,

            // Celkové hodnoty (Totals)
            'pts_total' => $totals['pts'],
            'minutes_total' => $totals['minutes'],
            'fg2_total' => $totals['fg2_made'],
            'fg2_att_total' => $totals['fg2_att'],
            'fg3_total' => $totals['fg3_made'],
            'fg3_att_total' => $totals['fg3_att'],
            'ft_total' => $totals['ft_made'],
            'ft_att_total' => $totals['ft_att'],
            'efficiency_total' => $totals['efficiency'],
            'rebounds_total' => $totals['rebounds_total'],
            'assists_total' => $totals['assists'],
            'steals_total' => $totals['steals'],
            'turnovers_total' => $totals['turnovers'],
            'blocks_total' => $totals['blocks'],
            'fouls_total' => $totals['fouls'],
            'fouls_drawn_total' => $totals['fouls_drawn'],

            // Průměrné hodnoty (Averages)
            'ppg' => $gp > 0 ? round($totals['pts'] / $gp, 1) : 0,
            'minutes_avg' => $gp > 0 ? round($totals['minutes'] / $gp, 1) : 0,
            'fg2_pct' => $totals['fg2_att'] > 0 ? round(($totals['fg2_made'] / $totals['fg2_att']) * 100, 1) : null,
            'fg3_pct' => $totals['fg3_att'] > 0 ? round(($totals['fg3_made'] / $totals['fg3_att']) * 100, 1) : null,
            'ft_pct' => $totals['ft_att'] > 0 ? round(($totals['ft_made'] / $totals['ft_att']) * 100, 1) : null,

            // Průměry střelby (pro zobrazení bez pokusů)
            'fg2_avg' => $gp > 0 ? round($totals['fg2_made'] / $gp, 1) : 0,
            'fg3_avg' => $gp > 0 ? round($totals['fg3_made'] / $gp, 1) : 0,
            'ft_avg' => $gp > 0 ? round($totals['ft_made'] / $gp, 1) : 0,

            'efficiency_avg' => $gp > 0 ? round($totals['efficiency'] / $gp, 1) : 0,
            'rebounds_avg' => $gp > 0 ? round($totals['rebounds_total'] / $gp, 1) : 0,
            'assists_avg' => $gp > 0 ? round($totals['assists'] / $gp, 1) : 0,
            'steals_avg' => $gp > 0 ? round($totals['steals'] / $gp, 1) : 0,
            'turnovers_avg' => $gp > 0 ? round($totals['turnovers'] / $gp, 1) : 0,
            'blocks_avg' => $gp > 0 ? round($totals['blocks'] / $gp, 1) : 0,
            'fouls_avg' => $gp > 0 ? round($totals['fouls'] / $gp, 1) : 0,
            'fouls_drawn_avg' => $gp > 0 ? round($totals['fouls_drawn'] / $gp, 1) : 0,
        ];
    }

    /**
     * Propojí externí identitu s interním uživatelem a přepočítá statistiky.
     */
    public function linkPlayerAndRecompute(ExternalEntityMapping $mapping, int $userId): void
    {
        DB::transaction(function () use ($mapping, $userId) {
            $oldInternalId = $mapping->internal_id;

            $mapping->update([
                'internal_id' => $userId,
                'internal_type' => User::class,
            ]);

            // Aktualizace řádků statistik pro tento externí ID a sezónu (pokud je sezónní)
            // nebo globálně (pokud je hráč stabilní).
            $query = StatisticRow::where(function ($q) use ($mapping) {
                $q->where('source_metadata', 'like', '%"player_external_id":"' . (string) $mapping->external_id . '"%')
                    ->orWhere('source_metadata', 'like', '%"player_external_id":' . (string) $mapping->external_id . '%');
            });

            if ($mapping->season_id) {
                $query->where('season_id', $mapping->season_id);
            }

            $query->update([
                'player_id' => $userId,
                'row_label' => null,
            ]);

            // Čištění: Pokud byl původně přiřazen "Ghost" uživatel a nyní ho nahrazujeme reálným,
            // smažeme osiřelého ghosta, aby v systému nezůstávaly duplikáty.
            if ($oldInternalId && (int) $oldInternalId !== (int) $userId) {
                $oldUser = User::find($oldInternalId);
                if ($oldUser && ($oldUser->metadata['is_ghost'] ?? false)) {
                    // Zkontrolujeme, zda na tohoto ghosta už neukazuje žádný jiný mapping
                    $otherMappingsCount = ExternalEntityMapping::where('internal_id', $oldInternalId)
                        ->where('id', '!=', (int) $mapping->id)
                        ->count();

                    if ($otherMappingsCount === 0) {
                        // Smažeme ghosta (včetně jeho profilů a vztahů s týmy)
                        foreach ($oldUser->playerProfiles as $oldProfile) {
                            $oldProfile->teams()->detach();
                            $oldProfile->delete();
                        }
                        $oldUser->delete();
                    }
                }
            }
        });

        // Přepočet souhrnů
        if ($mapping->season_id) {
            $this->recomputePlayerSummaries($mapping->season_id);
        }
    }

    /**
     * Normalizuje text pro porovnání (odstraní whitespace, diakritiku, převede na malé).
     */
    public function normalizeForComparison(string $text): string
    {
        $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
        $text = \Illuminate\Support\Str::ascii($text);
        $text = strtolower($text);
        $text = preg_replace('/[^a-z0-9]/', '', $text);

        return $text;
    }

    /**
     * Aktualizuje nebo vytvoří detailní záznam zápasu pro externího hráče.
     */
    public function updateExternalPlayerMatch(
        int $userId,
        string $externalMatchId,
        ?string $externalPlayerId,
        array $rowValues,
        array $rowMetadata,
        array $matchInfo = []
    ): \App\Models\ExternalPlayerMatch {
        $sourceKey = $matchInfo['source_key'] ?? 'czbasketball';

        return \App\Models\ExternalPlayerMatch::updateOrCreate(
            [
                'user_id' => $userId,
                'source_key' => $sourceKey,
                'external_match_id' => (string) $externalMatchId,
            ],
            [
                'external_id' => $externalPlayerId,
                'match_date' => $matchInfo['match_date'] ?? null,
                'scheduled_at' => $matchInfo['scheduled_at'] ?? null,
                'competition_label' => $matchInfo['competition_label'] ?? null,
                'opponent_name' => $matchInfo['opponent_name'] ?? null,
                'venue' => $matchInfo['venue'] ?? null,
                'number' => $rowValues['number'] ?? null,
                'is_starter' => (bool) ($rowMetadata['is_starter'] ?? false),
                'is_captain' => (bool) ($rowMetadata['is_captain'] ?? false),
                'points' => (int) ($rowValues['pts'] ?? 0),
                'two_points_made' => isset($rowValues['fg2_made']) ? (int) $rowValues['fg2_made'] : null,
                'two_points_attempts' => isset($rowValues['fg2_att']) ? (int) $rowValues['fg2_att'] : null,
                'three_points_made' => isset($rowValues['fg3_made']) ? (int) $rowValues['fg3_made'] : null,
                'three_points_attempts' => isset($rowValues['fg3_att']) ? (int) $rowValues['fg3_att'] : null,
                'free_throws_made' => isset($rowValues['ft_made']) ? (int) $rowValues['ft_made'] : null,
                'free_throws_attempts' => isset($rowValues['ft_att']) ? (int) $rowValues['ft_att'] : null,
                'free_throws_pct' => isset($rowValues['ft_pct']) ? (float) $rowValues['ft_pct'] : null,
                'fouls' => isset($rowValues['fouls']) ? (int) $rowValues['fouls'] : null,
                'minutes' => isset($rowValues['minutes']) ? (int) $rowValues['minutes'] : null,
                'valuation' => isset($rowValues['efficiency']) ? (int) $rowValues['efficiency'] : (isset($rowValues['valuation']) ? (int) $rowValues['valuation'] : null),
                'plus_minus' => isset($rowValues['plus_minus']) ? (int) $rowValues['plus_minus'] : null,
                'rebounds_offensive' => isset($rowValues['rebounds_offensive']) ? (int) $rowValues['rebounds_offensive'] : null,
                'rebounds_defensive' => isset($rowValues['rebounds_defensive']) ? (int) $rowValues['rebounds_defensive'] : null,
                'rebounds_total' => isset($rowValues['rebounds_total']) ? (int) $rowValues['rebounds_total'] : null,
                'assists' => isset($rowValues['assists']) ? (int) $rowValues['assists'] : null,
                'steals' => isset($rowValues['steals']) ? (int) $rowValues['steals'] : null,
                'turnovers' => isset($rowValues['turnovers']) ? (int) $rowValues['turnovers'] : null,
                'blocks' => isset($rowValues['blocks']) ? (int) $rowValues['blocks'] : null,
                'fouls_drawn' => isset($rowValues['fouls_drawn']) ? (int) $rowValues['fouls_drawn'] : null,
                'metadata' => $matchInfo['metadata'] ?? null,
                'boxscore_synced_at' => $matchInfo['boxscore_synced_at'] ?? null,
            ]
        );
    }

    /**
     * Vypočítá index užitečnosti (VAL/efficiency) z dostupných boxscore metrik.
     * PTS + REB + AST + STL + BLK - (FGA - FGM) - (FTA - FTM) - TO - PF
     */
    protected function calculateEfficiencyFromValues(array $values): float
    {
        $pts = (float) ($values['pts'] ?? 0);
        $reb = (float) ($values['rebounds_total'] ?? 0);
        $ast = (float) ($values['assists'] ?? 0);
        $stl = (float) ($values['steals'] ?? 0);
        $blk = (float) ($values['blocks'] ?? 0);

        $fg2_made = (float) ($values['fg2_made'] ?? 0);
        $fg2_att = (float) ($values['fg2_att'] ?? 0);
        $fg3_made = (float) ($values['fg3_made'] ?? 0);
        $fg3_att = (float) ($values['fg3_att'] ?? 0);
        $ft_made = (float) ($values['ft_made'] ?? 0);
        $ft_att = (float) ($values['ft_att'] ?? 0);

        $to = (float) ($values['turnovers'] ?? 0);
        $fouls = (float) ($values['fouls'] ?? 0);

        // Neproměněné střely (pokud známe pokusy)
        $missed_fg = 0;
        if ($fg2_att > 0) $missed_fg += ($fg2_att - $fg2_made);
        if ($fg3_att > 0) $missed_fg += ($fg3_att - $fg3_made);

        $missed_ft = 0;
        if ($ft_att > 0) $missed_ft += ($ft_att - $ft_made);

        // Výpočet VAL
        // Pokud nemáme doskoky, asistence atd. (v nižších ligách), aspoň zohledníme Body - Fauly - Neproměněné hody.
        return $pts + $reb + $ast + $stl + $blk - $missed_fg - $missed_ft - $to - $fouls;
    }
}
