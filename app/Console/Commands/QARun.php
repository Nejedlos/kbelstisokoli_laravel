<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use App\Models\Season;
use App\Models\Team;
use App\Models\User;
use App\Models\ExternalTeamSeasonConfig;
use App\Models\BasketballMatch;
use App\Models\StatisticRow;
use App\Services\Stats\Contracts\StatFetcherInterface;
use App\Services\Stats\Sync\ExternalStatsSyncService;
use Mockery;

class QARun extends Command
{
    protected $signature = 'qa:run {--full : Provede kompletní reset DB a plný smoke run}';
    protected $description = 'Provede end-to-end smoke test celého systému statistik';

    public function handle()
    {
        $this->info("========================================");
        $this->info("  BRUTÁLNÍ SMOKE RUNNER (QA:RUN)");
        $this->info("========================================");

        if ($this->option('full')) {
            $this->warn("!!! PROVÁDÍM KOMPLETNÍ RESET DATABÁZE !!!");
            Artisan::call('migrate:fresh', ['--force' => true]);
            $this->info("Databáze vyresetována.");

            $this->info("Seedování základních dat...");
            Artisan::call('db:seed', ['--class' => 'RoleSeeder', '--force' => true]);
            Artisan::call('db:seed', ['--class' => 'UserSeeder', '--force' => true]);
            Artisan::call('db:seed', ['--class' => 'SportSeeder', '--force' => true]);
        }

        $this->section("1. Externí Sync (z Fixtures)");
        $this->runExternalSyncSmoke();

        // Namapujeme admina na jednoho z hráčů, aby viděl data v členské sekci
        $admins = User::whereIn('email', ['nejedlymi@gmail.com', 'admin@basketkbely.cz'])->get();
        foreach ($admins as $admin) {
            $season = Season::where('is_active', true)->first();
            $this->info("Mapuji uživatele {$admin->email} na testovacího hráče (ID 11246)...");

            // Smažeme případného ghost uživatele, který si toto ID zabral
            $ghostMapping = \App\Models\ExternalEntityMapping::where([
                'source_key' => 'czbasketball',
                'entity_type' => 'player',
                'external_id' => '11246',
            ])->first();

            if ($ghostMapping && $ghostMapping->internal_id != $admin->id) {
                $ghostUser = User::find($ghostMapping->internal_id);
                if ($ghostUser && str_contains($ghostUser->email, 'ghost')) {
                    $ghostUser->delete();
                }
            }

            app(\App\Services\Stats\Sync\StatisticSyncService::class)->linkPlayerAndRecompute(
                \App\Models\ExternalEntityMapping::updateOrCreate([
                    'source_key' => 'czbasketball',
                    'entity_type' => 'player',
                    'external_id' => '11246',
                ], [
                    'internal_id' => $admin->id,
                    'internal_type' => User::class,
                    'season_id' => $season->id,
                ]),
                $admin->id
            );
        }
        $this->line("✅ Admini namapováni.");

        $this->section("2. Legacy Import (reálné soubory)");
        $this->runLegacyImportSmoke();

        $this->section("3. Ověření invariantů");
        $this->checkInvariants();

        $this->info("\n✅ SMOKE RUN DOKONČEN ÚSPĚŠNĚ.");
        return 0;
    }

    private function section(string $title): void
    {
        $this->info("\n>>> " . $title);
    }

    private function runExternalSyncSmoke(): void
    {
        $season = Season::where('is_active', true)->first() ?: Season::create(['name' => '2025/2026', 'is_active' => true]);

        $teams = [
            'muzi-e' => '7738',
            'muzi-c' => '7761',
        ];

        // Mock fetcheru
        $currentSyncTeamName = 'Sokol Kbely E'; // Default
        $mockFetcher = Mockery::mock(StatFetcherInterface::class);
        $mockFetcher->shouldReceive('fetch')->andReturnUsing(function($url) use (&$currentSyncTeamName) {
            if (str_contains($url, 'tym/')) return File::get(base_path('tests/Fixtures/Stats/CzBasketball/team_page.html'));
            if (str_contains($url, 'zapasy')) return File::get(base_path('tests/Fixtures/Stats/CzBasketball/matches_list.html'));
            if (str_contains($url, 'zapas/')) {
                $html = File::get(base_path('tests/Fixtures/Stats/CzBasketball/match_detail.html'));
                // V fixture je "Sokol Kbely E". Nahradíme ho jen pokud synchronizujeme něco jiného.
                if ($currentSyncTeamName !== 'Sokol Kbely E') {
                    $html = str_replace('Sokol Kbely E', $currentSyncTeamName, $html);
                }
                return $html;
            }
            return '';
        });
        app()->instance(StatFetcherInterface::class, $mockFetcher);

        $syncService = app(ExternalStatsSyncService::class);

        foreach ($teams as $slug => $extId) {
            $team = Team::where('slug', $slug)->first() ?: Team::create([
                'name' => ['cs' => ($slug === 'muzi-c' ? 'Sokol Kbely C' : 'Sokol Kbely E')],
                'slug' => $slug
            ]);

            $currentSyncTeamName = $team->getTranslation('name', 'cs');

            ExternalTeamSeasonConfig::updateOrCreate([
                'team_id' => $team->id,
                'season_id' => $season->id,
            ], [
                'source_key' => 'czbasketball',
                'external_team_id' => $extId,
                'external_season_year' => 2025,
                'team_season_url' => "https://cz.basketball/tym/{$extId}?y=2025",
                'matches_list_url' => "https://smo.cz.basketball/zapasy?c={$extId}&y=2025",
                'is_enabled' => true,
            ]);

            $this->info("Synchronizuji sezónu týmu {$team->slug}...");
            $syncService->syncTeamSeason($team->id, $season->id);

            $matchCount = BasketballMatch::where('team_id', $team->id)->count();
            $this->line("✅ Importováno zápasů pro {$slug}: $matchCount");

            // Synchronizujeme detaily všech zápasů
            $matches = BasketballMatch::where('team_id', $team->id)->get();
            $this->info("Synchronizuji detaily " . $matches->count() . " zápasů pro {$slug}...");
            foreach ($matches as $match) {
                $syncService->syncMatchDetail($match->id, ['force' => true]);
            }

            $statRows = StatisticRow::where('team_id', $team->id)
                ->where('season_id', $season->id)
                ->whereNotNull('basketball_match_id')
                ->count();
            $this->line("✅ Importováno celkem řádků statistik pro {$slug}: $statRows");
        }
    }

    private function runLegacyImportSmoke(): void
    {
        $path = storage_path('app/legacystats');
        if (!File::isDirectory($path)) {
            $this->warn("Složka legacystats neexistuje, přeskakuji.");
            return;
        }

        $this->info("Spouštím reálný legacy import z {$path}...");

        // Vytvoříme batch
        $batch = \App\Models\LegacyImportBatch::create([
            'title' => 'QA Smoke Run Legacy Import',
            'status' => 'queued',
            'total_files' => 0,
            'created_by_user_id' => User::first()?->id,
        ]);

        $files = File::files($path);
        $count = 0;
        $classifier = app(\App\Services\Stats\Legacy\LegacyFileClassifier::class);

        foreach ($files as $file) {
            if (in_array($file->getExtension(), ['html', 'htm'])) {
                $content = File::get($file->getPathname());
                $classification = $classifier->classify($file->getFilename(), $content);

                if (empty($classification['season'])) {
                    $this->warn("U souboru {$file->getFilename()} nebyla detekována sezóna!");
                }

                \App\Models\LegacyImportFile::create([
                    'legacy_import_batch_id' => $batch->id,
                    'original_filename' => $file->getFilename(),
                    'stored_path' => 'legacystats/' . $file->getFilename(),
                    'detected_season_label' => $classification['season'],
                    'detected_team_slug' => $classification['team'],
                    'file_type' => $classification['file_type'],
                    'status' => 'queued',
                    'content_hash' => hash('sha256', $content),
                ]);
                $count++;
            }
        }

        $batch->update(['total_files' => $count]);
        $this->info("Vytvořen batch {$batch->id} s {$count} soubory.");

        // Spustíme synchronně
        Artisan::call('legacy:import-batch', ['batchId' => $batch->id, '--sync' => true]);

        $batch->refresh();
        $this->line("✅ Legacy import dokončen. Stav: {$batch->status}, Úspěšně: {$batch->success_files}, Chyba: {$batch->failed_files}");

        $statRows = StatisticRow::whereJsonContains('source_metadata->source_type', 'legacy')->count();
        $this->line("✅ Celkem naimportováno legacy statistik: $statRows");
    }

    private function checkInvariants(): void
    {
        $errors = [];

        if (Season::count() === 0) $errors[] = "Chybí sezóny.";
        if (Team::count() === 0) $errors[] = "Chybí týmy.";

        $statRowsWithoutSeason = StatisticRow::whereNull('season_id')->count();
        if ($statRowsWithoutSeason > 0) $errors[] = "Existuje $statRowsWithoutSeason statistik bez sezóny.";

        if (count($errors) > 0) {
            foreach ($errors as $error) $this->error("❌ Invariant selhal: $error");
            exit(1);
        }

        $this->line("✅ Všechny invarianty v pořádku.");
    }
}
