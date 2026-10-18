<?php

namespace Tests\Feature\Filament\Pages;

use App\Filament\Resources\BasketballMatches\Pages\CreateBasketballMatch;
use App\Filament\Resources\BasketballMatches\Pages\EditBasketballMatch;
use App\Models\Attendance;
use App\Models\BasketballMatch;
use App\Models\Opponent;
use App\Models\Season;
use App\Models\StatisticRow;
use App\Models\StatisticSet;
use App\Models\Team;
use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;
use Tests\TestCase;

class BasketballMatchDuplicateProtectionTest extends TestCase
{
    private Team $team;

    private Season $season;

    private Opponent $opponent;

    private User $manager;

    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();
        $this->manager = User::factory()->create(['is_active' => true]);
        $this->manager->givePermissionTo(['access_admin', 'manage_matches']);
        $this->actingAs($this->manager);
        $this->season = Season::create(['name' => '2026/2027', 'is_active' => true]);
        $this->team = Team::factory()->create(['category' => 'youth']);
        $this->opponent = Opponent::create(['name' => 'TJ ČSA Praha']);
    }

    private function attributes(array $overrides = []): array
    {
        return array_replace([
            'match_type' => 'mistrovske', 'season_id' => $this->season->id,
            'opponent_id' => $this->opponent->id, 'scheduled_at' => '2026-11-04 20:15:00',
            'is_home' => true, 'status' => 'planned',
        ], $overrides);
    }

    private function createForm(array $overrides = [], ?Team $team = null)
    {
        $component = Livewire::test(CreateBasketballMatch::class);
        foreach ($this->attributes($overrides) as $key => $value) {
            $component->set('data.'.$key, $value);
        }

        return $component->set('data.teams', [($team ?? $this->team)->id]);
    }

    private function existing(bool $legacy = false): BasketballMatch
    {
        $match = BasketballMatch::create($this->attributes([
            'team_id' => $legacy ? $this->team->id : null,
            'metadata' => ['external_id' => '549807'],
            'notes_internal' => 'Pokyny trenéra',
        ]));
        if (! $legacy) {
            $match->teams()->attach($this->team);
        }

        return $match;
    }

    public function test_manual_create_blocks_imported_legacy_fixture_and_preserves_related_data(): void
    {
        $match = $this->existing(true);
        $attendance = $match->attendances()->create(['user_id' => $this->manager->id, 'planned_status' => 'confirmed', 'actual_status' => 'attended', 'note' => 'Přijdu', 'responded_at' => now()]);
        $set = StatisticSet::create(['name' => ['cs' => 'Zápis'], 'slug' => 'duplicate-test', 'type' => 'custom']);
        $stat = StatisticRow::create(['statistic_set_id' => $set->id, 'team_id' => $this->team->id, 'season_id' => $this->season->id, 'basketball_match_id' => $match->id, 'row_label' => 'Hráč', 'values' => ['pts' => 17]]);
        $before = [$match->fresh()->getRawOriginal(), $attendance->fresh()->getRawOriginal(), $stat->fresh()->getRawOriginal()];

        $this->createForm()->call('create')->assertHasErrors(['data.scheduled_at']);

        $this->assertDatabaseCount('matches', 1);
        $this->assertSame($before, [$match->fresh()->getRawOriginal(), $attendance->fresh()->getRawOriginal(), $stat->fresh()->getRawOriginal()]);
        $this->assertSame($match->id, Attendance::sole()->attendable_id);
        Notification::assertNotified(__('matches.duplicates.title'));
    }

    public function test_manual_create_blocks_pivot_fixture_with_an_exact_opponent_alias(): void
    {
        $this->existing();
        $alias = Opponent::create(['name' => 'TJ ČSA']);
        $this->createForm(['opponent_id' => $alias->id])->call('create')->assertHasErrors(['data.scheduled_at']);
        $this->assertDatabaseCount('matches', 1);
    }

    public function test_failed_edit_does_not_change_teams_before_duplicate_check(): void
    {
        $this->existing();
        $other = Team::factory()->create(['category' => 'youth']);
        $edited = BasketballMatch::create($this->attributes(['notes_internal' => 'Zachovat']));
        $edited->teams()->attach($other);
        $before = $edited->fresh()->getRawOriginal();

        Livewire::test(EditBasketballMatch::class, ['record' => $edited->getRouteKey()])
            ->set('data.teams', [$this->team->id])
            ->set('data.notes_internal', 'Nesmí se uložit')
            ->call('save')->assertHasErrors(['data.scheduled_at']);

        $this->assertSame($before, $edited->fresh()->getRawOriginal());
        $this->assertSame([$other->id], $edited->fresh()->teams->modelKeys());
        $this->assertDatabaseCount('matches', 2);
    }

    public function test_unchanged_edit_excludes_itself_and_successful_edit_saves_teams_atomically(): void
    {
        $match = $this->existing();
        $extra = Team::factory()->create(['category' => 'youth']);
        Livewire::test(EditBasketballMatch::class, ['record' => $match->getRouteKey()])
            ->set('data.teams', [$this->team->id, $extra->id])
            ->set('data.notes_internal', 'Aktualizované pokyny')
            ->call('save')->assertHasNoErrors();
        $this->assertDatabaseCount('matches', 1);
        $this->assertEqualsCanonicalizing([$this->team->id, $extra->id], $match->fresh()->teams->modelKeys());
        $this->assertSame('Aktualizované pokyny', $match->fresh()->notes_internal);
    }

    public function test_different_time_opponent_or_team_can_be_saved(): void
    {
        $this->existing();
        $otherOpponent = Opponent::create(['name' => 'Sparta Praha']);
        $otherTeam = Team::factory()->create(['category' => 'youth']);
        $this->createForm(['scheduled_at' => '2026-11-04 22:15:00'])->call('create')->assertHasNoErrors();
        $this->createForm(['opponent_id' => $otherOpponent->id])->call('create')->assertHasNoErrors();
        $this->createForm([], $otherTeam)->call('create')->assertHasNoErrors();
        $this->assertDatabaseCount('matches', 4);
    }

    public function test_invalid_team_ids_cannot_create_or_reattach_a_match(): void
    {
        $this->createForm()->set('data.teams', [999999])->call('create')->assertHasErrors();
        $this->assertDatabaseCount('matches', 0);
    }

    public function test_similar_time_warning_links_to_existing_match_without_blocking_a_separate_fixture(): void
    {
        $match = $this->existing();
        $component = $this->createForm(['scheduled_at' => '2026-11-04 22:15:00']);
        $component->assertSee(__('matches.duplicates.similar'))
            ->assertSee('/basketball-matches/'.$match->id.'/edit', false);
        $component->call('create')->assertHasNoErrors();
        $this->assertDatabaseCount('matches', 2);
    }

    public function test_legacy_team_is_preselected_and_removing_it_does_not_leave_hidden_membership(): void
    {
        $match = $this->existing(true);
        $other = Team::factory()->create(['category' => 'youth']);
        Livewire::test(EditBasketballMatch::class, ['record' => $match->getRouteKey()])
            ->assertSet('data.teams', [(string) $this->team->id])
            ->set('data.teams', [$other->id])
            ->call('save')->assertHasNoErrors();
        $this->assertNull($match->fresh()->team_id);
        $this->assertSame([$other->id], $match->fresh()->teams->modelKeys());
    }

    public function test_scheduled_duplicate_audit_does_not_merge_or_delete_existing_records(): void
    {
        $first = $this->existing(true);
        $second = $this->existing(true);
        $attendance = $second->attendances()->create(['user_id' => $this->manager->id, 'planned_status' => 'confirmed', 'note' => 'Zachovat audit']);
        $before = $attendance->fresh()->getRawOriginal();
        $event = collect(app(Schedule::class)->events())->first(fn ($event) => $event->description === 'match-duplicates-audit');
        $this->assertNotNull($event);
        $event->run(app());
        $this->assertDatabaseHas('matches', ['id' => $first->id]);
        $this->assertDatabaseHas('matches', ['id' => $second->id]);
        $this->assertSame($before, $attendance->fresh()->getRawOriginal());
    }
}
