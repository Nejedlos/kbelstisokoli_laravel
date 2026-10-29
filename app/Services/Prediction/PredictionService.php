<?php

namespace App\Services\Prediction;

use App\Jobs\ComputeMatchPredictionJob;
use App\Models\BasketballMatch;
use App\Models\MatchPrediction;
use App\Models\StatisticRow;
use App\Models\TeamEloRating;

class PredictionService
{
    private EloCalculator $eloCalculator;

    private FormCalculator $formCalculator;

    private RosterStrengthCalculator $rosterStrengthCalculator;

    private MutualMatchesCalculator $mutualMatchesCalculator;

    public function __construct(
        EloCalculator $eloCalculator,
        FormCalculator $formCalculator,
        RosterStrengthCalculator $rosterStrengthCalculator,
        MutualMatchesCalculator $mutualMatchesCalculator
    ) {
        $this->eloCalculator = $eloCalculator;
        $this->formCalculator = $formCalculator;
        $this->rosterStrengthCalculator = $rosterStrengthCalculator;
        $this->mutualMatchesCalculator = $mutualMatchesCalculator;
    }

    /**
     * Historical boxscores affect future roster strength, not the frozen snapshot.
     */
    public function queueForStatistics(StatisticRow $row): void
    {
        $sourceMatch = $row->match;
        $seasonId = $row->season_id ?? $sourceMatch?->season_id;
        $teamId = $row->team_id ?? $sourceMatch?->team_id;
        $opponentId = $row->opponent_id ?? $sourceMatch?->opponent_id;
        if (! $seasonId || (! $teamId && ! $opponentId)) {
            return;
        }

        BasketballMatch::where('season_id', $seasonId)
            ->whereIn('status', ['planned', 'scheduled'])
            ->whereNotNull('team_id')->whereNotNull('opponent_id')
            ->where(function ($query) use ($teamId, $opponentId): void {
                $query->when($teamId, fn ($q) => $q->where('team_id', $teamId))
                    ->when($opponentId, fn ($q) => $q->orWhere('opponent_id', $opponentId));
            })
            ->eachById(fn (BasketballMatch $match) => ComputeMatchPredictionJob::dispatch($match->id)->afterCommit());
    }

    public function predict(BasketballMatch $match): ?MatchPrediction
    {
        if (! $match->hasPredictionContext()) {
            if ($match->exists) {
                $match->prediction()->delete();
            }

            return null;
        }

        // A post-match import must not rewrite a pre-match snapshot using later results.
        if (in_array($match->status, ['finished', 'played', 'completed'])
            || ($match->has_score && ($match->score_home + $match->score_away > 0))) {
            return $match->prediction()->where('team_id', $match->team_id)
                ->where('season_id', $match->season_id)->first();
        }

        $teamKey = TeamEloRating::getInternalTeamKey($match->team_id);
        $oppKey = TeamEloRating::getOpponentKey($match->opponent_id);

        $teamElo = TeamEloRating::where('season_id', $match->season_id)->where('team_key', $teamKey)->value('rating') ?? $this->eloCalculator::INITIAL_ELO;
        $oppElo = TeamEloRating::where('season_id', $match->season_id)->where('team_key', $oppKey)->value('rating') ?? $this->eloCalculator::INITIAL_ELO;

        // 1. Elo Prob
        $eloProb = $this->eloCalculator->calculateExpected($teamElo, $oppElo, $match->is_home);

        // 2. Form Prob
        $formResult = $this->formCalculator->calculateFormDelta($match);
        $teamFormSource = $formResult['count'] > 0 ? 'internal' : 'none';
        if ($formResult['count'] === 0) {
            $formResult = $this->formCalculator->calculateSourceFormDelta($match, 'team');
            $teamFormSource = $formResult['count'] > 0 ? 'external_preview' : 'none';
        }
        $opponentFormResult = $this->formCalculator->calculateSourceFormDelta($match, 'opponent');
        $formDelta = max(-60, min(60, $formResult['delta'] - $opponentFormResult['delta']));
        $formProb = $this->eloCalculator->calculateExpected($teamElo + $formDelta, $oppElo, $match->is_home);

        // 3. Roster Prob
        $rosterResult = $this->rosterStrengthCalculator->calculateRosterStrength($match);
        $rosterDelta = $rosterResult['delta'];
        $rosterProb = $this->eloCalculator->calculateExpected($teamElo + $rosterDelta, $oppElo, $match->is_home);

        // 4. Preview Prob (from external comparison)
        $previewResult = $this->calculatePreviewDelta($match);
        $previewDelta = $previewResult['delta'];
        $previewProb = $this->eloCalculator->calculateExpected($teamElo + $previewDelta, $oppElo, $match->is_home);

        // 5. Mutual Matches Prob (H2H)
        $mutualResult = $this->mutualMatchesCalculator->calculateMutualMatchesDelta($match);
        $mutualDelta = $mutualResult['delta'];
        $mutualProb = $this->eloCalculator->calculateExpected($teamElo + $mutualDelta, $oppElo, $match->is_home);

        // Mix probabilities
        $w1 = 0.40; // Elo (reduced from 0.50)
        $w2 = 0.15; // Form (reduced from 0.20)
        $w3 = 0.15; // Roster
        $w4 = 0.15; // Preview
        $w5 = 0.15; // Mutual Matches (H2H)

        if ($rosterResult['opponent']['count'] === 0 || $rosterResult['team']['count'] === 0) {
            $w3 = 0;
            // Přerozdělíme w3 mezi ostatní
            $totalRemaining = $w1 + $w2 + $w4 + $w5;
            if ($totalRemaining > 0) {
                $w1 += (0.15 * ($w1 / $totalRemaining));
                $w2 += (0.15 * ($w2 / $totalRemaining));
                $w4 += (0.15 * ($w4 / $totalRemaining));
                $w5 += (0.15 * ($w5 / $totalRemaining));
            }
        }

        if (empty($previewResult['factors'])) {
            $prevW4 = $w4;
            $w4 = 0;
            // Přerozdělíme w4 mezi ostatní
            $totalRemaining = $w1 + $w2 + $w3 + $w5;
            if ($totalRemaining > 0) {
                $w1 += ($prevW4 * ($w1 / $totalRemaining));
                $w2 += ($prevW4 * ($w2 / $totalRemaining));
                $w3 += ($prevW4 * ($w3 / $totalRemaining));
                $w5 += ($prevW4 * ($w5 / $totalRemaining));
            }
        }

        if ($mutualResult['count'] === 0) {
            $prevW5 = $w5;
            $w5 = 0;
            // Přerozdělíme w5 mezi ostatní
            $totalRemaining = $w1 + $w2 + $w3 + $w4;
            if ($totalRemaining > 0) {
                $w1 += ($prevW5 * ($w1 / $totalRemaining));
                $w2 += ($prevW5 * ($w2 / $totalRemaining));
                $w3 += ($prevW5 * ($w3 / $totalRemaining));
                $w4 += ($prevW5 * ($w4 / $totalRemaining));
            }
        }

        $logitMix = $w1 * $this->logit($eloProb) +
                    $w2 * $this->logit($formProb) +
                    $w3 * $this->logit($rosterProb) +
                    $w4 * $this->logit($previewProb) +
                    $w5 * $this->logit($mutualProb);
        $finalProb = $this->invLogit($logitMix);

        // Zmírnění extrémů (squashing) - sport není nikdy 100% jistý
        // Mapujeme 0-1 na 0.10-0.90
        $finalProb = 0.10 + ($finalProb * 0.80);

        // Confidence
        $confidence = 'low';
        $teamMatchesCount = BasketballMatch::forHistoricalPredictionTeam($match->team_id)
            ->where('season_id', $match->season_id)
            ->whereIn('status', ['finished', 'played', 'completed'])
            ->when($match->scheduled_at, fn ($query) => $query->where('scheduled_at', '<', $match->scheduled_at))
            ->whereNotNull('score_home')->whereNotNull('score_away')
            ->whereRaw('(score_home + score_away) > 0')->count();
        $teamMatchesCount = max($teamMatchesCount, $formResult['count']);
        if ($teamMatchesCount >= 10 && ($rosterResult['opponent']['count'] > 0 || ! empty($previewResult['factors']) || $opponentFormResult['count'] > 0)) {
            $confidence = 'high';
        } elseif ($teamMatchesCount >= 5 || ! empty($previewResult['factors']) || $opponentFormResult['count'] > 0) {
            $confidence = 'medium';
        }

        // Explanation
        $explanation = $this->generateExplanation($match, $formResult, $opponentFormResult, $rosterResult, $previewResult, $mutualResult);

        return MatchPrediction::updateOrCreate(
            ['basketball_match_id' => $match->id],
            [
                'season_id' => $match->season_id,
                'team_id' => $match->team_id,
                'computed_at' => now(),
                'model_version' => 'elo+form+roster+preview+h2h:v4',
                'probability_win' => $finalProb,
                'probability_loss' => 1 - $finalProb,
                'confidence' => $confidence,
                'factors' => [
                    'elo_team' => $teamElo,
                    'elo_opp' => $oppElo,
                    'elo_prob' => $eloProb,
                    'form_team' => $formResult,
                    'form_team_source' => $teamFormSource,
                    'form_opp' => $opponentFormResult,
                    'form_opp_source' => $opponentFormResult['count'] > 0 ? 'external_preview' : 'none',
                    'form_delta' => $formDelta,
                    'roster_team' => $rosterResult['team'],
                    'roster_opp' => $rosterResult['opponent'],
                    'roster_delta' => $rosterDelta,
                    'preview_data' => $previewResult,
                    'preview_delta' => $previewDelta,
                    'mutual_data' => $mutualResult,
                    'mutual_delta' => $mutualDelta,
                    'home_adv_applied' => $match->is_home,
                ],
                'explanation_points' => $explanation,
                'expires_at' => $match->scheduled_at ? $match->scheduled_at->copy()->subHour() : now()->addDay(),
            ]
        );
    }

    private function calculatePreviewDelta(BasketballMatch $match): array
    {
        $comparison = $match->metadata['team_comparison'] ?? [];
        if (empty($comparison)) {
            return ['delta' => 0, 'factors' => []];
        }

        $delta = 0;
        $factors = [];

        // Body na zápas (PG)
        if ($this->hasNumericComparison($comparison['pts_per_game'] ?? [])) {
            $homePts = (float) str_replace(',', '.', $comparison['pts_per_game']['home']);
            $awayPts = (float) str_replace(',', '.', $comparison['pts_per_game']['away']);

            $diff = $match->is_home ? ($homePts - $awayPts) : ($awayPts - $homePts);
            // 1 bod rozdílu v průměru = cca 5 Elo bodů (odhad)
            $delta += $diff * 5;
            $factors['pts'] = $diff;
        }

        // Úspěšnost střelby
        if ($this->hasNumericComparison($comparison['fg2_pct'] ?? [])) {
            $homePct = (float) str_replace(['%', ','], ['', '.'], $comparison['fg2_pct']['home']);
            $awayPct = (float) str_replace(['%', ','], ['', '.'], $comparison['fg2_pct']['away']);
            $diff = $match->is_home ? ($homePct - $awayPct) : ($awayPct - $homePct);
            $delta += $diff * 2; // 1% = 2 Elo
            $factors['fg2_pct'] = $diff;
        }

        return [
            'delta' => max(-50, min(50, $delta)),
            'factors' => $factors,
            'source' => 'external_comparison',
        ];
    }

    private function logit(float $p): float
    {
        $p = max(0.001, min(0.999, $p));

        return log($p / (1 - $p));
    }

    private function invLogit(float $x): float
    {
        return 1 / (1 + exp(-$x));
    }

    private function hasNumericComparison(array $comparison): bool
    {
        foreach (['home', 'away'] as $side) {
            $value = str_replace(['%', ',', ' ', "\u{00A0}"], ['', '.', '', ''], (string) ($comparison[$side] ?? ''));
            if (! is_numeric($value)) {
                return false;
            }
        }

        return true;
    }

    private function generateExplanation(BasketballMatch $match, array $formResult, array $opponentFormResult, array $rosterResult, array $previewResult, array $mutualResult): array
    {
        $points = [];

        // 1. Vzájemné zápasy
        if ($mutualResult['count'] > 0) {
            $wins = $mutualResult['wins'];
            $count = $mutualResult['count'];
            $delta = $mutualResult['delta'];

            $text = "Historie: z posledních {$count} vzájemných zápasů jsme vyhráli {$wins}x.";
            if ($delta < -50) {
                $text .= ' Tato bilance výrazně snižuje naši šanci na výhru.';
            } elseif ($delta > 50) {
                $text .= ' Tato historie nám dává psychickou výhodu.';
            }
            $points[] = $text;
        }

        // 2. Domácí prostředí
        if ($match->is_home) {
            $points[] = "Výhodu nám dává domácí prostředí (+{$this->eloCalculator->getHomeAdvantage()} Elo).";
        }

        // 3. Forma týmu (z interních dat)
        if ($formResult['count'] > 0) {
            $winText = "{$formResult['wins']}–".($formResult['count'] - $formResult['wins']);
            $diffPrefix = $formResult['avg_diff'] > 0 ? '+' : '';
            $points[] = "Naše forma: posledních {$formResult['count']} zápasů {$winText}, průměrný rozdíl skóre {$diffPrefix}".round($formResult['avg_diff'], 1).'.';
        }

        // 4. Forma soupeře ze stejné ověřené historie, kterou používá výpočet.
        if ($opponentFormResult['count'] > 0) {
            $oppCount = $opponentFormResult['count'];
            $oppWins = $opponentFormResult['wins'];
            $points[] = "Forma soupeře: z posledních {$oppCount} zápasů vyhráli {$oppWins}x.";
        }

        // 5. Soupiska
        if ($rosterResult['team']['count'] >= 3) {
            $points[] = __('matches.prediction.roster_summary', ['count' => $rosterResult['team']['count'], 'points' => round($rosterResult['team']['total'] / $rosterResult['team']['count'], 1)]);
        }

        // 6. Rozvaha (externí srovnání)
        if (! empty($previewResult['factors']['pts'])) {
            $diff = $previewResult['factors']['pts'];
            $diffPrefix = $diff > 0 ? '+' : '';
            $points[] = "Statistika (rozvaha): rozdíl v průměru vstřelených bodů obou týmů je {$diffPrefix}".round($diff, 1).'.';
        }

        // 7. Varování
        if ($rosterResult['opponent']['count'] === 0 && empty($previewResult['factors']) && $opponentFormResult['count'] === 0) {
            $points[] = 'Pozor: o soupeři máme málo dat → predikce má nižší jistotu.';
        }

        if (! empty($previewResult['factors'])) {
            $points[] = 'Model využívá aktuální statistické srovnání (rozvahu) ze serveru cz.basketball.';
        }

        return $points;
    }
}
