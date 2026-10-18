<?php

namespace Tests\Feature\Stats\Sync;

use App\Exceptions\DuplicateMatchException;
use App\Models\Attendance;
use App\Models\BasketballMatch;
use App\Models\ExternalPlayerMatch;
use App\Models\Opponent;
use App\Models\Season;
use App\Models\StatisticRow;
use App\Models\StatisticSet;
use App\Models\Team;
use App\Models\User;
use App\Services\Matches\MatchWriteService;
use App\Services\Stats\Sync\MatchSyncService;
use App\Services\Stats\Sync\PlayerSyncService;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class MatchWriteServiceTest extends TestCase
{
    private Team $team;

    private Season $season;

    private Opponent $opponent;

    protected function setUp(): void
    {
        parent::setUp();
        Bus::fake();
        $this->team = Team::create(['name' => ['cs' => 'Sokol Kbely C'], 'slug' => 'muzi-c']);
        $this->season = Season::create(['name' => '2026/2027', 'is_active' => true]);
        $this->opponent = Opponent::create(['name' => 'TJ ČSA Praha']);
    }

    private function data(array $overrides = []): array
    {
        return array_replace(['team_id' => null, 'season_id' => $this->season->id, 'opponent_id' => $this->opponent->id, 'scheduled_at' => '2026-10-21 20:15:00', 'is_home' => true, 'match_type' => 'poharove', 'status' => 'planned'], $overrides);
    }

    private function imported(array $overrides = []): BasketballMatch
    {
        return app(MatchSyncService::class)->sync($this->team, $this->season, array_replace(['external_match_id' => '549796', 'scheduled_at' => '2026-10-21 20:15:00', 'home_team' => 'Sokol Kbely C', 'away_team' => 'TJ ČSA', 'status' => 'planned'], $overrides));
    }

    public function test_manual_duplicate_of_imported_fixture_is_blocked_for_alias_and_pivot_membership(): void
    {
        $existing = $this->imported();
        $existing->update(['team_id' => null]);
        try {
            app(MatchWriteService::class)->saveManual($this->data(), [$this->team->id]);
            $this->fail('Expected duplicate rejection');
        } catch (DuplicateMatchException $exception) {
            $this->assertSame($existing->id, $exception->matchId);
        }
        $this->assertDatabaseCount('matches', 1);
        $this->assertDatabaseCount('basketball_match_team', 1);
    }

    public function test_import_claims_manual_fixture_and_reschedules_without_touching_related_data(): void
    {
        $writer = app(MatchWriteService::class);
        $existing = $writer->saveManual($this->data(['notes_internal' => 'Manual note', 'location' => 'Manual gym']), [$this->team->id]);
        $user = User::factory()->create();
        $attendance = Attendance::create(['user_id' => $user->id, 'attendable_type' => BasketballMatch::class, 'attendable_id' => $existing->id, 'planned_status' => 'confirmed', 'note' => 'Keep']);
        $set = StatisticSet::create(['name' => ['cs' => 'Test'], 'slug' => 'test-preserved', 'type' => 'player', 'scope' => 'match']);
        $statistic = StatisticRow::create(['statistic_set_id' => $set->id, 'basketball_match_id' => $existing->id, 'team_id' => $this->team->id, 'season_id' => $this->season->id, 'player_id' => $user->id, 'values' => ['pts' => 42]]);
        $attendanceBefore = $attendance->fresh()->getAttributes();
        $statisticBefore = $statistic->fresh()->getAttributes();
        $claimed = $this->imported();
        $rescheduled = $this->imported(['scheduled_at' => '2026-11-03 19:00:00']);
        $this->assertSame($existing->id, $claimed->id);
        $this->assertSame($existing->id, $rescheduled->id);
        $this->assertSame($this->opponent->id, $rescheduled->opponent_id);
        $this->assertSame('poharove', $rescheduled->match_type);
        $this->assertSame('Manual gym', $rescheduled->location);
        $this->assertSame('Manual note', $rescheduled->notes_internal);
        $this->assertNull($rescheduled->team_id);
        $this->assertSame($attendanceBefore, $attendance->fresh()->getAttributes());
        $this->assertSame($statisticBefore, $statistic->fresh()->getAttributes());
        $this->assertDatabaseCount('matches', 1);
    }

    public function test_distinct_times_opponents_teams_and_return_legs_are_not_merged(): void
    {
        $first = $this->imported();
        $later = $this->imported(['external_match_id' => '549797', 'scheduled_at' => '2026-10-21 21:15:00']);
        $different = $this->imported(['external_match_id' => '549798', 'away_team' => 'TJ Sparta C']);
        $return = $this->imported(['external_match_id' => '549799', 'home_team' => 'TJ ČSA', 'away_team' => 'Sokol Kbely C']);
        $otherTeam = Team::create(['name' => ['cs' => 'Sokol Kbely E'], 'slug' => 'muzi-e']);
        $other = app(MatchWriteService::class)->saveManual($this->data(), [$otherTeam->id]);
        $this->assertCount(5, array_unique([$first->id, $later->id, $different->id, $return->id, $other->id]));
        $this->assertDatabaseCount('matches', 5);
    }

    public function test_different_external_id_at_exact_fixture_is_rejected_without_overwrite(): void
    {
        $existing = $this->imported();
        $before = $existing->fresh()->getAttributes();
        try {
            $this->imported(['external_match_id' => 'different']);
            $this->fail('Expected conflict');
        } catch (DuplicateMatchException $exception) {
            $this->assertSame($existing->id, $exception->matchId);
        }
        $this->assertSame($before, $existing->fresh()->getAttributes());
        $this->assertDatabaseCount('matches', 1);
    }

    public function test_full_import_rejects_preexisting_duplicate_external_ids_without_deleting_either(): void
    {
        $existing = $this->imported();
        $duplicate = $existing->replicate();
        $duplicate->save();
        $duplicate->teams()->attach($this->team);
        try {
            $this->imported();
            $this->fail('Expected ambiguity');
        } catch (DuplicateMatchException $exception) {
            $this->assertSame($existing->id, $exception->matchId);
        }
        $this->assertDatabaseCount('matches', 2);
        $this->assertNotNull($duplicate->fresh());
    }

    public function test_edit_checks_new_team_and_preserves_original_pivots_on_conflict(): void
    {
        $existing = $this->imported();
        $otherTeam = Team::create(['name' => ['cs' => 'Sokol Kbely E'], 'slug' => 'muzi-e']);
        $writer = app(MatchWriteService::class);
        $other = $writer->saveManual($this->data(), [$otherTeam->id]);
        try {
            $writer->saveManual($this->data(['notes_internal' => 'Should roll back']), [$this->team->id], $other);
            $this->fail('Expected edit collision');
        } catch (DuplicateMatchException $exception) {
            $this->assertSame($existing->id, $exception->matchId);
        }
        $this->assertSame([$otherTeam->id], $other->fresh()->teams()->pluck('teams.id')->all());
        $this->assertNull($other->fresh()->notes_internal);
        $same = $writer->saveManual(['notes_internal' => 'Allowed'], [$otherTeam->id], $other);
        $this->assertSame($other->id, $same->id);
        $this->assertSame('Allowed', $same->notes_internal);
    }

    public function test_invalid_team_ids_cannot_leave_a_fixture_without_its_pivot(): void
    {
        try {
            app(MatchWriteService::class)->saveManual($this->data(), [$this->team->id, 999999]);
            $this->fail('Expected invalid team');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('teams', $exception->errors());
        }
        $this->assertDatabaseCount('matches', 0);
    }

    public function test_pivot_failure_rolls_back_the_match_insert(): void
    {
        $fail = true;
        DB::listen(function (QueryExecuted $query) use (&$fail) {
            if ($fail && str_contains(strtolower($query->sql), 'insert into') && str_contains($query->sql, 'basketball_match_team')) {
                $fail = false;
                throw new \RuntimeException('Simulated pivot failure');
            }
        });
        try {
            app(MatchWriteService::class)->saveManual($this->data(), [$this->team->id]);
            $this->fail('Expected pivot failure');
        } catch (\RuntimeException $exception) {
            $this->assertSame('Simulated pivot failure', $exception->getMessage());
        } finally {
            $fail = false;
        }
        $this->assertDatabaseCount('matches', 0);
        $this->assertDatabaseCount('basketball_match_team', 0);
    }

    public function test_reschedule_into_existing_manual_fixture_is_blocked(): void
    {
        $existing = $this->imported();
        $manual = app(MatchWriteService::class)->saveManual($this->data(['scheduled_at' => '2026-11-03 19:00:00']), [$this->team->id]);
        try {
            $this->imported(['scheduled_at' => '2026-11-03 19:00:00']);
            $this->fail('Expected reschedule conflict');
        } catch (DuplicateMatchException $exception) {
            $this->assertSame($manual->id, $exception->matchId);
        }
        $this->assertSame('2026-10-21 20:15:00', $existing->fresh()->scheduled_at->toDateTimeString());
        $this->assertDatabaseCount('matches', 2);
    }

    public function test_player_import_reuses_pivot_fixture_instead_of_creating_or_claiming_another_team(): void
    {
        $manual = app(MatchWriteService::class)->saveManual($this->data(), [$this->team->id]);
        $ext = new ExternalPlayerMatch;
        $ext->forceFill(['external_match_id' => '549796', 'scheduled_at' => '2026-10-21 20:15:00']);
        // Keep this DTO in memory; the association save is intercepted to isolate match persistence.
        $ext = \Mockery::mock($ext)->makePartial();
        $ext->shouldReceive('save')->andReturn(true);
        $result = app(PlayerSyncService::class)->ensureBasketballMatch($ext, ['home_team' => 'Sokol Kbely C', 'away_team' => 'TJ ČSA', 'score' => '65:45']);
        $this->assertSame($manual->id, $result->id);
        $this->assertSame($this->opponent->id, $result->opponent_id);
        $this->assertNull($result->team_id);
        $this->assertDatabaseCount('matches', 1);
    }

    public function test_manual_team_edit_clears_removed_legacy_membership_under_the_lock(): void
    {
        $existing = $this->imported();
        $otherTeam = Team::factory()->create();
        $updated = app(MatchWriteService::class)->saveManual(['notes_internal' => 'Moved'], [$otherTeam->id], $existing);
        $this->assertNull($updated->team_id);
        $this->assertSame([$otherTeam->id], $updated->teams()->pluck('teams.id')->all());
    }

    public function test_reschedule_checks_other_teams_of_shared_fixture(): void
    {
        $existing = $this->imported();
        $otherTeam = Team::factory()->create();
        $existing->teams()->attach($otherTeam);
        $conflict = app(MatchWriteService::class)->saveManual($this->data(['scheduled_at' => '2026-11-03 19:00:00']), [$otherTeam->id]);
        try {
            $this->imported(['scheduled_at' => '2026-11-03 19:00:00']);
            $this->fail('Expected shared fixture collision');
        } catch (DuplicateMatchException $exception) {
            $this->assertSame($conflict->id, $exception->matchId);
        }
        $this->assertSame('2026-10-21 20:15:00', $existing->fresh()->scheduled_at->toDateTimeString());
    }

    public function test_similar_matches_are_read_only_advisory_and_exclude_exact_time_other_team_and_direction(): void
    {
        $existing = $this->imported();
        $writer = app(MatchWriteService::class);
        $before = $existing->fresh()->getAttributes();
        $this->assertSame([$existing->id], $writer->findSimilar($this->data(['scheduled_at' => '2026-10-21 21:00:00']), [$this->team->id])->modelKeys());
        $this->assertCount(0, $writer->findSimilar($this->data(), [$this->team->id]));
        $this->assertCount(0, $writer->findSimilar($this->data(['scheduled_at' => '2026-10-21 21:00:00', 'is_home' => false]), [$this->team->id]));
        $this->assertCount(0, $writer->findSimilar($this->data(['scheduled_at' => '2026-10-21 21:00:00']), [Team::factory()->create()->id]));
        $this->assertCount(0, $writer->findSimilar($this->data(['scheduled_at' => '2026-10-21 21:00:00']), [$this->team->id], $existing->id));
        $this->assertSame($before, $existing->fresh()->getAttributes());
    }
}
