<?php

namespace App\Services\Prediction;

use App\Models\BasketballMatch;
use App\Models\MatchPrediction;
use App\Models\TeamEloRating;
use Carbon\Carbon;

class PredictionService
{
    private EloCalculator $eloCalculator;
    private FormCalculator $formCalculator;
    private RosterStrengthCalculator $rosterStrengthCalculator;

    public function __construct(
        EloCalculator $eloCalculator,
        FormCalculator $formCalculator,
        RosterStrengthCalculator $rosterStrengthCalculator
    ) {
        $this->eloCalculator = $eloCalculator;
        $this->formCalculator = $formCalculator;
        $this->rosterStrengthCalculator = $rosterStrengthCalculator;
    }

    public function predict(BasketballMatch $match): MatchPrediction
    {
        $teamKey = TeamEloRating::getInternalTeamKey($match->team_id);
        $oppKey = TeamEloRating::getOpponentKey($match->opponent_id);

        $teamElo = TeamEloRating::where('season_id', $match->season_id)->where('team_key', $teamKey)->value('rating') ?? $this->eloCalculator::INITIAL_ELO;
        $oppElo = TeamEloRating::where('season_id', $match->season_id)->where('team_key', $oppKey)->value('rating') ?? $this->eloCalculator::INITIAL_ELO;

        // 1. Elo Prob
        $eloProb = $this->eloCalculator->calculateExpected($teamElo, $oppElo, $match->is_home);

        // 2. Form Prob
        $formResult = $this->formCalculator->calculateFormDelta($match);
        $formDelta = $formResult['delta'];
        $formProb = $this->sigmoid(($teamElo + $formDelta) - $oppElo);

        // 3. Roster Prob
        $rosterResult = $this->rosterStrengthCalculator->calculateRosterStrength($match);
        $rosterDelta = $rosterResult['delta'];
        $rosterProb = $this->sigmoid(($teamElo + $rosterDelta) - $oppElo);

        // 4. Preview Prob (from external comparison)
        $previewResult = $this->calculatePreviewDelta($match);
        $previewDelta = $previewResult['delta'];
        $previewProb = $this->sigmoid(($teamElo + $previewDelta) - $oppElo);

        // Mix probabilities
        $w1 = 0.50; // Elo
        $w2 = 0.20; // Form
        $w3 = 0.15; // Roster
        $w4 = 0.15; // Preview

        if ($rosterResult['opponent']['count'] === 0) {
            $w3 = 0;
            $w1 += 0.05;
            $w2 += 0.05;
            $w4 += 0.05;
        }

        if ($previewDelta === 0) {
            $w4 = 0;
            // Přerozdělíme w4 mezi ostatní
            $totalRemaining = $w1 + $w2 + $w3;
            if ($totalRemaining > 0) {
                $w1 += (0.15 * ($w1 / $totalRemaining));
                $w2 += (0.15 * ($w2 / $totalRemaining));
                $w3 += (0.15 * ($w3 / $totalRemaining));
            }
        }

        $logitMix = $w1 * $this->logit($eloProb) + $w2 * $this->logit($formProb) + $w3 * $this->logit($rosterProb) + $w4 * $this->logit($previewProb);
        $finalProb = $this->invLogit($logitMix);

        // Confidence
        $confidence = 'low';
        $teamMatchesCount = BasketballMatch::where('team_id', $match->team_id)->where('season_id', $match->season_id)->whereNotNull('score_home')->count();
        if ($teamMatchesCount >= 10 && ($rosterResult['opponent']['count'] > 0 || $previewDelta !== 0)) {
            $confidence = 'high';
        } elseif ($teamMatchesCount >= 5 || $previewDelta !== 0) {
            $confidence = 'medium';
        }

        // Explanation
        $explanation = $this->generateExplanation($match, $eloProb, $formResult, $rosterResult, $previewResult);

        return MatchPrediction::updateOrCreate(
            ['basketball_match_id' => $match->id],
            [
                'season_id' => $match->season_id,
                'team_id' => $match->team_id,
                'computed_at' => now(),
                'model_version' => 'elo+form+roster+preview:v1',
                'probability_win' => $finalProb,
                'probability_loss' => 1 - $finalProb,
                'confidence' => $confidence,
                'factors' => [
                    'elo_team' => $teamElo,
                    'elo_opp' => $oppElo,
                    'elo_prob' => $eloProb,
                    'form_team' => $formResult,
                    'form_delta' => $formDelta,
                    'roster_team' => $rosterResult['team'],
                    'roster_opp' => $rosterResult['opponent'],
                    'roster_delta' => $rosterDelta,
                    'preview_data' => $previewResult,
                    'preview_delta' => $previewDelta,
                    'home_adv_applied' => $match->is_home,
                ],
                'explanation_points' => $explanation,
                'expires_at' => $match->scheduled_at ? $match->scheduled_at->subHour() : now()->addDay(),
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
        if (isset($comparison['pts_per_game'])) {
            $homePts = (float) str_replace(',', '.', $comparison['pts_per_game']['home']);
            $awayPts = (float) str_replace(',', '.', $comparison['pts_per_game']['away']);

            $diff = $match->is_home ? ($homePts - $awayPts) : ($awayPts - $homePts);
            // 1 bod rozdílu v průměru = cca 5 Elo bodů (odhad)
            $delta += $diff * 5;
            $factors['pts'] = $diff;
        }

        // Úspěšnost střelby
        if (isset($comparison['fg2_pct'])) {
             $homePct = (float) str_replace(['%', ','], ['', '.'], $comparison['fg2_pct']['home']);
             $awayPct = (float) str_replace(['%', ','], ['', '.'], $comparison['fg2_pct']['away']);
             $diff = $match->is_home ? ($homePct - $awayPct) : ($awayPct - $homePct);
             $delta += $diff * 2; // 1% = 2 Elo
        }

        return [
            'delta' => max(-100, min(100, $delta)),
            'factors' => $factors,
            'source' => 'external_comparison'
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

    private function sigmoid(float $x): float
    {
        // Simple mapping for delta to probability
        return 1 / (1 + 10 ** (-$x / 400));
    }

    private function generateExplanation(BasketballMatch $match, float $eloProb, array $formResult, array $rosterResult, array $previewResult): array
    {
        $points = [];
        if ($match->is_home) {
            $points[] = "Výhodu nám dává domácí prostředí (+{$this->eloCalculator->getHomeAdvantage()} Elo).";
        }

        if ($formResult['count'] >= 3) {
            $winText = "{$formResult['wins']}–" . ($formResult['count'] - $formResult['wins']);
            $diffPrefix = $formResult['avg_diff'] > 0 ? '+' : '';
            $points[] = "Naše forma: posledních {$formResult['count']} zápasů {$winText}, průměrný rozdíl skóre {$diffPrefix}" . round($formResult['avg_diff'], 1) . ".";
        }

        if ($rosterResult['team']['count'] >= 3) {
             $points[] = "Naše soupiska: top 5 hráčů drží průměrně " . round($rosterResult['team']['total'] / 5, 1) . " bodů na zápas (dle interních dat).";
        }

        if (!empty($previewResult['factors']['pts'])) {
            $diff = $previewResult['factors']['pts'];
            $diffPrefix = $diff > 0 ? '+' : '';
            $points[] = "Statistika (rozvaha): rozdíl v průměru vstřelených bodů obou týmů je {$diffPrefix}" . round($diff, 1) . ".";
        }

        if ($rosterResult['opponent']['count'] === 0 && empty($previewResult['factors'])) {
            $points[] = "Pozor: o soupeři máme málo dat → predikce má nižší jistotu.";
        }

        if (!empty($previewResult['factors'])) {
            $points[] = "Model využívá aktuální statistické srovnání (rozvahu) ze serveru cz.basketball.";
        }

        return $points;
    }
}
