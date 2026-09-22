<?php

namespace Tests\Feature;

use App\Http\Middleware\MinifyHtmlMiddleware;
use App\Models\BasketballMatch;
use App\Models\Opponent;
use App\Models\PlayerProfile;
use App\Models\Season;
use App\Models\Team;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class MemberProgramMatchesTest extends TestCase
{
    public function test_member_sees_a_future_match_assigned_only_through_the_team_pivot(): void
    {
        Carbon::setTestNow('2026-09-22 12:00:00');
        $this->withoutMiddleware(MinifyHtmlMiddleware::class);

        $member = $this->createMember();
        $team = Team::factory()->create();
        $profile = PlayerProfile::create(['user_id' => $member->id, 'is_active' => true]);
        $profile->teams()->attach($team->id, ['is_primary_team' => true]);

        $season = Season::create(['name' => '2026/2027', 'is_active' => true]);
        $opponent = Opponent::create(['name' => 'Testovací soupeř']);
        $match = BasketballMatch::create([
            'season_id' => $season->id,
            'opponent_id' => $opponent->id,
            'scheduled_at' => now()->addWeek(),
            'is_home' => true,
            'status' => 'planned',
        ]);
        $match->teams()->attach($team->id);

        $this->actingAs($member)->withSession(['member_active_team_id' => $team->id]);

        $this->get(route('member.attendance.index'))
            ->assertOk()
            ->assertSee('Testovací soupeř');

        $this->get(route('member.dashboard'))
            ->assertOk()
            ->assertSee('Testovací soupeř');
    }
}
