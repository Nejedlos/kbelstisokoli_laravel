<?php

namespace Tests\Feature\Livewire\Admin;

use App\Filament\Pages\RealAttendance;
use App\Livewire\Admin\AttendanceTracker;
use App\Models\BasketballMatch;
use App\Models\PlayerProfile;
use App\Models\Season;
use App\Models\Team;
use App\Models\Training;
use App\Models\User;
use App\Services\Finance\FinanceAutomationService;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;
use Tests\TestCase;

class AttendanceTrackerTest extends TestCase
{
    protected User $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->manager = User::factory()->create();
        $this->manager->givePermissionTo('manage_attendance');
        $this->actingAs($this->manager);
    }

    public function test_checklist_shows_only_confirmed_players_and_previously_added_present_players(): void
    {
        $training = Training::factory()->create();
        $teams = Team::factory()->count(2)->create();
        $training->teams()->attach($teams->modelKeys());
        $players = collect();
        foreach (range(1, 24) as $index) {
            $player = $this->player('Player '.str_pad($index, 2, '0', STR_PAD_LEFT));
            $player->playerProfiles->first()->teams()->attach($teams->first()->id, ['is_on_roster' => false]);
            $players->push($player);
        }
        $players->first()->playerProfiles->first()->teams()->attach($teams->last()->id);
        $confirmed = $this->player('Z Confirmed Guest');
        $training->attendances()->create(['user_id' => $confirmed->id, 'planned_status' => 'confirmed']);
        $training->attendances()->create(['user_id' => $players[0]->id, 'planned_status' => 'confirmed', 'actual_status' => 'absent']);
        $training->attendances()->create(['user_id' => $players[1]->id, 'planned_status' => 'declined']);
        $training->attendances()->create(['user_id' => $players[2]->id, 'planned_status' => 'maybe']);
        $training->attendances()->create(['user_id' => $players[3]->id, 'planned_status' => 'pending']);
        $training->attendances()->create(['user_id' => $players[4]->id, 'planned_status' => 'declined', 'actual_status' => 'attended']);
        Training::factory()->create()->attendances()->create(['user_id' => $players[5]->id, 'planned_status' => 'confirmed']);

        $component = $this->tracker($training)->assertSee($confirmed->name)->assertSee($players[0]->name)->assertSee($players[4]->name);
        foreach ($players->except([0, 4]) as $player) {
            $component->assertDontSee($player->name);
        }
        $rows = $component->instance()->participants;
        $this->assertCount(3, $rows);
        $this->assertSame('confirmed', $rows->first()['planned_status']);
        $this->assertSame($players[4]->id, $rows->last()['user']->id);
        $this->assertDatabaseCount('attendances', 7);
    }

    public function test_checkbox_changes_only_actual_attendance_and_preserves_original_response(): void
    {
        $training = Training::factory()->create();
        $player = $this->player();
        $respondedAt = now()->subDay()->startOfSecond();
        $attendance = $training->attendances()->create([
            'user_id' => $player->id, 'planned_status' => 'declined',
            'note' => 'Original excuse', 'responded_at' => $respondedAt,
        ]);

        $component = $this->tracker($training)->call('setPresent', $player->id, true)->assertHasNoErrors();
        $attendance->refresh();
        $this->assertSame('attended', $attendance->actual_status);
        $this->assertSame('declined', $attendance->planned_status);
        $this->assertSame('Original excuse', $attendance->note);
        $this->assertTrue($attendance->responded_at->equalTo($respondedAt));
        $component->call('setPresent', $player->id, false)->assertHasNoErrors()->assertDontSee($player->name);
        $this->assertNull($attendance->fresh()->actual_status);
        $this->assertDatabaseCount('finance_charges', 0);
    }

    public function test_bulk_action_marks_only_confirmed_for_this_event_without_changing_responses_or_other_presence(): void
    {
        $training = Training::factory()->create();
        $records = collect(['confirmed', 'declined', 'maybe', 'pending'])->map(fn ($status) => $training->attendances()->create([
            'user_id' => $this->player()->id,
            'planned_status' => $status,
            'actual_status' => $status === 'declined' ? 'attended' : null,
            'responded_at' => now()->subDays(2)->startOfSecond(),
        ]));
        $other = Training::factory()->create()->attendances()->create([
            'user_id' => $records->first()->user_id, 'planned_status' => 'confirmed',
        ]);
        $this->tracker($training)->call('markConfirmedPresent')->assertHasNoErrors();
        foreach ($records as $record) {
            $fresh = $record->fresh();
            $this->assertSame($record->planned_status, $fresh->planned_status);
            $this->assertTrue($record->responded_at->equalTo($fresh->responded_at));
            $this->assertSame(in_array($record->planned_status, ['confirmed', 'declined']) ? 'attended' : null, $fresh->actual_status);
        }
        $this->assertNull($other->fresh()->actual_status);
        $this->assertDatabaseCount('finance_charges', 0);
    }

    public function test_search_adds_active_player_from_another_team_but_not_nonplayers(): void
    {
        $training = Training::factory()->create();
        $training->teams()->attach(Team::factory()->create());
        $guest = $this->player('Guest Player');
        $guest->playerProfiles->first()->teams()->attach(Team::factory()->create());
        User::factory()->create(['name' => 'Guest Nonplayer']);
        $inactive = $this->player('Guest Inactive');
        $inactive->playerProfiles()->update(['is_active' => false]);

        $this->tracker($training)->set('search', 'Guest')
            ->assertSee('Guest Player')->assertDontSee('Guest Nonplayer')->assertDontSee('Guest Inactive')
            ->call('selectUser', $guest->id)->assertHasNoErrors()->assertSet('search', '')->assertSee('Guest Player');
        $this->assertDatabaseHas('attendances', [
            'user_id' => $guest->id, 'attendable_id' => $training->id,
            'attendable_type' => Training::class, 'actual_status' => 'attended',
        ]);
    }

    public function test_nonplayer_id_cannot_be_injected(): void
    {
        $training = Training::factory()->create();
        $nonplayer = User::factory()->create();
        $this->tracker($training)->call('selectUser', $nonplayer->id)->assertNotFound();
        $this->assertDatabaseCount('attendances', 0);
    }

    public function test_unconfirmed_team_players_can_be_searched_added_and_removed_without_changing_rsvp(): void
    {
        $training = Training::factory()->create();
        $team = Team::factory()->create();
        $training->teams()->attach($team);
        foreach (['declined', 'maybe', 'pending', null] as $index => $status) {
            $player = $this->player('Search Player '.$index);
            $player->playerProfiles->first()->teams()->attach($team, ['is_on_roster' => false]);
            if ($status) {
                $training->attendances()->create(['user_id' => $player->id, 'planned_status' => $status]);
            }
            $component = $this->tracker($training)->assertDontSee($player->name)
                ->set('search', $player->name)->assertSee($player->name)
                ->call('selectUser', $player->id)->assertSet('search', '')->assertSee($player->name);
            $this->tracker($training)->assertSee($player->name);
            $attendance = $training->attendances()->where('user_id', $player->id)->firstOrFail();
            $this->assertSame($status ?? 'pending', $attendance->planned_status);
            $component->call('setPresent', $player->id, false)->assertDontSee($player->name)
                ->set('search', $player->name)->assertSee($player->name);
            $this->assertNull($attendance->fresh()->actual_status);
        }
    }

    public function test_confirmed_player_remains_in_checklist_after_unchecking(): void
    {
        $training = Training::factory()->create();
        $player = $this->player();
        $training->attendances()->create(['user_id' => $player->id, 'planned_status' => 'confirmed', 'actual_status' => 'attended']);
        $this->tracker($training)->call('setPresent', $player->id, false)->assertSee($player->name);
        $this->assertNull($training->attendances()->first()->actual_status);
    }

    public function test_legacy_match_shows_confirmed_player_and_keeps_other_team_players_searchable(): void
    {
        $team = Team::factory()->create();
        $match = BasketballMatch::create([
            'team_id' => $team->id, 'scheduled_at' => now(),
            'season_id' => Season::create(['name' => '2026/2027', 'is_active' => true])->id,
        ]);
        $player = $this->player('Legacy Team Player');
        $player->playerProfiles->first()->teams()->attach($team->id, ['is_on_roster' => false]);
        $this->tracker($match)->assertDontSee('Legacy Team Player')->set('search', 'Legacy')->assertSee('Legacy Team Player');
        $match->attendances()->create(['user_id' => $player->id, 'planned_status' => 'confirmed']);
        $this->tracker($match)->assertSee('Legacy Team Player');
    }

    public function test_removal_cannot_change_another_events_attendance(): void
    {
        $training = Training::factory()->create();
        $other = Training::factory()->create()->attendances()->create([
            'user_id' => $this->player()->id, 'actual_status' => 'attended',
        ]);
        $this->tracker($training)->call('removeAttendance', $other->id)->assertNotFound();
        $this->assertSame('attended', $other->fresh()->actual_status);
    }

    public function test_member_without_manage_permission_cannot_mount_tracker(): void
    {
        $this->actingAs($this->createMember());
        $this->tracker(Training::factory()->create())->assertForbidden();
    }

    public function test_permission_is_rechecked_on_follow_up_requests_and_finalization(): void
    {
        $training = Training::factory()->create();
        $component = $this->tracker($training);
        $this->manager->revokePermissionTo('manage_attendance');
        $this->mock(FinanceAutomationService::class)->shouldNotReceive('finalizeAttendance');
        $component->call('finalize')->assertForbidden();
    }

    public function test_event_identity_is_locked(): void
    {
        $component = $this->tracker(Training::factory()->create());
        $this->expectException(CannotUpdateLockedPropertyException::class);
        $component->set('attendableId', 1234);
    }

    public function test_arbitrary_model_type_is_rejected_in_tracker_and_page(): void
    {
        Livewire::test(AttendanceTracker::class, ['attendableId' => $this->manager->id, 'attendableType' => User::class])
            ->assertNotFound();
        $this->manager->assignRole('super_admin');
        Livewire::test(RealAttendance::class)->call('selectEvent', $this->manager->id, User::class)->assertNotFound();
    }

    protected function player(?string $name = null): User
    {
        $user = User::factory()->create($name ? ['name' => $name] : []);
        PlayerProfile::create(['user_id' => $user->id, 'is_active' => true]);

        return $user;
    }

    protected function tracker($event)
    {
        return Livewire::test(AttendanceTracker::class, ['attendableId' => $event->id, 'attendableType' => $event::class]);
    }
}
