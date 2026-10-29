<?php

namespace Tests\Feature\Stats\Sync;

use App\Exceptions\DuplicateMatchException;
use App\Jobs\Stats\SyncMatchDetailJob;
use App\Jobs\Stats\SyncTeamSeasonJob;
use App\Models\BasketballMatch;
use App\Models\ExternalEntityMapping;
use App\Models\ExternalImportRun;
use App\Models\ExternalTeamSeasonConfig;
use App\Models\Opponent;
use App\Models\OpponentMergeSuggestion;
use App\Models\Season;
use App\Models\StatisticRow;
use App\Models\StatisticSet;
use App\Models\Team;
use App\Models\User;
use App\Services\Finance\FinanceAutomationService;
use App\Services\Stats\Contracts\StatFetcherInterface;
use App\Services\Stats\Contracts\StatNormalizerInterface;
use App\Services\Stats\DTO\NormalizedRowDTO;
use App\Services\Stats\DTO\NormalizedTableDTO;
use App\Services\Stats\Extractors\CzBasketball\MatchDetailBoxscoreExtractor;
use App\Services\Stats\Extractors\CzBasketball\MatchesListExtractor;
use App\Services\Stats\Sync\CompetitionSyncService;
use App\Services\Stats\Sync\ExternalStatsSyncService;
use App\Services\Stats\Sync\MatchSyncService;
use App\Services\Stats\Sync\PlayerSyncService;
use App\Services\Stats\Sync\RosterSyncService;
use App\Services\Stats\Sync\StatisticSetService;
use App\Services\Stats\Sync\StatisticSyncService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\TestCase;

class MatchesOnlySyncTest extends TestCase
{
    private Team $team;

    private Season $season;

    private ExternalTeamSeasonConfig $config;

    protected function setUp(): void
    {
        parent::setUp();
        Bus::fake();
        Storage::fake('local');
        config(['external_sources.czbasketball.ai_only' => false]);
        $this->team = Team::create(['name' => ['cs' => 'Sokol Kbely C'], 'slug' => 'muzi-c']);
        $this->season = Season::create(['name' => '2026/2027', 'is_active' => true]);
        $this->config = ExternalTeamSeasonConfig::create([
            'source_key' => 'czbasketball', 'team_id' => $this->team->id, 'season_id' => $this->season->id,
            'external_team_id' => '7761', 'external_season_year' => 2026,
            'team_season_url' => 'https://pbasket.cz.basketball/tym/7761?y=2026',
            'matches_list_url' => 'https://pbasket.cz.basketball/tym/7761?y=2026',
            'competition_url' => 'https://pbasket.cz.basketball/soutez/5379?p=10466',
            'team_name_in_source' => 'Sokol Kbely C', 'is_enabled' => false,
            'metadata' => ['matches_enabled' => true, 'standings_enabled' => true],
        ]);
    }

    private function match(bool $pivotOnly = false): BasketballMatch
    {
        $match = BasketballMatch::create([
            'team_id' => $pivotOnly ? null : $this->team->id, 'season_id' => $this->season->id,
            'scheduled_at' => '2026-10-01 20:00:00', 'is_home' => true, 'status' => 'finished',
            'score_home' => 70, 'score_away' => 60, 'metadata' => ['external_id' => '550451'],
        ]);
        $match->teams()->attach($this->team);

        return $match;
    }

    public function test_repeated_import_updates_future_fixture_and_reconciles_unchanged_source(): void
    {
        $this->travelTo(Carbon::parse('2026-10-01 10:00:00'));
        $this->mock(StatFetcherInterface::class)->shouldReceive('fetch')->times(3)->andReturn('<html></html>');
        $row = fn ($date) => new NormalizedRowDTO([
            'external_match_id' => '550451', 'scheduled_at' => $date,
            'home_team' => 'Sokol Kbely C', 'away_team' => 'Opponent', 'status' => 'planned',
        ]);
        $this->mock(MatchesListExtractor::class)->shouldReceive('extract')->times(3)->andReturn(
            ['data' => new NormalizedTableDTO('Matches', [], [$row('2026-11-01 00:00:00')]), 'fragment_html' => 'initial'],
            ['data' => new NormalizedTableDTO('Matches', [], [$row('2026-11-02 19:30:00')]), 'fragment_html' => 'updated'],
            ['data' => new NormalizedTableDTO('Matches', [], [$row('2026-11-02 19:30:00')]), 'fragment_html' => 'updated'],
        );
        $sync = fn () => app(ExternalStatsSyncService::class)->syncTeamSeason($this->team->id, $this->season->id, ['sync_details' => false]);
        $sync();
        $match = BasketballMatch::sole();
        $sync();
        $this->assertSame('2026-11-02 19:30:00', $match->fresh()->scheduled_at->toDateTimeString());
        $match->update(['scheduled_at' => '2026-11-01 00:00:00']);
        $sync();
        $this->assertDatabaseCount('matches', 1);
        $this->assertSame($match->id, BasketballMatch::sole()->id);
        $this->assertSame('2026-11-02 19:30:00', $match->fresh()->scheduled_at->toDateTimeString());
        Bus::assertNotDispatched(SyncMatchDetailJob::class);
    }

    private function detail(array $tables): void
    {
        $main = $tables[0] ?? new NormalizedTableDTO('Boxscore', [], []);
        $main->metadata['header'] = ['home_team' => 'Sokol Kbely C', 'away_team' => 'Opponent'];
        $main->metadata['best_players'] = ['home' => ['external_id' => '99999', 'name' => 'Unknown Player', 'photo_url' => 'https://example.test/photo.jpg']];
        $this->mock(StatFetcherInterface::class)->shouldReceive('fetch')->once()->andReturn('<html></html>');
        $this->mock(MatchDetailBoxscoreExtractor::class)->shouldReceive('extract')->once()->andReturn([
            'data' => $main, 'tables' => $tables, 'fragment_html' => 'synthetic '.count($tables),
        ]);
    }

    public function test_matches_only_import_reuses_pivot_match_queues_details_and_skips_undated_rows(): void
    {
        $match = $this->match(true);
        $match->update(['match_type' => 'poharove', 'metadata' => ['season_external_match_id' => '550451']]);
        $future = $this->match(true);
        $future->update(['scheduled_at' => now()->addWeek(), 'metadata' => ['external_id' => '550452'], 'status' => 'planned']);
        $this->mock(RosterSyncService::class)->shouldNotReceive('syncWithData');
        $this->mock(StatFetcherInterface::class)->shouldReceive('fetch')->once()->with($this->config->matches_list_url, Mockery::any())->andReturn('<html></html>');
        $row = new NormalizedRowDTO(['external_match_id' => '550451', 'scheduled_at' => '2026-10-01 20:00:00', 'home_team' => 'Sokol Kbely C', 'away_team' => 'Opponent', 'score' => '71:61', 'status' => 'finished']);
        $cup = new NormalizedRowDTO(['external_match_id' => '555336', 'scheduled_at' => null, 'home_team' => 'Sokol Kbely C', 'away_team' => 'Cup team']);
        $this->mock(MatchesListExtractor::class)->shouldReceive('extract')->once()->andReturn(['data' => new NormalizedTableDTO('Matches', [], [$row, $cup]), 'fragment_html' => 'matches']);
        Cache::put("team_stats_{$this->team->id}_{$this->season->id}_match_stats", 'stale');
        $users = User::count();

        app(ExternalStatsSyncService::class)->syncTeamSeason($this->team->id, $this->season->id, ['sync_roster' => true, 'sync_competition' => true]);

        $this->assertDatabaseCount('matches', 2);
        $this->assertSame('poharove', $match->fresh()->match_type);
        Bus::assertDispatchedTimes(SyncMatchDetailJob::class, 1);
        $this->assertSame($users, User::count());
        $this->assertNull($match->fresh()->team_id);
        $this->assertSame(71, $match->fresh()->score_home);
        $this->assertFalse(Cache::has("team_stats_{$this->team->id}_{$this->season->id}_match_stats"));
        Bus::assertDispatched(SyncMatchDetailJob::class, function ($job) use ($match) {
            $id = new \ReflectionProperty($job, 'matchId');
            $options = (new \ReflectionProperty($job, 'options'))->getValue($job);

            return $id->getValue($job) === $match->id && $options['sync_team_id'] === $this->team->id;
        });
        $this->assertSame($this->config->competition_url, $this->config->fresh()->competition_url);
    }

    public function test_direct_detail_obeys_matches_only_scope_for_pivot_team_and_unknown_players(): void
    {
        $match = $this->match(true);
        $member = $this->createAdmin();
        ExternalEntityMapping::create(['source_key' => 'czbasketball', 'season_id' => $this->season->id, 'entity_type' => 'player', 'external_id' => '123', 'internal_type' => User::class, 'internal_id' => $member->id, 'identity_key' => '123']);
        $rows = [
            new NormalizedRowDTO(['pts' => 12], rowLabel: 'Known Member', metadata: ['external_player_id' => '123']),
            new NormalizedRowDTO(['pts' => 8], rowLabel: 'Unknown Player', metadata: ['external_player_id' => '99999']),
            new NormalizedRowDTO(['pts' => 20], rowLabel: 'Celkem', metadata: ['is_total' => true]),
        ];
        $this->detail([new NormalizedTableDTO('Sokol Kbely C', [], $rows)]);
        $this->mock(FinanceAutomationService::class)->shouldNotReceive('processThFines');
        $this->mock(RosterSyncService::class)->shouldNotReceive('findOrCreateUserForExternalPlayer');
        $count = User::count();

        app(ExternalStatsSyncService::class)->syncMatchDetail($match->id, ['force' => true, 'allow_player_creation' => true, 'process_finances' => true, 'skip_photos' => false]);

        $this->assertDatabaseMissing('statistic_rows', ['basketball_match_id' => $match->id, 'row_label' => 'Celkem']);
        $this->assertSame($count, User::count());
        $this->assertTrue($member->fresh()->hasRole('admin'));
        $this->assertNull($match->fresh()->team_id);
        $this->assertDatabaseHas('statistic_rows', ['basketball_match_id' => $match->id, 'team_id' => $this->team->id, 'player_id' => null, 'row_label' => 'Unknown Player']);
        $this->assertNotNull($match->fresh()->metadata['boxscore_synced_at'] ?? null);
    }

    public function test_missing_or_opponent_only_boxscore_preserves_existing_rows(): void
    {
        $match = $this->match();
        app(StatisticSetService::class)->ensureBaseSets();
        $set = StatisticSet::where('slug', StatisticSetService::MATCH_BOXSCORE_SET)->firstOrFail();
        $row = StatisticRow::create(['statistic_set_id' => $set->id, 'season_id' => $this->season->id, 'team_id' => $this->team->id, 'basketball_match_id' => $match->id, 'row_label' => 'Existing player', 'values' => ['pts' => 12]]);
        $this->detail([new NormalizedTableDTO('Opponent', [], [new NormalizedRowDTO(['pts' => 2], rowLabel: 'Other player')])]);

        app(ExternalStatsSyncService::class)->syncMatchDetail($match->id, ['force' => true]);

        $this->assertSame(['pts' => 12], $row->fresh()->values);
        $this->assertArrayNotHasKey('boxscore_synced_at', $match->fresh()->metadata);
    }

    public function test_empty_boxscore_preserves_existing_rows(): void
    {
        $match = $this->match();
        app(StatisticSetService::class)->ensureBaseSets();
        $set = StatisticSet::where('slug', StatisticSetService::MATCH_BOXSCORE_SET)->firstOrFail();
        $row = StatisticRow::create(['statistic_set_id' => $set->id, 'season_id' => $this->season->id, 'team_id' => $this->team->id, 'basketball_match_id' => $match->id, 'row_label' => 'Existing player', 'values' => ['pts' => 12]]);
        $this->detail([]);
        app(ExternalStatsSyncService::class)->syncMatchDetail($match->id, ['force' => true]);
        $this->assertNotNull($row->fresh());
        $this->assertArrayNotHasKey('boxscore_synced_at', $match->fresh()->metadata);
    }

    public function test_wrong_team_context_is_rejected_before_fetching(): void
    {
        $match = $this->match(true);
        $other = Team::factory()->create();
        $this->mock(StatFetcherInterface::class)->shouldNotReceive('fetch');
        $this->expectExceptionMessage('Match does not belong');
        app(ExternalStatsSyncService::class)->syncMatchDetail($match->id, ['sync_team_id' => $other->id]);
    }

    public function test_conservative_match_import_rejects_duplicate_ids_without_merging(): void
    {
        $this->match();
        $this->match();
        try {
            app(MatchSyncService::class)->sync($this->team, $this->season, ['external_match_id' => '550451', 'scheduled_at' => '2026-10-01 20:00:00', 'home_team' => 'Sokol Kbely C', 'away_team' => 'Opponent'], null, ['conservative_matching' => true]);
            $this->fail('Expected ambiguity rejection');
        } catch (\RuntimeException $error) {
            $this->assertInstanceOf(DuplicateMatchException::class, $error);
            $this->assertSame(__('matches.validation.import_conflict', ['id' => $error->matchId]), $error->getMessage());
        }
        $this->assertDatabaseCount('matches', 2);
    }

    public function test_full_sync_configuration_retains_existing_scope(): void
    {
        $this->config->update(['is_enabled' => true]);
        $this->assertFalse($this->config->isMatchesOnly());
        $service = Mockery::mock(ExternalStatsSyncService::class, [app(StatFetcherInterface::class), app(RosterSyncService::class), app(MatchSyncService::class), app(StatisticSyncService::class), app(StatNormalizerInterface::class), app(PlayerSyncService::class), app(CompetitionSyncService::class)])->makePartial()->shouldAllowMockingProtectedMethods();
        $service->shouldReceive('syncRoster')->once();
        $service->shouldReceive('syncMatchesList')->once()->withArgs(fn ($team, $season, $config, $options) => ! isset($options['allow_player_creation']));
        $service->syncTeamSeason($this->team->id, $this->season->id, ['sync_competition' => false]);
    }

    public function test_active_season_cli_alias_uses_current_season(): void
    {
        $this->mock(ExternalStatsSyncService::class)->shouldReceive('syncTeamSeason')->once()->with($this->team->id, $this->season->id, Mockery::type('array'));
        $this->artisan('stats:sync-team-season', ['teamSlug' => 'muzi-c', 'seasonNameOrId' => 'active'])->assertSuccessful();
    }

    public function test_replacement_rolls_back_all_rows_when_a_later_table_fails(): void
    {
        $match = $this->match();
        app(StatisticSetService::class)->ensureBaseSets();
        $set = StatisticSet::where('slug', StatisticSetService::MATCH_BOXSCORE_SET)->firstOrFail();
        $otherTeam = Team::factory()->create();
        $old = StatisticRow::create(['statistic_set_id' => $set->id, 'season_id' => $this->season->id, 'team_id' => $this->team->id, 'basketball_match_id' => $match->id, 'row_label' => 'Original', 'values' => ['pts' => 12]]);
        $other = StatisticRow::create(['statistic_set_id' => $set->id, 'season_id' => $this->season->id, 'team_id' => $otherTeam->id, 'basketball_match_id' => $match->id, 'row_label' => 'Other team', 'values' => ['pts' => 7]]);
        $ownTable = new NormalizedTableDTO('Sokol Kbely C', [], [new NormalizedRowDTO(['pts' => 9], rowLabel: 'Replacement')]);
        $opponentTable = new NormalizedTableDTO('Opponent', [], [new NormalizedRowDTO(['pts' => 8], rowLabel: 'Opponent player')]);
        $this->detail([$ownTable, $opponentTable]);
        $stats = Mockery::mock(StatisticSyncService::class, [app(StatisticSetService::class)])->makePartial();
        $stats->shouldReceive('syncMatchBoxscore')->withArgs(fn ($match, $table, $run, $options) => $table->name === 'Sokol Kbely C')->once()->passthru();
        $stats->shouldReceive('syncMatchBoxscore')->withArgs(fn ($match, $table, $run, $options) => $table->name === 'Opponent')->once()->andThrow(new \RuntimeException('Injected failure'));
        $this->app->instance(StatisticSyncService::class, $stats);

        try {
            app(ExternalStatsSyncService::class)->syncMatchDetail($match->id, ['force' => true]);
            $this->fail('Expected replacement failure');
        } catch (\RuntimeException $error) {
            $this->assertSame('Injected failure', $error->getMessage());
        }
        $this->assertSame(['pts' => 12], $old->fresh()->values);
        $this->assertSame(['pts' => 7], $other->fresh()->values);
        $this->assertDatabaseMissing('statistic_rows', ['basketball_match_id' => $match->id, 'row_label' => 'Replacement']);
        $this->assertArrayNotHasKey('boxscore_synced_at', $match->fresh()->metadata);
    }

    public function test_detail_reschedule_conflict_preserves_boxscore_and_both_fixtures(): void
    {
        $opponent = Opponent::create(['name' => 'Opponent']);
        $match = $this->match(true);
        $match->update(['opponent_id' => $opponent->id]);
        $conflict = $this->match(true);
        $conflict->update(['opponent_id' => $opponent->id, 'scheduled_at' => '2026-10-02 20:00:00', 'metadata' => []]);
        app(StatisticSetService::class)->ensureBaseSets();
        $set = StatisticSet::where('slug', StatisticSetService::MATCH_BOXSCORE_SET)->firstOrFail();
        $old = StatisticRow::create(['statistic_set_id' => $set->id, 'season_id' => $this->season->id, 'team_id' => $this->team->id, 'basketball_match_id' => $match->id, 'row_label' => 'Original', 'values' => ['pts' => 12]]);
        $table = new NormalizedTableDTO('Sokol Kbely C', [], [new NormalizedRowDTO(['pts' => 9], rowLabel: 'Replacement')]);
        $this->detail([$table]);
        $table->metadata['header']['scheduled_at'] = '2026-10-02 20:00:00';
        try {
            app(ExternalStatsSyncService::class)->syncMatchDetail($match->id, ['force' => true]);
            $this->fail('Expected detail collision');
        } catch (DuplicateMatchException $exception) {
            $this->assertSame($conflict->id, $exception->matchId);
        }
        $this->assertSame(['pts' => 12], $old->fresh()->values);
        $this->assertSame('2026-10-01 20:00:00', $match->fresh()->scheduled_at->toDateTimeString());
        $this->assertArrayNotHasKey('detail_synced_at', $match->fresh()->metadata);
        $this->assertDatabaseCount('matches', 2);
        $this->assertDatabaseMissing('statistic_rows', ['row_label' => 'Replacement']);
    }

    public function test_batch_import_includes_matches_only_configuration(): void
    {
        config(['external_sources.enabled' => true, 'external_sources.czbasketball.teams' => []]);
        $this->artisan('stats:import', ['--queue' => true])->assertSuccessful();
        Bus::assertDispatched(SyncTeamSeasonJob::class, function ($job) {
            return (new \ReflectionProperty($job, 'teamId'))->getValue($job) === $this->team->id;
        });
    }

    public function test_foreign_team_source_row_is_rejected_before_mutation(): void
    {
        $opponents = Opponent::count();
        try {
            app(MatchSyncService::class)->sync($this->team, $this->season, ['external_match_id' => '550451', 'scheduled_at' => '2026-10-01 20:00:00', 'home_team' => 'Other home', 'away_team' => 'Other away'], null, ['conservative_matching' => true]);
            $this->fail('Expected source team rejection');
        } catch (\RuntimeException $error) {
            $this->assertStringContainsString('exactly one configured team', $error->getMessage());
        }
        $this->assertSame($opponents, Opponent::count());
        $this->assertDatabaseCount('matches', 0);
    }

    public function test_disabled_configuration_does_not_import_or_fetch(): void
    {
        $this->config->update(['metadata' => ['standings_enabled' => true]]);
        $this->mock(StatFetcherInterface::class)->shouldNotReceive('fetch');
        app(ExternalStatsSyncService::class)->syncTeamSeason($this->team->id, $this->season->id);
        $this->assertNull($this->config->fresh()->last_synced_at);
    }

    public function test_exact_legacy_opponent_alias_preserves_match_and_opponent_and_is_idempotent(): void
    {
        foreach ([['TJ ČSA Praha', 'TJ ČSA'], ['Slavia Tygři Praha C', 'Slavia Tygři Praha C'], ['TJ Slovan Bohnice A', 'Slovan Bohnice A']] as $index => [$storedName, $sourceName]) {
            $opponent = Opponent::create(['name' => $storedName]);
            $date = now()->setDate(2026, 10, 21)->setTime(20, 15)->addDays($index);
            $match = $this->match(true);
            $match->update(['opponent_id' => $opponent->id, 'scheduled_at' => $date, 'metadata' => [], 'match_type' => 'poharove']);
            $row = ['external_match_id' => (string) (549796 + $index), 'scheduled_at' => $date->toDateTimeString(), 'home_team' => 'Sokol Kbely C', 'away_team' => $sourceName];
            for ($attempt = 0; $attempt < 2; $attempt++) {
                $result = app(MatchSyncService::class)->sync($this->team, $this->season, $row, null, ['conservative_matching' => true]);
                $this->assertSame($match->id, $result->id);
                $this->assertSame($opponent->id, $result->opponent_id);
                $this->assertNull($result->team_id);
                $this->assertSame('poharove', $result->match_type);
                $this->assertNotNull($opponent->fresh());
            }
        }
        $this->assertDatabaseCount('matches', 3);
    }

    public function test_legacy_alias_does_not_merge_different_identity_time_direction_empty_alias_or_rejected_pair(): void
    {
        $cases = [
            ['TJ ČSA Praha', 'TJ ČSA', '2026-10-01 21:00:00', true, false],
            ['TJ ČSA Praha', 'TJ ČSA', '2026-10-01 20:00:00', false, false],
            ['TJ ČSA Praha C', 'TJ ČSA E', '2026-10-01 20:00:00', true, false],
            ['TJ Slavia Praha', 'Sparta Praha', '2026-10-01 20:00:00', true, false],
            ['TJ ČSA U19', 'ČSA U23', '2026-10-01 20:00:00', true, false],
            ['TJ Praha', 'BK Praha', '2026-10-01 20:00:00', true, false],
            ['TJ ČSA Praha', 'TJ ČSA', '2026-10-01 20:00:00', true, true],
        ];
        foreach ($cases as [$storedName, $sourceName, $sourceDate, $home, $rejected]) {
            $stored = Opponent::firstOrCreate(['name' => $storedName]);
            $source = Opponent::firstOrCreate(['name' => $sourceName]);
            $match = $this->match(true);
            $match->update(['opponent_id' => $stored->id, 'metadata' => []]);
            if ($rejected) {
                OpponentMergeSuggestion::create(['source_opponent_id' => $stored->id, 'target_opponent_id' => $source->id, 'similarity' => 1, 'status' => 'rejected']);
            }
            $created = app(MatchSyncService::class)->sync($this->team, $this->season, ['external_match_id' => '549796', 'scheduled_at' => $sourceDate, 'home_team' => $home ? 'Sokol Kbely C' : $sourceName, 'away_team' => $home ? $sourceName : 'Sokol Kbely C'], null, ['conservative_matching' => true]);
            $this->assertNotSame($match->id, $created->id);
            $created->teams()->detach();
            $created->delete();
            $this->assertSame([], $match->fresh()->metadata);
            $this->assertSame($stored->id, $match->fresh()->opponent_id);
            $match->teams()->detach();
            $match->delete();
        }
    }

    public function test_alias_on_a_different_day_does_not_reuse_the_legacy_match(): void
    {
        $opponent = Opponent::create(['name' => 'TJ ČSA Praha']);
        $old = $this->match(true);
        $old->update(['opponent_id' => $opponent->id, 'metadata' => []]);
        $new = app(MatchSyncService::class)->sync($this->team, $this->season, ['external_match_id' => '549796', 'scheduled_at' => '2026-10-02 20:00:00', 'home_team' => 'Sokol Kbely C', 'away_team' => 'TJ ČSA'], null, ['conservative_matching' => true]);
        $this->assertNotSame($old->id, $new->id);
        $this->assertSame([], $old->fresh()->metadata);
        $this->assertDatabaseCount('matches', 2);
    }

    public function test_diagnostic_failure_cannot_mask_the_original_failed_import(): void
    {
        $original = $this->match();
        $this->match();
        $this->mock(StatFetcherInterface::class)->shouldReceive('fetch')->once()->andReturn('<html></html>');
        $row = new NormalizedRowDTO(['external_match_id' => '550451', 'scheduled_at' => '2026-10-01 20:00:00', 'home_team' => 'Sokol Kbely C', 'away_team' => 'Opponent']);
        $this->mock(MatchesListExtractor::class)->shouldReceive('extract')->once()->andReturn(['data' => new NormalizedTableDTO('Matches', [], [$row]), 'fragment_html' => 'matches']);
        $this->mock(StatNormalizerInterface::class)->shouldReceive('sanitizeHtml')->once()->andThrow(new \Error('Snapshot unavailable'));
        try {
            app(ExternalStatsSyncService::class)->syncTeamSeason($this->team->id, $this->season->id);
            $this->fail('Expected import failure');
        } catch (\Exception $error) {
            $this->assertStringContainsString(__('matches.validation.import_conflict', ['id' => $original->id]), $error->getMessage());
            $this->assertStringNotContainsString('Snapshot unavailable', $error->getMessage());
        }
        $run = ExternalImportRun::where('run_type', 'matches_list')->firstOrFail();
        $this->assertSame('failed', $run->status);
        $this->assertStringContainsString(__('matches.validation.import_conflict', ['id' => $original->id]), $run->error_summary);
    }
}
