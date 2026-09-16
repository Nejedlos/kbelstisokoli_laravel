<?php

namespace Tests\Feature\Prediction;

use App\Jobs\ComputeMatchPredictionJob;
use App\Models\BasketballMatch;
use App\Models\Opponent;
use App\Models\Season;
use App\Models\Team;
use App\Services\Prediction\EloService;
use App\Services\Prediction\PredictionService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class PredictionEligibilityTest extends TestCase
{
    public function test_prediction_service_skips_match_without_primary_team(): void
    {
        $match = new BasketballMatch([
            'team_id' => null,
            'opponent_id' => 1,
        ]);

        $prediction = $this->app->make(PredictionService::class)->predict($match);

        $this->assertNull($prediction);
    }

    public function test_elo_service_skips_finished_match_without_primary_team(): void
    {
        $match = new BasketballMatch([
            'team_id' => null,
            'opponent_id' => 1,
            'score_home' => 60,
            'score_away' => 50,
        ]);

        $this->app->make(EloService::class)->updateFromMatch($match);

        $this->assertDatabaseCount('team_elo_ratings', 0);
    }

    public function test_observer_does_not_dispatch_prediction_for_match_without_primary_team(): void
    {
        Queue::fake();
        [$season, $opponent] = $this->createPredictionDependencies();

        BasketballMatch::create([
            'team_id' => null,
            'season_id' => $season->id,
            'opponent_id' => $opponent->id,
            'scheduled_at' => now()->addDay(),
            'status' => 'planned',
        ]);

        Queue::assertNotPushed(ComputeMatchPredictionJob::class);
    }

    public function test_recompute_commands_skip_matches_without_prediction_context(): void
    {
        Queue::fake();
        [$season, $opponent] = $this->createPredictionDependencies();
        $team = Team::factory()->create();

        BasketballMatch::withoutEvents(function () use ($season, $opponent, $team): void {
            BasketballMatch::create([
                'team_id' => null,
                'season_id' => $season->id,
                'opponent_id' => $opponent->id,
                'scheduled_at' => now()->addDay(),
                'status' => 'planned',
                'score_home' => 60,
                'score_away' => 50,
            ]);

            BasketballMatch::create([
                'team_id' => $team->id,
                'season_id' => $season->id,
                'opponent_id' => $opponent->id,
                'scheduled_at' => now()->addDays(2),
                'status' => 'planned',
            ]);
        });

        $this->assertSame(0, Artisan::call('stats:predictions:recompute'));
        Queue::assertPushed(ComputeMatchPredictionJob::class, 1);

        $this->assertSame(0, Artisan::call('stats:elo:recompute'));
        $this->assertDatabaseCount('team_elo_ratings', 0);
    }

    /**
     * @return array{Season, Opponent}
     */
    private function createPredictionDependencies(): array
    {
        return [
            Season::create(['name' => '2026/2027', 'is_active' => true]),
            Opponent::create(['name' => 'Testovací soupeř']),
        ];
    }
}
