<?php

namespace Tests\Feature\Stats\Sync;

use App\Models\ExternalEntityMapping;
use App\Models\ExternalImportRun;
use App\Models\ExternalTeamSeasonConfig;
use App\Models\Season;
use App\Models\Team;
use App\Models\User;
use App\Services\Stats\Contracts\StatFetcherInterface;
use App\Services\Stats\Sync\RosterSyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class RosterSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_syncs_roster_and_creates_ghost_users()
    {
        $season = Season::create(['name' => '2025/2026', 'is_active' => true]);
        $team = Team::create(['name' => ['cs' => 'Muzi E'], 'slug' => 'muzi-e']);

        $config = ExternalTeamSeasonConfig::create([
            'source_key' => 'czbasketball',
            'season_id' => $season->id,
            'team_id' => $team->id,
            'external_team_id' => '7738',
            'external_season_year' => 2025,
            'team_season_url' => 'https://cz.basketball/tym/7738?y=2025',
            'matches_list_url' => 'https://smo.cz.basketball/zapasy?c=7738&y=2025',
            'is_enabled' => true,
        ]);

        $html = file_get_contents(base_path('tests/Fixtures/Stats/CzBasketball/team_page.html'));

        $fetcher = Mockery::mock(StatFetcherInterface::class);
        $fetcher->shouldReceive('fetch')->andReturn($html);

        $this->app->instance(StatFetcherInterface::class, $fetcher);

        /** @var RosterSyncService $service */
        $service = $this->app->make(RosterSyncService::class);

        $run = $service->sync($config);

        if ($run->status === 'failed') {
            $this->fail('Sync failed: '.$run->error_summary."\n".($run->metadata['exception_trace'] ?? ''));
        }

        $this->assertEquals('success', $run->status);
        $this->assertGreaterThan(0, $run->imported_count);

        // Ověřit vytvoření ghost uživatele (Marek Novotný je v HTML fixture)
        $user = User::where('name', 'Marek Novotný')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->metadata['is_ghost']);
        $this->assertEquals('ghost_czbasketball_11246@kbelstisokoli.cz', $user->email);

        // Ověřit mapping
        $mapping = ExternalEntityMapping::where('external_id', '11246')->first();
        $this->assertNotNull($mapping);
        $this->assertEquals($user->id, $mapping->internal_id);

        // Ověřit soupisku
        $profile = $user->playerProfile->fresh();
        $this->assertNotNull($profile);
        $teamOnRoster = $profile->teams()->where('team_id', $team->id)->first();
        $this->assertNotNull($teamOnRoster, 'Team not found in profile teams');
        $this->assertTrue((bool) $teamOnRoster->pivot->is_on_roster);
    }

    public function test_it_skips_sync_if_hash_matches()
    {
        $season = Season::create(['name' => '2025/2026', 'is_active' => true]);
        $team = Team::create(['name' => ['cs' => 'Muzi E'], 'slug' => 'muzi-e']);

        $config = ExternalTeamSeasonConfig::create([
            'source_key' => 'czbasketball',
            'season_id' => $season->id,
            'team_id' => $team->id,
            'external_team_id' => '7738',
            'external_season_year' => 2025,
            'team_season_url' => 'https://cz.basketball/tym/7738?y=2025',
            'matches_list_url' => 'https://smo.cz.basketball/zapasy?c=7738&y=2025',
            'is_enabled' => true,
        ]);

        $html = file_get_contents(base_path('tests/Fixtures/Stats/CzBasketball/team_page.html'));
        $contentHash = hash('sha256', (new \App\Services\Stats\Extractors\CzBasketball\TeamRosterExtractor)->extract($html)['fragment_html']);

        ExternalImportRun::create([
            'source_key' => 'czbasketball',
            'season_id' => $season->id,
            'team_id' => $team->id,
            'run_type' => 'team_page',
            'target_external_id' => '7738',
            'status' => 'success',
            'content_hash' => $contentHash,
            'started_at' => now()->subDay(),
            'finished_at' => now()->subDay(),
        ]);

        $fetcher = Mockery::mock(StatFetcherInterface::class);
        $fetcher->shouldReceive('fetch')->andReturn($html);
        $this->app->instance(StatFetcherInterface::class, $fetcher);

        $service = $this->app->make(RosterSyncService::class);
        $run = $service->sync($config);

        if ($run->status === 'failed') {
            $this->fail('Sync failed: '.$run->error_summary."\n".($run->metadata['exception_trace'] ?? ''));
        }

        $this->assertEquals('skipped', $run->status);
    }
}
