<?php

namespace Tests\Feature\Stats\Sync;

use App\Models\ExternalTeamSeasonConfig;
use App\Models\Season;
use App\Models\Team;
use App\Services\Stats\Sync\ExternalStatsSyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Mockery;
use App\Services\Stats\Contracts\StatFetcherInterface;
use App\Services\Stats\Sync\RosterSyncService;
use App\Services\Stats\Sync\MatchSyncService;
use App\Services\Stats\Sync\StatisticSyncService;
use App\Services\Stats\Sync\PlayerSyncService;
use App\Services\Stats\Sync\CompetitionSyncService;
use App\Services\Stats\Contracts\StatNormalizerInterface;

class LastSyncedUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_sync_team_season_updates_last_synced_at(): void
    {
        // 1. Příprava dat
        $team = Team::create(['name' => ['cs' => 'Test Team'], 'slug' => 'test-team']);
        $season = Season::create(['name' => '2024/2025', 'is_active' => true]);

        $config = ExternalTeamSeasonConfig::create([
            'source_key' => 'czbasketball',
            'team_id' => $team->id,
            'season_id' => $season->id,
            'external_team_id' => '123',
            'external_season_year' => 2024,
            'is_enabled' => true,
            'team_season_url' => 'http://example.com/roster',
            'matches_list_url' => 'http://example.com/matches',
            'last_synced_at' => null,
        ]);

        // 2. Mockování závislostí
        $fetcher = Mockery::mock(StatFetcherInterface::class);
        $rosterSyncService = Mockery::mock(RosterSyncService::class);
        $matchSyncService = Mockery::mock(MatchSyncService::class);
        $statisticSyncService = Mockery::mock(StatisticSyncService::class);
        $normalizer = Mockery::mock(StatNormalizerInterface::class);
        $playerSyncService = Mockery::mock(PlayerSyncService::class);
        $competitionSyncService = Mockery::mock(CompetitionSyncService::class);

        $partialService = Mockery::mock(ExternalStatsSyncService::class, [
            $fetcher,
            $rosterSyncService,
            $matchSyncService,
            $statisticSyncService,
            $normalizer,
            $playerSyncService,
            $competitionSyncService
        ])->makePartial()->shouldAllowMockingProtectedMethods();

        $partialService->shouldReceive('syncRoster')->once();
        $partialService->shouldReceive('syncMatchesList')->once();

        // 3. Akce
        $partialService->syncTeamSeason($team->id, $season->id);

        // 4. Ověření
        $config->refresh();
        $this->assertNotNull($config->last_synced_at, 'Pole last_synced_at by nemělo být null po synchronizaci.');
    }

    public function test_sync_team_season_throws_exception_on_error_but_updates_timestamp(): void
    {
        // 1. Příprava dat
        $team = Team::create(['name' => ['cs' => 'Test Team'], 'slug' => 'test-team']);
        $season = Season::create(['name' => '2024/2025', 'is_active' => true]);

        $config = ExternalTeamSeasonConfig::create([
            'source_key' => 'czbasketball',
            'team_id' => $team->id,
            'season_id' => $season->id,
            'external_team_id' => '123',
            'external_season_year' => 2024,
            'is_enabled' => true,
            'team_season_url' => 'http://example.com/roster',
            'matches_list_url' => 'http://example.com/matches',
            'last_synced_at' => null,
        ]);

        // 2. Mockování závislostí
        $fetcher = Mockery::mock(StatFetcherInterface::class);
        $rosterSyncService = Mockery::mock(RosterSyncService::class);
        $matchSyncService = Mockery::mock(MatchSyncService::class);
        $statisticSyncService = Mockery::mock(StatisticSyncService::class);
        $normalizer = Mockery::mock(StatNormalizerInterface::class);
        $playerSyncService = Mockery::mock(PlayerSyncService::class);
        $competitionSyncService = Mockery::mock(CompetitionSyncService::class);

        $partialService = Mockery::mock(ExternalStatsSyncService::class, [
            $fetcher,
            $rosterSyncService,
            $matchSyncService,
            $statisticSyncService,
            $normalizer,
            $playerSyncService,
            $competitionSyncService
        ])->makePartial()->shouldAllowMockingProtectedMethods();

        $partialService->shouldReceive('syncRoster')->once()->andThrow(new \Exception('Test Error Roster'));
        $partialService->shouldReceive('syncMatchesList')->once();

        // 3. Akce a ověření výjimky
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Synchronizace dokončena s chybami: Soupiska: Test Error Roster');

        try {
            $partialService->syncTeamSeason($team->id, $season->id);
        } catch (\Exception $e) {
            // 4. Ověření timestampu i při chybě
            $config->refresh();
            $this->assertNotNull($config->last_synced_at, 'Pole last_synced_at by mělo být nastaveno i při chybě.');
            throw $e;
        }
    }
}
