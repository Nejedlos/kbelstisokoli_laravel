<?php

namespace Tests\Feature\Prediction;

use App\Models\BasketballMatch;
use App\Models\Opponent;
use App\Models\Season;
use App\Models\Team;
use App\Models\TeamEloRating;
use App\Services\Prediction\EloCalculator;
use App\Services\Prediction\EloService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class EloRecomputeTest extends TestCase
{
    private Season $season;

    private Team $team;

    private Opponent $opponent;

    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();
        $this->season = Season::create(['name' => '2026/2027', 'is_active' => true]);
        $this->team = Team::factory()->create();
        $this->opponent = Opponent::create(['name' => 'Společný soupeř']);
    }

    public function test_repeated_rebuilds_start_from_baseline_and_preserve_last_match_time(): void
    {
        $this->createMatch(['scheduled_at' => '2026-10-02 12:00:00']);
        $this->createMatch(['scheduled_at' => '2026-10-01 12:00:00', 'score_home' => 40, 'score_away' => 50]);

        $service = app(EloService::class);
        $this->assertSame(2, $service->rebuildSeason($this->season->id));
        $first = $this->snapshot();
        TeamEloRating::query()->update(['rating' => 9999]);
        $this->assertSame(2, $service->rebuildSeason($this->season->id));
        $this->assertSame($first, $this->snapshot());
        $this->assertSame('2026-10-02 12:00:00', TeamEloRating::first()->last_match_at->toDateTimeString());
    }

    public function test_score_correction_status_reversal_and_deletion_remove_previous_contribution(): void
    {
        $match = $this->createMatch();
        $service = app(EloService::class);
        $service->updateFromMatch($match);
        $first = $this->snapshot();
        $this->assertGreaterThan(1500, $first[TeamEloRating::getInternalTeamKey($this->team->id)]);
        $service->updateFromMatch($match);
        $this->assertSame($first, $this->snapshot());

        $match->updateQuietly(['score_home' => 40, 'score_away' => 60]);
        $service->updateFromMatch($match);
        $this->assertLessThan(1500, $this->snapshot()[TeamEloRating::getInternalTeamKey($this->team->id)]);

        $match->updateQuietly(['status' => 'planned']);
        $service->updateFromMatch($match);
        $this->assertDatabaseCount('team_elo_ratings', 0);

        $match->updateQuietly(['status' => 'completed']);
        $service->updateFromMatch($match);
        $this->assertDatabaseCount('team_elo_ratings', 2);
        $match->deleteQuietly();
        $service->updateFromMatch($match);
        $this->assertDatabaseCount('team_elo_ratings', 0);
    }

    public function test_completed_zero_score_win_counts_but_placeholders_and_unfinished_results_do_not(): void
    {
        $this->createMatch(['score_home' => 20, 'score_away' => 0]);
        $this->createMatch(['score_home' => 0, 'score_away' => 0]);
        $this->createMatch(['status' => 'planned', 'score_home' => 100, 'score_away' => 50]);
        $this->createMatch(['status' => 'cancelled', 'score_home' => 100, 'score_away' => 50]);
        $this->createMatch(['score_home' => null]);

        $this->assertSame(1, app(EloService::class)->rebuildSeason($this->season->id));
        $snapshot = $this->snapshot();
        $this->assertGreaterThan(1500, $snapshot[TeamEloRating::getInternalTeamKey($this->team->id)]);
        $this->assertLessThan(1500, $snapshot[TeamEloRating::getOpponentKey($this->opponent->id)]);
    }

    public function test_team_filter_rebuilds_shared_opponent_history_for_the_entire_season(): void
    {
        $otherTeam = Team::factory()->create();
        $this->createMatch(['scheduled_at' => '2026-10-01 12:00:00']);
        $this->createMatch(['team_id' => $otherTeam->id, 'scheduled_at' => '2026-10-02 12:00:00', 'score_home' => 40, 'score_away' => 60]);
        $this->createMatch(['scheduled_at' => '2026-10-03 12:00:00']);

        $this->assertSame(0, Artisan::call('stats:elo:recompute', ['season' => $this->season->id]));
        $expected = $this->snapshot();
        TeamEloRating::query()->update(['rating' => 3000]);

        $arguments = ['season' => $this->season->id, 'team' => $this->team->id];
        $this->assertSame(0, Artisan::call('stats:elo:recompute', $arguments));
        $this->assertStringContainsString('each selected season is rebuilt in full', Artisan::output());
        $this->assertSame($expected, $this->snapshot());
        $this->assertCount(3, $expected);
        $this->assertSame(0, Artisan::call('stats:elo:recompute', $arguments));
        $this->assertSame($expected, $this->snapshot());
    }

    public function test_command_clears_stale_ratings_when_no_eligible_results_remain(): void
    {
        TeamEloRating::create([
            'season_id' => $this->season->id,
            'team_key' => TeamEloRating::getInternalTeamKey($this->team->id),
            'rating' => 1600,
        ]);

        $this->assertSame(0, Artisan::call('stats:elo:recompute', ['season' => $this->season->id, 'team' => $this->team->id]));
        $this->assertDatabaseCount('team_elo_ratings', 0);
    }

    public function test_replay_passes_each_internal_teams_prior_game_count_and_breaks_time_ties_by_id(): void
    {
        $otherTeam = Team::factory()->create();
        // Insert the later result first to ensure ordering uses match time first.
        $this->createMatch(['team_id' => $otherTeam->id, 'scheduled_at' => '2026-10-20 12:00:00']);
        for ($index = 0; $index < 12; $index++) {
            $this->createMatch([
                'scheduled_at' => '2026-10-01 12:00:00',
                'score_home' => $index % 2 === 0 ? 60 : 40,
                'score_away' => 50,
            ]);
        }

        $calculator = new class extends EloCalculator
        {
            public array $counts = [];

            public array $outcomes = [];

            public function calculateNewRatings(float $eloTeam, float $eloOpponent, float $outcome, int $scoreDiff, ?bool $isHome = null, int $gamesPlayed = 0): array
            {
                $this->counts[] = $gamesPlayed;
                $this->outcomes[] = $outcome;

                return parent::calculateNewRatings($eloTeam, $eloOpponent, $outcome, $scoreDiff, $isHome, $gamesPlayed);
            }
        };

        (new EloService($calculator))->rebuildSeason($this->season->id);
        $this->assertSame([...range(0, 11), 0], $calculator->counts);
        $this->assertSame([1.0, 0.0, 1.0, 0.0, 1.0, 0.0, 1.0, 0.0, 1.0, 0.0, 1.0, 0.0, 1.0], $calculator->outcomes);
    }

    private function createMatch(array $attributes = []): BasketballMatch
    {
        return BasketballMatch::withoutEvents(fn () => BasketballMatch::create(array_merge([
            'season_id' => $this->season->id,
            'team_id' => $this->team->id,
            'opponent_id' => $this->opponent->id,
            'scheduled_at' => '2026-10-01 12:00:00',
            'status' => 'completed',
            'is_home' => true,
            'score_home' => 60,
            'score_away' => 50,
        ], $attributes)));
    }

    private function snapshot(): array
    {
        return TeamEloRating::where('season_id', $this->season->id)
            ->orderBy('team_key')->pluck('rating', 'team_key')->all();
    }
}
