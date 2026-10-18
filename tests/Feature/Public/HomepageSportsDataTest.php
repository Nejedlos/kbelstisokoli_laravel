<?php

namespace Tests\Feature\Public;

use App\Livewire\Public\StandingsTable;
use App\Models\BasketballMatch;
use App\Models\CompetitionStanding;
use App\Models\ExternalTeamSeasonConfig;
use App\Models\Opponent;
use App\Models\Page;
use App\Models\Season;
use App\Models\Team;
use App\Services\PublicMatchListingService;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Tests\TestCase;

class HomepageSportsDataTest extends TestCase
{
    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_homepage_shows_only_scored_matches_from_the_active_season_and_configured_teams(): void
    {
        Carbon::setTestNow('2026-10-11 12:00:00');

        Page::factory()->create(['slug' => 'home']);
        $activeSeason = Season::create(['name' => '2026/2027', 'is_active' => true]);
        $oldSeason = Season::create(['name' => '2025/2026', 'is_active' => false]);
        $configuredTeam = Team::factory()->create(['slug' => 'muzi-c']);
        $otherTeam = Team::factory()->create(['slug' => 'muzi-x']);

        $this->createConfig($configuredTeam, $activeSeason);

        $pivotMatch = $this->createScoredMatch($activeSeason, null, 'Pivot soupeř', 'finished', 65, 45);
        $pivotMatch->teams()->attach($configuredTeam);

        $legacyMatch = $this->createScoredMatch($activeSeason, $configuredTeam, 'Legacy soupeř', 'completed', 47, 54);
        $this->createScoredMatch($oldSeason, $configuredTeam, 'Starý soupeř', 'finished', 70, 60);
        $this->createScoredMatch($activeSeason, $otherTeam, 'Cizí soupeř', 'finished', 80, 70);

        $response = $this->get('/')->assertOk();

        $response
            ->assertSee(__('general.home_results_title'))
            ->assertSee('Pivot soupeř')
            ->assertSee('Legacy soupeř')
            ->assertDontSee('Starý soupeř')
            ->assertDontSee('Cizí soupeř');

        $this->get(route('public.matches.index', [
            'type' => 'latest',
            'season_id' => $activeSeason->id,
            'team_id' => $configuredTeam->id,
        ]))
            ->assertOk()
            ->assertSee('Pivot soupeř')
            ->assertSee('Legacy soupeř')
            ->assertDontSee('Cizí soupeř');

        $this->assertTrue($legacyMatch->fresh()->status === 'completed');
    }

    public function test_homepage_standings_mode_uses_only_enabled_current_season_config_urls(): void
    {
        $season = Season::create(['name' => '2026/2027', 'is_active' => true]);
        $team = Team::factory()->create(['slug' => 'muzi-e']);
        $configuredUrl = 'https://pbasket.cz.basketball/soutez/5385?p=10472';
        $this->createConfig($team, $season, $configuredUrl);

        foreach ([
            [$configuredUrl, 'Sokol Kbely B', 6],
            [$configuredUrl, 'Sokol Kbely E', 11],
            ['https://pbasket.cz.basketball/soutez/9999', 'Jiná soutěž'],
        ] as $standing) {
            [$url, $teamName, $rank] = array_pad($standing, 3, 1);
            CompetitionStanding::create([
                'season_id' => $season->id,
                'competition_url' => $url,
                'competition_name' => '3.třída B',
                'team_name' => $teamName,
                'rank' => $rank,
                'gp' => 1,
                'w' => 1,
                'l' => 0,
                'score' => '70:60',
                'points' => 2,
            ]);
        }

        Livewire::test(StandingsTable::class, ['configuredOnly' => true])
            ->assertViewHas('groupedStandings', function ($standings) use ($configuredUrl): bool {
                return $standings->keys()->all() === [$configuredUrl];
            })
            ->assertSee('11'.__('general.rank_suffix'))
            ->assertDontSee('6'.__('general.rank_suffix'));
    }

    public function test_homepage_standings_mode_includes_standings_only_config_but_excludes_other_disabled_configs(): void
    {
        $season = Season::create(['name' => '2026/2027', 'is_active' => true]);
        $team = Team::factory()->create(['slug' => 'muzi-c']);
        $disabledTeam = Team::factory()->create(['slug' => 'muzi-x']);
        $standingsOnlyUrl = 'https://pbasket.cz.basketball/soutez/5379?p=10466';
        $disabledUrl = 'https://pbasket.cz.basketball/soutez/9999';
        $standingsOnly = $this->createConfig($team, $season, $standingsOnlyUrl);
        $standingsOnly->update([
            'is_enabled' => false,
            'metadata' => ['standings_enabled' => true],
        ]);
        $disabled = $this->createConfig($disabledTeam, $season, $disabledUrl);
        $disabled->update(['is_enabled' => false]);

        foreach ([
            [$standingsOnlyUrl, 'Sokol Kbely C'],
            [$disabledUrl, 'Skrytý tým'],
        ] as [$url, $teamName]) {
            CompetitionStanding::create([
                'season_id' => $season->id,
                'competition_url' => $url,
                'competition_name' => 'Přebor B',
                'team_name' => $teamName,
                'rank' => 1,
                'gp' => 1,
                'w' => 1,
                'l' => 0,
                'score' => '70:60',
                'points' => 2,
            ]);
        }

        Livewire::test(StandingsTable::class, ['configuredOnly' => true])
            ->assertViewHas('groupedStandings', fn ($standings): bool => $standings->keys()->all() === [$standingsOnlyUrl])
            ->assertViewHas('configuredTeamNames', fn ($names): bool => $names->has($standingsOnlyUrl))
            ->assertSee('Sokol Kbely C')
            ->assertDontSee('Skrytý tým');
    }

    public function test_archived_season_is_not_used_when_no_current_season_exists(): void
    {
        $archivedSeason = Season::create(['name' => '2024/2025', 'is_active' => false]);
        $team = Team::factory()->create(['slug' => 'muzi-c']);
        $this->createScoredMatch($archivedSeason, $team, 'Archivní soupeř', 'finished', 80, 70);

        $service = app(PublicMatchListingService::class);

        $this->assertNull($service->currentSeason());
        $this->assertTrue($service->forCurrentSeason('latest')->isEmpty());

        Livewire::test(StandingsTable::class, ['configuredOnly' => true])
            ->assertSet('seasonId', null)
            ->assertViewHas('groupedStandings', fn ($standings): bool => $standings->isEmpty());
    }

    private function createConfig(Team $team, Season $season, string $competitionUrl = 'https://pbasket.cz.basketball/soutez/5379?p=10466'): ExternalTeamSeasonConfig
    {
        $isTeamE = $team->slug === 'muzi-e';

        return ExternalTeamSeasonConfig::create([
            'source_key' => 'czbasketball',
            'season_id' => $season->id,
            'team_id' => $team->id,
            'external_team_id' => $isTeamE ? '7738' : '7761',
            'external_season_year' => 2026,
            'team_season_url' => $isTeamE
                ? 'https://pbasket.cz.basketball/tym/7738?y=2026'
                : 'https://pbasket.cz.basketball/tym/7761?y=2026',
            'competition_url' => $competitionUrl,
            'matches_list_url' => $isTeamE
                ? 'https://pbasket.cz.basketball/tym/7738?y=2026'
                : 'https://pbasket.cz.basketball/tym/7761?y=2026',
            'competition_label' => $isTeamE ? '3.třída B' : 'Přebor B',
            'team_name_in_source' => $isTeamE ? 'Sokol Kbely E' : 'Sokol Kbely C',
            'is_enabled' => true,
        ]);
    }

    private function createScoredMatch(Season $season, ?Team $team, string $opponentName, string $status, int $homeScore, int $awayScore): BasketballMatch
    {
        return BasketballMatch::create([
            'team_id' => $team?->id,
            'season_id' => $season->id,
            'opponent_id' => Opponent::create(['name' => $opponentName])->id,
            'scheduled_at' => now()->subDay(),
            'is_home' => true,
            'status' => $status,
            'score_home' => $homeScore,
            'score_away' => $awayScore,
        ]);
    }
}
