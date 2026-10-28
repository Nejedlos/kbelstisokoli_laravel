<?php

namespace Tests\Feature\Prediction;

use App\Jobs\ComputeMatchPredictionJob;
use App\Models\BasketballMatch;
use App\Models\Opponent;
use App\Models\Season;
use App\Models\StatisticRow;
use App\Models\Team;
use App\Models\TeamEloRating;
use App\Services\Prediction\FormCalculator;
use App\Services\Prediction\MutualMatchesCalculator;
use App\Services\Prediction\PredictionService;
use App\Support\MatchResultHelper;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class PredictionCorrectnessTest extends TestCase
{
    private function match(array $attributes = []): BasketballMatch
    {
        Queue::fake();

        return BasketballMatch::withoutEvents(fn () => BasketballMatch::create(array_merge([
            'team_id' => Team::factory()->create()->id,
            'opponent_id' => Opponent::create(['name' => 'Opponent'])->id,
            'season_id' => Season::create(['name' => '2026/27'])->id,
            'scheduled_at' => now()->addDay(),
            'is_home' => true,
            'status' => 'planned',
            'metadata' => ['official_team_name' => 'Sokol Kbely C'],
        ], $attributes)));
    }

    public function test_missing_external_values_do_not_count_as_zero_and_missing_data_stays_neutral(): void
    {
        $match = $this->match(['metadata' => ['team_comparison' => [
            'pts_per_game' => ['home' => '-', 'away' => '80,5'],
            'fg2_pct' => ['home' => '', 'away' => '50%'],
        ]]]);
        $service = app(PredictionService::class);
        $prediction = $service->predict($match);
        $this->assertSame(0.0, (float) $prediction->factors['preview_delta']);
        $this->assertSame([], $prediction->factors['preview_data']['factors']);
        $homeProbability = $prediction->probability_win;
        $this->assertGreaterThan(0.5, $homeProbability);
        $this->assertSame($match->fresh()->scheduled_at->toDateTimeString(), $match->scheduled_at->toDateTimeString());

        $match->is_home = false;
        $away = $service->predict($match);
        $this->assertEqualsWithDelta(1, $homeProbability + $away->probability_win, 0.00001);
        $this->assertEqualsWithDelta(1, $away->probability_win + $away->probability_loss, 0.00001);
    }

    public function test_mutual_matches_use_official_identity_and_ignore_missing_or_unknown_results(): void
    {
        $match = $this->match(['metadata' => [
            'official_team_name' => 'Sokol Kbely C',
            'mutual_matches' => [
                ['team_home' => '  SOKOL Kbely C ', 'team_away' => 'Opponent', 'score_home' => '80', 'score_away' => '60'],
                ['team_home' => 'Opponent', 'team_away' => 'Sokol Kbely C', 'score_home' => '50', 'score_away' => '70'],
                ['team_home' => 'Other', 'team_away' => 'Opponent', 'score_home' => 90, 'score_away' => 20],
                ['team_home' => 'Sokol Kbely C', 'team_away' => 'Opponent', 'score_home' => '-', 'score_away' => '-'],
            ],
        ]]);
        $result = app(MutualMatchesCalculator::class)->calculateMutualMatchesDelta($match);
        $this->assertSame(2, $result['count']);
        $this->assertSame(2, $result['wins']);
        $this->assertGreaterThan(0, $result['delta']);
        $this->assertNull(MatchResultHelper::homeSideFor(['team_home' => 'Sokol Kbely C', 'team_away' => 'Opponent'], 'Sokol Kbely'));
    }

    public function test_import_does_not_overwrite_historical_snapshot_or_invent_one_after_result(): void
    {
        $match = $this->match();
        $service = app(PredictionService::class);
        $snapshot = $service->predict($match);
        $original = $snapshot->fresh()->getAttributes();
        $match->score_home = 80;
        $match->score_away = 60;
        $match->status = 'finished';
        $this->assertEquals($original, $service->predict($match)->getAttributes());
        $snapshot->delete();
        $this->assertNull($service->predict($match));
    }

    public function test_changing_match_context_removes_stale_prediction(): void
    {
        $match = $this->match();
        app(PredictionService::class)->predict($match);
        $this->assertDatabaseCount('match_predictions', 1);
        $match->update(['team_id' => null]);
        $this->assertDatabaseCount('match_predictions', 0);
    }

    public function test_away_match_opponent_form_uses_the_home_source_column(): void
    {
        $theirGames = array_fill(0, 3, [
            'team_home' => 'Opponent', 'team_away' => 'Other', 'score_home' => 80, 'score_away' => 60,
        ]);
        $ourGames = array_fill(0, 3, [
            'team_home' => 'Other', 'team_away' => 'Sokol Kbely C', 'score_home' => 80, 'score_away' => 60,
        ]);
        $match = $this->match(['is_home' => false, 'metadata' => [
            'official_team_name' => 'Sokol Kbely C',
            'last_matches' => ['home' => $theirGames, 'away' => $ourGames],
        ]]);
        $prediction = app(PredictionService::class)->predict($match);
        $this->assertContains('Forma soupeře: z posledních 3 zápasů vyhráli 3x.', $prediction->explanation_points);
    }

    public function test_score_corrections_rebuild_elo_through_the_console_observer(): void
    {
        $match = $this->match();
        $match->update(['status' => 'finished', 'score_home' => 80, 'score_away' => 60]);
        $before = TeamEloRating::where('team_key', 'team_'.$match->team_id)->value('rating');
        $this->assertGreaterThan(1500, $before);
        $match->update(['score_home' => 40, 'score_away' => 60]);
        $this->assertLessThan(1500, TeamEloRating::where('team_key', 'team_'.$match->team_id)->value('rating'));
    }

    public function test_historical_boxscore_updates_queue_future_predictions_instead_of_overwriting_history(): void
    {
        $match = $this->match();
        $row = new StatisticRow([
            'season_id' => $match->season_id, 'team_id' => $match->team_id,
        ]);
        $row->setRelation('match', null);
        app(PredictionService::class)->queueForStatistics($row);
        Queue::assertPushed(ComputeMatchPredictionJob::class, 1);
    }

    public function test_form_ignores_scheduled_zero_score_placeholders(): void
    {
        $match = $this->match();
        BasketballMatch::withoutEvents(fn () => BasketballMatch::create([
            'team_id' => $match->team_id,
            'opponent_id' => $match->opponent_id,
            'season_id' => $match->season_id,
            'scheduled_at' => now()->subDay(),
            'is_home' => true,
            'status' => 'planned',
            'score_home' => 0,
            'score_away' => 0,
        ]));
        $this->assertSame(0, app(FormCalculator::class)->calculateFormDelta($match)['count']);
    }
}
