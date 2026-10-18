<?php

namespace Tests\Feature\Stats;

use App\Models\BasketballMatch;
use App\Models\CompetitionStanding;
use App\Models\ExternalTeamSeasonConfig;
use App\Models\Season;
use App\Models\StatisticRow;
use App\Models\StatisticSet;
use App\Models\Team;
use App\Services\Stats\Contracts\StatFetcherInterface;
use App\Services\Stats\DTO\NormalizedRowDTO;
use App\Services\Stats\DTO\NormalizedTableDTO;
use App\Services\Stats\Extractors\CzBasketball\CompetitionStandingExtractor;
use App\Services\Stats\Sync\CompetitionSyncService;
use App\Services\Stats\Sync\StatisticSetService;
use App\Services\Stats\TeamStatsService;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class CurrentTeamSummaryTest extends TestCase
{
    private Season $season;

    private Team $team;

    private ExternalTeamSeasonConfig $config;

    protected function setUp(): void
    {
        parent::setUp();
        $this->season = Season::create(['name' => '2026/2027', 'is_active' => true]);
        $this->team = Team::factory()->create();
        $this->config = $this->createConfig($this->team, $this->season);
    }

    private function createConfig(Team $team, Season $season, ?string $url = null): ExternalTeamSeasonConfig
    {
        return ExternalTeamSeasonConfig::create([
            'source_key' => 'czbasketball', 'team_id' => $team->id, 'season_id' => $season->id,
            'external_team_id' => '7738', 'external_season_year' => 2026,
            'team_season_url' => 'https://pbasket.cz.basketball/tym/7738?y=2026',
            'matches_list_url' => 'https://pbasket.cz.basketball/tym/7738?y=2026',
            'competition_url' => $url ?? 'https://pbasket.cz.basketball/soutez/5385?p=10472',
            'team_name_in_source' => 'Sokol Kbely E', 'is_enabled' => false,
            'metadata' => ['matches_enabled' => true, 'standings_enabled' => true,
                'official_standing' => ['gp' => 20, 'w' => 7, 'l' => 13, 'score' => '986:1211']],
        ]);
    }

    private function standing(array $attributes = []): CompetitionStanding
    {
        return CompetitionStanding::create(array_replace([
            'season_id' => $this->season->id, 'competition_url' => $this->config->competition_url,
            'team_name' => 'Sokol Kbely E', 'rank' => 10, 'gp' => 1, 'w' => 0, 'l' => 1,
            'score' => '28:73', 'points' => 1,
        ], $attributes));
    }

    private function playedMatch(): void
    {
        $match = BasketballMatch::create(['season_id' => $this->season->id, 'team_id' => null, 'scheduled_at' => '2026-10-01 20:00:00', 'is_home' => true, 'status' => 'finished', 'score_home' => 28, 'score_away' => 73]);
        $match->teams()->attach($this->team);
    }

    public function test_exact_current_standing_wins_over_legacy_metadata_and_other_rows(): void
    {
        $oldSeason = Season::create(['name' => '2025/2026']);
        $this->standing(['season_id' => $oldSeason->id, 'gp' => 20]);
        $this->standing(['competition_url' => 'https://pbasket.cz.basketball/soutez/old', 'gp' => 18]);
        $this->standing(['team_name' => 'Sokol Kbely C', 'gp' => 19]);
        $this->standing(['team_name' => ' Sokol Kbely E ']);
        $this->config->update(['team_name_in_source' => ' Sokol Kbely E ']);

        $summary = app(TeamStatsService::class)->getSeasonSummary($this->team->id, $this->season->id);
        $this->assertSame(1, $summary['gp']);
        $this->assertSame(0, $summary['wins']);
        $this->assertSame(1, $summary['losses']);
        $this->assertSame(28, $summary['pts_for']);
        $this->assertSame(73, $summary['pts_against']);
        $this->assertSame('official', $summary['source']);
    }

    public function test_independent_modes_without_exact_row_use_matches_not_historic_metadata_or_aggregate(): void
    {
        $this->playedMatch();
        app(StatisticSetService::class)->ensureBaseSets();
        $set = StatisticSet::where('slug', StatisticSetService::TEAM_SEASON_SUMMARY_SET)->firstOrFail();
        StatisticRow::create(['statistic_set_id' => $set->id, 'season_id' => $this->season->id, 'team_id' => $this->team->id, 'values' => ['gp' => 99, 'wins' => 99]]);
        $this->standing(['team_name' => 'Sokol Kbely C', 'gp' => 19]);
        foreach (['matches_enabled', 'standings_enabled'] as $flag) {
            $this->config->update(['metadata' => [$flag => true, 'official_standing' => ['gp' => 20, 'w' => 7, 'l' => 13, 'score' => '986:1211']]]);
            app(TeamStatsService::class)->invalidateCache($this->team->id, $this->season->id);
            $summary = app(TeamStatsService::class)->getSeasonSummary($this->team->id, $this->season->id);
            $this->assertSame(1, $summary['gp']);
            $this->assertSame(28, $summary['pts_for']);
            $this->assertSame('internal', $summary['source']);
        }
    }

    public function test_ambiguous_trimmed_team_rows_do_not_supply_an_official_summary(): void
    {
        $this->playedMatch();
        $this->standing();
        $this->standing(['team_name' => ' Sokol Kbely E', 'gp' => 20]);
        $summary = app(TeamStatsService::class)->getSeasonSummary($this->team->id, $this->season->id);
        $this->assertSame('internal', $summary['source']);
        $this->assertSame(1, $summary['gp']);
    }

    public function test_full_mode_keeps_legacy_summary_fallback_but_prefers_current_row(): void
    {
        $this->config->update(['is_enabled' => true]);
        $service = app(TeamStatsService::class);
        $legacy = $service->getSeasonSummary($this->team->id, $this->season->id);
        $this->assertSame(20, $legacy['gp']);
        $this->assertSame(986, $legacy['pts_for']);
        $this->standing();
        $service->invalidateCache($this->team->id, $this->season->id);
        $this->assertSame(1, $service->getSeasonSummary($this->team->id, $this->season->id)['gp']);
    }

    public function test_standings_only_sync_invalidates_only_eligible_team_and_season_caches(): void
    {
        $oldSeason = Season::create(['name' => '2025/2026']);
        $this->createConfig($this->team, $oldSeason);
        $otherTeam = Team::factory()->create();
        $this->createConfig($otherTeam, $this->season, 'https://example.test/other');
        $disabledTeam = Team::factory()->create();
        $this->createConfig($disabledTeam, $this->season)->update(['metadata' => []]);
        $this->standing();
        $service = app(TeamStatsService::class);
        $this->assertSame(1, $service->getSeasonSummary($this->team->id, $this->season->id)['gp']);
        $keys = [
            "team_stats_{$this->team->id}_{$oldSeason->id}_season_summary",
            "team_stats_{$otherTeam->id}_{$this->season->id}_season_summary",
            "team_stats_{$disabledTeam->id}_{$this->season->id}_season_summary",
        ];
        foreach ($keys as $key) {
            Cache::put($key, 'unchanged');
        }
        $this->mock(StatFetcherInterface::class)->shouldReceive('fetch')->once()->andReturn('<html></html>');
        $row = new NormalizedRowDTO(['team_name' => 'Sokol Kbely E', 'rank' => 5, 'gp' => 2, 'w' => 1, 'l' => 1, 'score' => '100:130', 'points' => 3]);
        $this->mock(CompetitionStandingExtractor::class)->shouldReceive('extract')->once()->andReturn(['data' => new NormalizedTableDTO('Pražská soutěž', [], [$row]), 'fragment_html' => 'standings']);

        app(CompetitionSyncService::class)->syncStandingsOnly($this->config->competition_url, $this->season);

        $summary = $service->getSeasonSummary($this->team->id, $this->season->id);
        $this->assertSame(2, $summary['gp']);
        $this->assertSame(100, $summary['pts_for']);
        foreach ($keys as $key) {
            $this->assertSame('unchanged', Cache::get($key));
        }
        $this->assertSame(20, $this->config->fresh()->metadata['official_standing']['gp']);
        $this->assertFalse($this->config->fresh()->is_enabled);
        $this->assertDatabaseCount('matches', 0);
    }
}
