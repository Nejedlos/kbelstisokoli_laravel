<?php

namespace Tests\Feature\Stats;

use App\Livewire\Member\Statistics\Standings;
use App\Models\CompetitionStanding;
use App\Models\ExternalTeamSeasonConfig;
use App\Models\Season;
use App\Models\Team;
use App\Services\Stats\Contracts\StatFetcherInterface;
use App\Services\Stats\StandingsConfigService;
use App\Services\Stats\Sync\CompetitionSyncService;
use App\Services\Stats\Sync\MatchSyncService;
use Livewire\Livewire;
use Mockery;
use Tests\TestCase;

class StandingsConfigServiceTest extends TestCase
{
    public function test_disabled_config_can_be_enabled_for_standings_without_enabling_other_disabled_configs(): void
    {
        $season = Season::create(['name' => '2026/2027', 'is_active' => true]);
        $enabledTeam = Team::factory()->create();
        $standingsOnlyTeam = Team::factory()->create();
        $disabledTeam = Team::factory()->create();
        $explicitlyDisabledTeam = Team::factory()->create();
        $enabledUrl = 'https://example.test/enabled';
        $standingsOnlyUrl = 'https://example.test/standings-only';

        $enabled = $this->createConfig($season, $enabledTeam, $enabledUrl, true);
        $standingsOnly = $this->createConfig($season, $standingsOnlyTeam, $standingsOnlyUrl, false, [
            'standings_enabled' => true,
            'existing_key' => 'preserved',
        ]);
        $this->createConfig($season, $disabledTeam, 'https://example.test/disabled', false);
        $this->createConfig($season, $explicitlyDisabledTeam, 'https://example.test/explicitly-disabled', false, [
            'standings_enabled' => false,
        ]);

        $service = app(StandingsConfigService::class);

        $this->assertSame([$enabled->id, $standingsOnly->id], $service->forSeason($season)->pluck('id')->all());
        $this->assertSame([$enabledUrl, $standingsOnlyUrl], $service->competitionUrls($season)->all());
    }

    public function test_eligible_configs_are_isolated_by_season_and_team(): void
    {
        $currentSeason = Season::create(['name' => '2026/2027', 'is_active' => true]);
        $otherSeason = Season::create(['name' => '2025/2026', 'is_active' => false]);
        $selectedTeam = Team::factory()->create();
        $otherTeam = Team::factory()->create();
        $selectedUrl = 'https://example.test/current-selected';

        $selected = $this->createConfig($currentSeason, $selectedTeam, $selectedUrl, false, ['standings_enabled' => true]);
        $this->createConfig($otherSeason, $selectedTeam, 'https://example.test/old-selected', false, ['standings_enabled' => true]);
        $this->createConfig($currentSeason, $otherTeam, 'https://example.test/current-other', false, ['standings_enabled' => true]);

        $service = app(StandingsConfigService::class);

        $this->assertSame([$selected->id], $service->forSeason($currentSeason, $selectedTeam->id)->pluck('id')->all());
        $this->assertSame([$selectedUrl], $service->competitionUrls($currentSeason, $selectedTeam->id)->all());
        $this->assertSame([], $service->competitionUrls(null, $selectedTeam->id)->all());
    }

    public function test_sync_all_standings_uses_standings_only_config_and_skips_unselected_disabled_config(): void
    {
        $season = Season::create(['name' => '2026/2027', 'is_active' => true]);
        $standingsOnlyTeam = Team::factory()->create();
        $disabledTeam = Team::factory()->create();
        $standingsOnlyUrl = 'https://example.test/standings-only';

        $this->createConfig($season, $standingsOnlyTeam, $standingsOnlyUrl, false, ['standings_enabled' => true]);
        $this->createConfig($season, $disabledTeam, 'https://example.test/disabled', false);

        $service = Mockery::mock(CompetitionSyncService::class, [
            Mockery::mock(StatFetcherInterface::class),
            Mockery::mock(MatchSyncService::class),
            app(StandingsConfigService::class),
        ])->makePartial();
        $service->shouldReceive('syncStandingsOnly')
            ->once()
            ->with($standingsOnlyUrl, $season);

        $this->assertSame(['synced' => 1, 'total' => 1], $service->syncAllStandings($season));
    }

    public function test_member_standings_render_for_selected_team_and_season_when_full_import_is_disabled(): void
    {
        $season = Season::create(['name' => '2026/2027', 'is_active' => true]);
        $otherSeason = Season::create(['name' => '2025/2026', 'is_active' => false]);
        $team = Team::factory()->create();
        $otherTeam = Team::factory()->create();
        $url = 'https://example.test/standings-only';

        $this->createConfig($season, $team, $url, false, ['standings_enabled' => true]);
        $this->createConfig($season, $otherTeam, 'https://example.test/other-team', false, ['standings_enabled' => true]);
        $this->createStanding($season, $url, 'Sokol Kbely C');
        $this->createStanding($otherSeason, $url, 'Stará sezóna');
        $this->createStanding($season, 'https://example.test/other-team', 'Jiný tým');

        Livewire::withQueryParams([
            'season' => $season->id,
            'team' => $team->id,
        ])->test(Standings::class)
            ->assertSet('seasonId', $season->id)
            ->assertSet('teamId', $team->id)
            ->assertSee('Sokol Kbely C')
            ->assertDontSee('Stará sezóna')
            ->assertDontSee('Jiný tým');
    }

    private function createConfig(
        Season $season,
        Team $team,
        string $url,
        bool $isEnabled,
        ?array $metadata = null,
    ): ExternalTeamSeasonConfig {
        return ExternalTeamSeasonConfig::create([
            'source_key' => 'czbasketball',
            'season_id' => $season->id,
            'team_id' => $team->id,
            'external_team_id' => (string) fake()->unique()->numberBetween(1000, 999999),
            'external_season_year' => (int) substr($season->name, 0, 4),
            'team_season_url' => $url.'/team',
            'competition_url' => $url,
            'matches_list_url' => $url.'/matches',
            'is_enabled' => $isEnabled,
            'metadata' => $metadata,
        ]);
    }

    private function createStanding(Season $season, string $url, string $teamName): CompetitionStanding
    {
        return CompetitionStanding::create([
            'season_id' => $season->id,
            'competition_url' => $url,
            'competition_name' => 'Testovací soutěž',
            'team_name' => $teamName,
            'rank' => 1,
            'gp' => 2,
            'w' => 1,
            'l' => 1,
            'score' => '100:100',
            'points' => 3,
        ]);
    }
}
