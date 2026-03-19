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

    public function predict(BasketballMatch $match): ?MatchPrediction
    {
        if (!$match->opponent_id) {
            return null;
        }

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

        // 5. Mutual Matches Prob (H2H)
        $mutualResult = $this->mutualMatchesCalculator->calculateMutualMatchesDelta($match);
        $mutualDelta = $mutualResult['delta'];
        $mutualProb = $this->sigmoid(($teamElo + $mutualDelta) - $oppElo);

        // Mix probabilities
        $w1 = 0.40; // Elo (reduced from 0.50)
        $w2 = 0.15; // Form (reduced from 0.20)
        $w3 = 0.15; // Roster
        $w4 = 0.15; // Preview
        $w5 = 0.15; // Mutual Matches (H2H)

        if ($rosterResult['opponent']['count'] === 0) {
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

        if ($previewDelta === 0) {
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
        // Mapujeme 0-1 na 0.05-0.95
        $finalProb = 0.05 + ($finalProb * 0.90);

        // Confidence
        $confidence = 'low';
        $teamMatchesCount = BasketballMatch::where('team_id', $match->team_id)->where('season_id', $match->season_id)->whereNotNull('score_home')->count();
        if ($teamMatchesCount >= 10 && ($rosterResult['opponent']['count'] > 0 || $previewDelta !== 0)) {
            $confidence = 'high';
        } elseif ($teamMatchesCount >= 5 || $previewDelta !== 0) {
            $confidence = 'medium';
        }

        // Explanation
        $explanation = $this->generateExplanation($match, $eloProb, $formResult, $rosterResult, $previewResult, $mutualResult);

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
                    'mutual_data' => $mutualResult,
                    'mutual_delta' => $mutualDelta,
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
            'delta' => max(-50, min(50, $delta)),
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
        // Používáme 600 místo 400 pro méně extrémní pravděpodobnosti (více konzervativní odhad pro basketbal)
        return 1 / (1 + 10 ** (-$x / 600));
    }

    private function generateExplanation(BasketballMatch $match, float $eloProb, array $formResult, array $rosterResult, array $previewResult, array $mutualResult): array
    {
        $points = [];

        // 1. Vzájemné zápasy
        if ($mutualResult['count'] > 0) {
            $wins = $mutualResult['wins'];
            $count = $mutualResult['count'];
            $delta = $mutualResult['delta'];

            $text = "Historie: z posledních {$count} vzájemných zápasů jsme vyhráli {$wins}x.";
            if ($delta < -50) {
                $text .= " Tato bilance výrazně snižuje naši šanci na výhru.";
            } elseif ($delta > 50) {
                $text .= " Tato historie nám dává psychickou výhodu.";
            }
            $points[] = $text;
        }

        // 2. Domácí prostředí
        if ($match->is_home) {
            $points[] = "Výhodu nám dává domácí prostředí (+{$this->eloCalculator->getHomeAdvantage()} Elo).";
        }

        // 3. Forma týmu (z interních dat)
        if ($formResult['count'] >= 3) {
            $winText = "{$formResult['wins']}–" . ($formResult['count'] - $formResult['wins']);
            $diffPrefix = $formResult['avg_diff'] > 0 ? '+' : '';
            $points[] = "Naše forma: posledních {$formResult['count']} zápasů {$winText}, průměrný rozdíl skóre {$diffPrefix}" . round($formResult['avg_diff'], 1) . ".";
        }

        // 4. Forma soupeře (pokud je v preview_data)
        $lastMatches = $match->metadata['last_matches'] ?? [];
        if (!empty($lastMatches['away'])) {
            $awayMatches = array_slice($lastMatches['away'], 0, 5);
            $oppWins = 0;
            $oppCount = count($awayMatches);
            $oppName = $match->opponent?->name ?? 'Soupeř';
            foreach ($awayMatches as $m) {
                $isWin = (int)$m['score_home'] > (int)$m['score_away'];
                if (str_contains(strtolower($m['team_home']), strtolower($oppName)) === false) {
                    $isWin = (int)$m['score_away'] > (int)$m['score_home'];
                }
                if ($isWin) {
                    $oppWins++;
                }
            }
            if ($oppCount >= 3) {
                $points[] = "Forma soupeře: z posledních {$oppCount} zápasů vyhráli {$oppWins}x.";
            }
        }

        // 5. Soupiska
        if ($rosterResult['team']['count'] >= 3) {
            $points[] = "Naše soupiska: top 5 hráčů drží průměrně " . round($rosterResult['team']['total'] / 5, 1) . " bodů na zápas (dle interních dat).";
        }

        // 6. Rozvaha (externí srovnání)
        if (!empty($previewResult['factors']['pts'])) {
            $diff = $previewResult['factors']['pts'];
            $diffPrefix = $diff > 0 ? '+' : '';
            $points[] = "Statistika (rozvaha): rozdíl v průměru vstřelených bodů obou týmů je {$diffPrefix}" . round($diff, 1) . ".";
        }

        // 7. Varování
        if ($rosterResult['opponent']['count'] === 0 && empty($previewResult['factors'])) {
            $points[] = "Pozor: o soupeři máme málo dat → predikce má nižší jistotu.";
        }

        if (!empty($previewResult['factors'])) {
            $points[] = "Model využívá aktuální statistické srovnání (rozvahu) ze serveru cz.basketball.";
        }

        return $points;
    }
}
