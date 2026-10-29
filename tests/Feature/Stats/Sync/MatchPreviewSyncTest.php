<?php

namespace Tests\Feature\Stats\Sync;

use App\Jobs\ComputeMatchPredictionJob;
use App\Models\BasketballMatch;
use App\Models\ExternalTeamSeasonConfig;
use App\Models\Opponent;
use App\Models\Season;
use App\Models\Team;
use App\Services\Stats\Contracts\StatFetcherInterface;
use App\Services\Stats\Sync\MatchPreviewSyncService;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class MatchPreviewSyncTest extends TestCase
{
    private BasketballMatch $match;

    private ExternalTeamSeasonConfig $config;

    protected function setUp(): void
    {
        parent::setUp();
        Bus::fake();
        $team = Team::create(['name' => ['cs' => 'Kbely E'], 'slug' => 'kbely-e']);
        $season = Season::create(['name' => '2026/2027', 'is_active' => true]);
        $opponent = Opponent::create(['name' => 'Opponent']);
        $this->config = ExternalTeamSeasonConfig::create([
            'source_key' => 'czbasketball', 'team_id' => $team->id, 'season_id' => $season->id,
            'external_team_id' => '1', 'external_season_year' => 2026, 'team_season_url' => 'https://pbasket.cz.basketball/tym/1',
            'team_name_in_source' => 'Kbely E', 'matches_list_url' => 'https://pbasket.cz.basketball/tym/1',
            'is_enabled' => false, 'metadata' => ['matches_enabled' => true],
        ]);
        $this->match = BasketballMatch::create([
            'team_id' => $team->id, 'opponent_id' => $opponent->id, 'season_id' => $season->id,
            'scheduled_at' => now()->addDays(5), 'is_home' => true, 'status' => 'planned',
            'metadata' => ['external_id' => '12345', 'other' => 'preserved', 'team_comparison' => ['stale' => true]],
        ]);
        Bus::fake();
    }

    public function test_verified_page_without_boxscore_clears_stale_preview_and_preserves_other_data(): void
    {
        $this->mock(StatFetcherInterface::class)->shouldReceive('fetch')->once()
            ->with('https://pbasket.cz.basketball/zapas/12345')->andReturn('<div class="match-header"><div class="alfa">Kbely E</div><div class="beta">Opponent</div></div>');
        $this->sync();
        $match = $this->match->fresh();
        $this->assertSame('preserved', $match->metadata['other']);
        $this->assertSame([], $match->metadata['team_comparison']);
        $this->assertSame([], $match->metadata['mutual_matches']);
        $this->assertNotEmpty($match->metadata['preview_synced_at']);
        $this->assertNull($match->score_home);
        Bus::assertDispatched(ComputeMatchPredictionJob::class);
        $this->sync(); // Successful sync throttles the second fetch.
    }

    public function test_away_preview_imports_comparison_and_force_refreshes_it(): void
    {
        $this->match->update(['is_home' => false]);
        $html = '<div class="match-header"><div class="alfa">Opponent</div><div class="beta">Kbely E</div></div>'
            .'<table><tr><td>70</td><td>Body na zápas</td><td>80</td></tr></table>';
        $this->mock(StatFetcherInterface::class)->shouldReceive('fetch')->twice()->andReturn($html);
        $this->sync();
        $this->assertSame('70', $this->match->fresh()->metadata['team_comparison']['pts_per_game']['home']);
        $this->assertSame('Kbely E', $this->match->fresh()->metadata['official_team_name']);
        $this->sync(true);
    }

    public function test_mismatched_header_never_updates_metadata_even_with_force(): void
    {
        $before = $this->match->metadata;
        $this->mock(StatFetcherInterface::class)->shouldReceive('fetch')->once()
            ->andReturn('<div class="match-header"><div class="alfa">Kbely E</div><div class="beta">Wrong opponent</div></div>');
        $this->sync(true);
        $this->assertSame($before, $this->match->fresh()->metadata);
        Bus::assertNotDispatched(ComputeMatchPredictionJob::class);
    }

    public function test_disabled_or_inactive_or_past_matches_are_never_fetched_even_with_force(): void
    {
        $this->mock(StatFetcherInterface::class)->shouldNotReceive('fetch');
        $this->config->update(['metadata' => []]);
        $this->sync(true);
        $this->config->update(['metadata' => ['matches_enabled' => true]]);
        $this->match->season->update(['is_active' => false]);
        $this->sync(true);
        $this->match->season->update(['is_active' => true]);
        $this->match->update(['scheduled_at' => now()->subMinute()]);
        $this->sync(true);
        $this->match->update(['scheduled_at' => now()->addDay()]);
        $this->config->update(['matches_list_url' => 'https://evil-cz.basketball/']);
        $this->sync(true);
    }

    private function sync(bool $force = false): void
    {
        app(MatchPreviewSyncService::class)->sync($this->match->id, $this->match->team_id, $force);
    }
}
