<?php

namespace Tests\Feature\Public;

use App\Models\BasketballMatch;
use App\Models\MatchPrediction;
use App\Models\Opponent;
use App\Models\Season;
use App\Models\Team;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class MatchRoutesTest extends TestCase
{
    #[DataProvider('invalidIdentifiers')]
    public function test_invalid_match_identifiers_return_not_found(string $id): void
    {
        $this->get('/zapasy/'.$id)->assertNotFound();
    }

    public static function invalidIdentifiers(): array
    {
        return [
            'null image request' => ['null'],
            'undefined' => ['undefined'],
            'letters' => ['abc'],
            'mixed identifier' => ['2972abc'],
            'negative number' => ['-1'],
            'decimal' => ['1.5'],
            'integer overflow' => ['999999999999999999999999999999'],
        ];
    }

    public function test_missing_numeric_match_returns_not_found(): void
    {
        $this->get('/zapasy/2972')->assertNotFound();
    }

    public function test_existing_numeric_match_renders_its_detail(): void
    {
        $team = Team::factory()->create();
        $season = Season::create(['name' => '2026/2027', 'is_active' => true]);
        $opponent = Opponent::create(['name' => 'Testovací soupeř']);
        $match = BasketballMatch::create([
            'team_id' => $team->id,
            'season_id' => $season->id,
            'opponent_id' => $opponent->id,
            'scheduled_at' => now()->addWeek(),
            'is_home' => true,
            'status' => 'planned',
        ]);
        $match->teams()->attach($team->id);

        $this->get(route('public.matches.show', ['id' => $match->id]))
            ->assertOk()
            ->assertSee('Testovací soupeř');
    }

    public function test_undated_match_renders_its_prediction_instead_of_failing(): void
    {
        $team = Team::factory()->create();
        $season = Season::create(['name' => '2026/2027', 'is_active' => true]);
        $opponent = Opponent::create(['name' => 'Testovací soupeř']);
        $match = BasketballMatch::withoutEvents(fn () => BasketballMatch::create([
            'team_id' => $team->id,
            'season_id' => $season->id,
            'opponent_id' => $opponent->id,
            'scheduled_at' => null,
            'is_home' => true,
            'status' => 'planned',
        ]));
        MatchPrediction::create([
            'basketball_match_id' => $match->id,
            'season_id' => $season->id,
            'team_id' => $team->id,
            'computed_at' => now(),
            'expires_at' => now()->addDay(),
            'model_version' => 'elo+form+roster+preview+h2h:v3',
            'probability_win' => 0.73,
            'probability_loss' => 0.27,
            'confidence' => 'low',
            'factors' => [],
            'explanation_points' => [],
        ]);

        $this->get(route('public.matches.show', ['id' => $match->id]))
            ->assertOk()->assertSee('Termín bude upřesněn')->assertSee('Čas bude upřesněn')
            ->assertSee('73%');
        $this->followingRedirects()->get(route('public.matches.show', ['id' => $match->id, 'lang' => 'en']))
            ->assertOk()->assertSee('Date to be confirmed')->assertSee('Time to be confirmed')
            ->assertSee('73%');
    }
}
