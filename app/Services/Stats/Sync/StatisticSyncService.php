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

        if ($contentHash) {
            // LIKE pro kompatibilitu se staršími DB bez JSON funkcí
            $query->where('source_metadata', 'LIKE', '%"content_hash":"' . $contentHash . '"%');
        }

        $statRow = $query->first();

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

        $count = StatisticRow::where('statistic_set_id', $set->id)
            ->where('basketball_match_id', $match->id)
            ->delete();

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

        DB::transaction(function () use ($match, $data, $set, $run) {
            $isOurTeam = true;
            $currentTeamId = $match->team_id;

            // Zkusíme detekovat, zda je to tabulka našeho týmu nebo soupeře
            $teamNameValue = $match->team->getTranslation('name', 'cs') ?: ($match->team->name ?: '');
            if (is_array($teamNameValue)) {
                $teamNameValue = $teamNameValue['cs'] ?? ($teamNameValue['en'] ?? (reset($teamNameValue) ?: ''));
            }

            $possibleOurNames = [
                $this->normalizeForComparison($teamNameValue),
            ];

            // Přidáme název z externí konfigurace, pokud existuje
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
                // Poslední šance: pokud je v tabulce jméno našeho týmu jako podřetězec bez normalizace
                if (! str_contains(mb_strtolower($data->name), mb_strtolower($teamNameValue)) &&
                    (! $externalName || ! str_contains(mb_strtolower($data->name), mb_strtolower($externalName)))) {
                    $isOurTeam = false;
                    $currentTeamId = null;
                }
            }

            foreach ($data->rows as $row) {
                $externalPlayerId = $row->metadata['external_player_id'] ?? null;
                $playerName = $row->rowLabel;

                // Párování hráče (jen pro náš tým má smysl hledat internal_id)
                $playerId = $isOurTeam ? $this->findInternalPlayerId($externalPlayerId, $match->season_id, 'czbasketball') : null;

                $attributes = [
                    'statistic_set_id' => $set->id,
                    'basketball_match_id' => $match->id,
                    'team_id' => $currentTeamId,
                    'player_id' => $playerId,
                    'row_label' => $playerId ? null : $playerName,
                ];

                $values = [
                    'season_id' => $match->season_id,
                    'values' => $row->values,
                    'source_metadata' => [
                        'source' => 'czbasketball',
                        'match_external_id' => $match->metadata['external_id'] ?? null,
                        'player_external_id' => $externalPlayerId,
                        'scraped_at' => now()->toDateTimeString(),
                        'is_opponent' => ! $isOurTeam,
                    ],
                ];

                $statRow = StatisticRow::where($attributes)->first();
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
            }
        });

        $this->recomputePlayerSummaries($match->season_id);
        $this->recomputeTeamSummary($match->season_id, $match->team_id);
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

        foreach ($playerIds as $playerId) {
            $rows = StatisticRow::where('statistic_set_id', $boxscoreSet->id)
                ->where('season_id', $seasonId)
                ->where('player_id', $playerId)
                ->get();

            $summaryData = $this->aggregateRows($rows);

            StatisticRow::updateOrCreate(
                [
                    'statistic_set_id' => $summarySet->id,
                    'player_id' => $playerId,
                    'season_id' => $seasonId,
                ],
                [
                    'values' => $summaryData,
                    'source_metadata' => [
                        'last_computed_at' => now()->toDateTimeString(),
                        'source' => 'aggregation',
                    ],
                ]
            );
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
            ->where('status', 'completed')
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
        ];

        foreach ($rows as $row) {
            foreach ($totals as $key => $val) {
                $totals[$key] += (float) ($row->values[$key] ?? 0);
            }
        }

        return [
            'gp' => $gp,
            'pts_total' => $totals['pts'],
            'ppg' => $gp > 0 ? round($totals['pts'] / $gp, 1) : 0,
            'minutes_avg' => $gp > 0 ? round($totals['minutes'] / $gp, 1) : 0,
            'fg2_pct' => $totals['fg2_att'] > 0 ? round(($totals['fg2_made'] / $totals['fg2_att']) * 100, 1) : 0,
            'fg3_pct' => $totals['fg3_att'] > 0 ? round(($totals['fg3_made'] / $totals['fg3_att']) * 100, 1) : 0,
            'ft_pct' => $totals['ft_att'] > 0 ? round(($totals['ft_made'] / $totals['ft_att']) * 100, 1) : 0,
        ];
    }

    /**
     * Propojí externí identitu s interním uživatelem a přepočítá statistiky.
     */
    public function linkPlayerAndRecompute(ExternalEntityMapping $mapping, int $userId): void
    {
        DB::transaction(function () use ($mapping, $userId) {
            $mapping->update([
                'internal_id' => $userId,
                'internal_type' => User::class,
            ]);

            // Aktualizace řádků statistik pro tento externí ID a sezónu (pokud je sezónní)
            // nebo globálně (pokud je hráč stabilní).
            $query = StatisticRow::where(function($q) use ($mapping) {
                $q->where('source_metadata', 'LIKE', '%"player_external_id":"' . $mapping->external_id . '"%')
                  ->orWhere('source_metadata', 'LIKE', '%"player_external_id":' . $mapping->external_id . '%');
            });

            if ($mapping->season_id) {
                $query->where('season_id', $mapping->season_id);
            }

            $query->update([
                'player_id' => $userId,
                'row_label' => null,
            ]);
        });

        // Přepočet souhrnů
        if ($mapping->season_id) {
            $this->recomputePlayerSummaries($mapping->season_id);
        }
    }

    /**
     * Normalizuje text pro porovnání (odstraní whitespace, diakritiku, převede na malé).
     */
    protected function normalizeForComparison(string $text): string
    {
        $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
        $text = \Illuminate\Support\Str::ascii($text);
        $text = strtolower($text);
        $text = preg_replace('/[^a-z0-9]/', '', $text);

        return $text;
    }
}
