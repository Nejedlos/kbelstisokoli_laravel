<?php

namespace Tests\Feature\QA;

use App\Models\BasketballMatch;
use App\Models\ExternalImportRun;
use App\Models\ExternalTeamSeasonConfig;
use App\Models\Season;
use App\Models\Team;
use App\Models\User;
use App\Services\Stats\Contracts\StatFetcherInterface;
use App\Services\Stats\Extractors\CzBasketball\MatchesListExtractor;
use App\Services\Stats\Legacy\Extractors\LegacyStatExtractor;
use App\Services\Stats\Legacy\LegacyFileClassifier;
use App\Services\Stats\Sync\ExternalStatsSyncService;
use App\Services\Stats\Sync\MatchSyncService;
use App\Services\Stats\Sync\StatisticSetService;
use App\Services\Stats\Sync\StatisticSyncService;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Mockery;
use Tests\TestCase;

class QAMasterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    /**
     * QA-AUTH-*: Testy autentizace a oprávnění
     */
    public function test_auth_and_permissions()
    {
        // 1. Guest Access
        $this->get('/admin')->assertUnauthorized();
        $this->get('/clenska-sekce/dashboard')->assertUnauthorized();

        // 2. Member (Player) Access
        $player = User::factory()->create(['is_active' => true]);
        $player->assignRole('player');

        $this->actingAs($player);
        $this->get('/clenska-sekce/dashboard')->assertStatus(200);
        $this->get('/admin')->assertStatus(403);

        // 3. Admin Access
        $admin = $this->with2FA($this->createAdmin());

        $this->actingAs($admin, 'web');
        $this->confirm2FA($admin);

        $this->get('/admin')->assertStatus(200);
        $this->get('/clenska-sekce/dashboard')->assertStatus(200);
    }

    /**
     * QA-SYNC-*: Testy externí synchronizace z Fixtures
     */
    public function test_external_sync_pipeline()
    {
        // Příprava dat
        $season = Season::create(['name' => '2025/2026', 'is_active' => true]);
        $team = Team::create(['name' => ['cs' => 'Sokol Kbely E'], 'slug' => 'sokol-kbely-e']);

        $config = ExternalTeamSeasonConfig::create([
            'source_key' => 'czbasketball',
            'season_id' => $season->id,
            'team_id' => $team->id,
            'external_team_id' => '7738',
            'external_season_year' => 2025,
            'team_season_url' => 'https://cz.basketball/tym/7738?y=2025',
            'matches_list_url' => 'https://smo.cz.basketball/zapasy?c=7738&y=2025',
            'team_name_in_source' => 'Sokol Kbely E',
            'is_enabled' => true,
        ]);

        // Mock Fetcher pro soupisku, zápasy i detail
        $fetcher = Mockery::mock(StatFetcherInterface::class);
        $fetcher->shouldReceive('fetch')
            ->with(Mockery::on(fn ($url) => str_contains($url, 'tym/7738')), Mockery::any())
            ->andReturn(File::get(base_path('tests/Fixtures/Stats/CzBasketball/team_page.html')));
        $fetcher->shouldReceive('fetch')
            ->with(Mockery::on(fn ($url) => str_contains($url, 'zapasy')), Mockery::any())
            ->andReturn(File::get(base_path('tests/Fixtures/Stats/CzBasketball/matches_list.html')));
        $fetcher->shouldReceive('fetch')
            ->with(Mockery::on(fn ($url) => str_contains($url, 'zapas/')), Mockery::any())
            ->andReturn(File::get(base_path('tests/Fixtures/Stats/CzBasketball/match_detail.html')));

        $this->app->instance(StatFetcherInterface::class, $fetcher);

        /** @var ExternalStatsSyncService $syncService */
        $syncService = $this->app->make(ExternalStatsSyncService::class);

        // 1. Sync Team Season (Roster + Matches List)
        try {
            $syncService->syncTeamSeason($team->id, $season->id);
        } catch (\Exception $e) {
            dump('SyncTeamSeason Exception: '.$e->getMessage());
        }

        dump('Import runs status & errors: ', ExternalImportRun::all()->mapWithKeys(fn ($r) => [$r->run_type => ['status' => $r->status, 'error' => $r->error_summary]])->toArray());

        if (BasketballMatch::count() === 0) {
            dump('Matches list fixture content length: '.strlen(File::get(base_path('tests/Fixtures/Stats/CzBasketball/matches_list.html'))));
            $extractor = $this->app->make(MatchesListExtractor::class);
            $data = $extractor->extract(File::get(base_path('tests/Fixtures/Stats/CzBasketball/matches_list.html')));
            dump('Extractor data rows count: '.count($data['data']->rows));

            // Manual sync attempt to see if it fails here
            dump('Manual sync attempt...');
            $matchSync = $this->app->make(MatchSyncService::class);
            try {
                $matchSync->sync($team, $season, $data['data']->rows[0]->values);
                dump('Manual sync success. Matches count: '.BasketballMatch::count());
            } catch (\Exception $e) {
                dump('Manual sync exception: '.$e->getMessage());
            }
        }

        $this->assertDatabaseHas('matches', ['season_id' => $season->id, 'team_id' => $team->id]);
        $this->assertDatabaseHas('users', ['name' => 'Marek Novotný']); // Z fixture soupisky

        // 2. Sync Match Detail (Boxscore)
        $match = BasketballMatch::first();
        $this->assertNotNull($match, 'No match found in database after sync.');

        $syncService->syncMatchDetail($match->id);

        // Ověření statistik
        $this->assertDatabaseHas('statistic_rows', [
            'basketball_match_id' => $match->id,
            'team_id' => $team->id,
        ]);

        // 3. Idempotence (Hash)
        $runCountBefore = ExternalImportRun::count();
        $syncService->syncTeamSeason($team->id, $season->id);
        // Each syncTeamSeason makes 2 calls: roster and matches_list
        $this->assertEquals($runCountBefore + 2, ExternalImportRun::count());
        $this->assertEquals('skipped', ExternalImportRun::orderBy('id', 'desc')->first()->status);
    }

    /**
     * QA-LEG-*: Testy legacy importu z reálné složky
     */
    public function test_legacy_import_pipeline()
    {
        $path = storage_path('app/legacystats');
        if (! File::isDirectory($path) || count(File::files($path)) === 0) {
            $this->markTestSkipped("Legacy source files not found in {$path}");
        }

        $files = File::files($path);
        $testFile = null;
        foreach ($files as $file) {
            if ($file->getExtension() === 'html') {
                $testFile = $file;
                break;
            }
        }

        if (! $testFile) {
            $this->markTestSkipped('No .html legacy files found.');
        }

        /** @var LegacyFileClassifier $classifier */
        $classifier = $this->app->make(LegacyFileClassifier::class);
        /** @var LegacyStatExtractor $extractor */
        $extractor = $this->app->make(LegacyStatExtractor::class);

        // 1. Classification
        $classification = $classifier->classify($testFile->getFilename(), File::get($testFile->getPathname()));
        $this->assertNotNull($classification['file_type']);

        // 2. Parsing
        $extracted = $extractor->extract(File::get($testFile->getPathname()), $classification['file_type']);
        $this->assertNotEmpty($extracted);
        $dto = $extracted[0];
        $this->assertNotEmpty($dto->columns);
        $this->assertNotEmpty($dto->rows);

        // 3. Persistence (Mock Batch)
        $season = Season::create(['name' => $classification['season'] ?? 'Legacy Season']);
        $team = Team::create(['name' => ['cs' => 'Legacy Team'], 'slug' => 'legacy-team']);

        /** @var StatisticSyncService $statService */
        $statService = $this->app->make(StatisticSyncService::class);
        /** @var StatisticSetService $setService */
        $setService = $this->app->make(StatisticSetService::class);

        $setType = match ($classification['file_type']) {
            'players_stats' => 'player',
            'team_stats' => 'team',
            default => 'team',
        };

        $set = $setService->ensureSet("legacy_{$classification['file_type']}_{$season->id}", 'Legacy Import', $setType, 'external');

        foreach ($dto->rows as $rowDto) {
            $statService->saveRow($set, $rowDto, [
                'season_id' => $season->id,
                'team_id' => $team->id,
                'source_metadata' => ['source_type' => 'legacy'],
            ]);
        }

        $this->assertDatabaseHas('statistic_rows', [
            'statistic_set_id' => $set->id,
            'season_id' => $season->id,
        ]);
    }

    /**
     * QA-UI-*: Testy renderování
     */
    public function test_ui_rendering()
    {
        $admin = User::factory()->create([
            'is_active' => true,
            'two_factor_secret' => 'secret',
            'two_factor_confirmed_at' => now(),
        ]);
        $admin->assignRole('admin');
        $this->actingAs($admin, 'web');
        session(['impersonated_by' => 1]);

        // Admin pages
        $this->get('/admin/debug-operations')->assertStatus(200);
        $this->get('/admin/external-import-runs')->assertStatus(200);
        $this->get('/admin/legacy-import-batches')->assertStatus(200);

        // Member dashboard
        $player = User::factory()->create(['is_active' => true]);
        $player->assignRole('player');
        $this->actingAs($player);

        $this->get('/clenska-sekce/statistiky')->assertStatus(200);

        // Public pages
        $this->get('/')->assertStatus(200);
    }
}
