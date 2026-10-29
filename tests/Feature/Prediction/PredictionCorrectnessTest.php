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
        $pastDate = '20. 10. 2026';
        $theirGames = array_fill(0, 3, [
            'date' => $pastDate, 'team_home' => 'Opponent', 'team_away' => 'Other', 'score_home' => 80, 'score_away' => 60,
        ]);
        $ourGames = array_fill(0, 3, [
            'date' => $pastDate, 'team_home' => 'Other', 'team_away' => 'Sokol Kbely C', 'score_home' => 80, 'score_away' => 60,
        ]);
        $match = $this->match(['scheduled_at' => '2026-10-29 12:00:00', 'is_home' => false, 'metadata' => [
            'official_team_name' => 'Sokol Kbely C',
            'last_matches' => ['home' => $theirGames, 'away' => $ourGames],
        ]]);
        $prediction = app(PredictionService::class)->predict($match);
        $this->assertContains('Forma soupeře: z posledních 3 zápasů vyhráli 3x.', $prediction->explanation_points);
        $this->assertSame(3, $prediction->factors['form_opp']['count']);
        $this->assertLessThan(0, $prediction->factors['form_delta']);
    }

    public function test_source_results_fill_missing_own_form_and_opponent_form_changes_probability(): void
    {
        $pastDate = '20. 10. 2026';
        $ours = array_fill(0, 3, [
            'date' => $pastDate, 'team_home' => 'Sokol Kbely C', 'team_away' => 'Other', 'score_home' => 80, 'score_away' => 60,
        ]);
        $theirs = array_fill(0, 3, [
            'date' => $pastDate, 'team_home' => 'Opponent', 'team_away' => 'Other', 'score_home' => 40, 'score_away' => 60,
        ]);
        $match = $this->match(['scheduled_at' => '2026-10-29 12:00:00', 'metadata' => [
            'official_team_name' => 'Sokol Kbely C',
            'last_matches' => ['home' => $ours, 'away' => $theirs],
        ]]);

        $prediction = app(PredictionService::class)->predict($match);
        $this->assertSame(3, $prediction->factors['form_team']['count']);
        $this->assertSame('external_preview', $prediction->factors['form_team_source']);
        $this->assertSame(3, $prediction->factors['form_opp']['count']);
        $this->assertSame('external_preview', $prediction->factors['form_opp_source']);
        $this->assertSame(60, $prediction->factors['form_delta']);

        $match->metadata = ['official_team_name' => 'Sokol Kbely C'];
        $neutral = app(PredictionService::class)->predict($match);
        $this->assertGreaterThan($neutral->probability_win, $prediction->probability_win);
    }

    public function test_source_form_excludes_ambiguous_missing_duplicate_and_later_results(): void
    {
        $pastDate = '20. 10. 2026';
        $futureDate = '30. 10. 2026';
        $match = $this->match(['scheduled_at' => '2026-10-29 12:00:00', 'metadata' => [
            'official_team_name' => 'Sokol Kbely C',
            'last_matches' => ['away' => [
                ['date' => $pastDate, 'team_home' => 'Opponent', 'team_away' => 'Other', 'score_home' => 70, 'score_away' => 60, 'external_id' => '1'],
                ['date' => $pastDate, 'team_home' => 'Opponent', 'team_away' => 'Other', 'score_home' => 70, 'score_away' => 60, 'external_id' => '1'],
                ['date' => $futureDate, 'team_home' => 'Opponent', 'team_away' => 'Other', 'score_home' => 100, 'score_away' => 10],
                ['date' => '20. 08. 2026', 'team_home' => 'Opponent', 'team_away' => 'Other', 'score_home' => 100, 'score_away' => 10],
                ['date' => $pastDate, 'team_home' => 'Unknown', 'team_away' => 'Other', 'score_home' => 100, 'score_away' => 10],
                ['date' => $pastDate, 'team_home' => 'Opponent', 'team_away' => 'Other', 'score_home' => '-', 'score_away' => '-'],
                ['date' => $pastDate, 'team_home' => 'Opponent', 'team_away' => 'Other', 'score_home' => 0, 'score_away' => 0],
            ]],
        ]]);

        $form = app(FormCalculator::class)->calculateSourceFormDelta($match, 'opponent');
        $this->assertSame(1, $form['count']);
        $this->assertSame(1, $form['wins']);
        $this->assertEqualsWithDelta(10, $form['avg_diff'], 0.00001);
    }

    public function test_source_form_selects_newest_results_within_match_season(): void
    {
        $match = $this->match(['scheduled_at' => '2026-10-29 12:00:00', 'metadata' => [
            'last_matches' => ['away' => [
                ['date' => '01. 10. 2026', 'team_home' => 'Opponent', 'team_away' => 'Other', 'score_home' => 90, 'score_away' => 30],
                ['date' => '20. 10. 2026', 'team_home' => 'Opponent', 'team_away' => 'Other', 'score_home' => 40, 'score_away' => 70],
                ['date' => '15. 10. 2026', 'team_home' => 'Opponent', 'team_away' => 'Other', 'score_home' => 30, 'score_away' => 60],
            ]],
        ]]);

        $form = app(FormCalculator::class)->calculateSourceFormDelta($match, 'opponent', 2);
        $this->assertSame(2, $form['count']);
        $this->assertSame(0, $form['wins']);
        $this->assertEqualsWithDelta(-30, $form['avg_diff'], 0.00001);
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

    public function test_form_uses_only_unambiguous_pivot_historical_results(): void
    {
        $match = $this->match();
        $single = BasketballMatch::withoutEvents(fn () => BasketballMatch::create([
            'team_id' => null, 'opponent_id' => $match->opponent_id,
            'season_id' => $match->season_id, 'scheduled_at' => now()->subDays(2),
            'is_home' => true, 'status' => 'finished', 'score_home' => 70, 'score_away' => 50,
        ]));
        $single->teams()->attach($match->team_id);
        $ambiguous = BasketballMatch::withoutEvents(fn () => BasketballMatch::create([
            'team_id' => null, 'opponent_id' => $match->opponent_id,
            'season_id' => $match->season_id, 'scheduled_at' => now()->subDay(),
            'is_home' => true, 'status' => 'finished', 'score_home' => 10, 'score_away' => 90,
        ]));
        $ambiguous->teams()->attach([$match->team_id, Team::factory()->create()->id]);

        $form = app(FormCalculator::class)->calculateFormDelta($match);
        $this->assertSame(1, $form['count']);
        $this->assertSame(1, $form['wins']);
        $prediction = app(PredictionService::class)->predict($match);
        $this->assertSame(1, $prediction->factors['form_team']['count']);
        $this->assertSame('internal', $prediction->factors['form_team_source']);
    }
}
