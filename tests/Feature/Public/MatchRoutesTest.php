<?php

namespace Tests\Feature\Public;

use App\Models\BasketballMatch;
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
}
