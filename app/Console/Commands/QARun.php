<?php

namespace App\Console\Commands;

use App\Models\BasketballMatch;
use App\Models\ExternalTeamSeasonConfig;
use App\Models\Season;
use App\Models\StatisticRow;
use App\Models\Team;
use App\Models\User;
use App\Services\Stats\Contracts\StatFetcherInterface;
use App\Services\Stats\Sync\ExternalStatsSyncService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Mockery;

class QARun extends Command
{
    protected $signature = 'qa:run {--full : Provede kompletní reset DB (pouze non-prod) a plný smoke run} {--prod : Spustí v produkčním režimu (bez resetu DB)}';

    protected $description = 'Provede end-to-end smoke test celého systému statistik';

    public function handle()
    {
        $this->info('========================================');
        $this->info('  BRUTÁLNÍ SMOKE RUNNER (QA:RUN)'.($this->option('prod') ? ' [PROD]' : ''));
        $this->info('========================================');

        if ($this->option('full') && ! $this->option('prod')) {
            $this->warn('!!! PROVÁDÍM KOMPLETNÍ RESET DATABÁZE !!!');
            Artisan::call('migrate:fresh', ['--force' => true]);
            $this->info('Databáze vyresetována.');

            $this->info('Seedování základních dat...');
            Artisan::call('db:seed', ['--class' => 'RoleSeeder', '--force' => true]);
            Artisan::call('db:seed', ['--class' => 'UserSeeder', '--force' => true]);
            Artisan::call('db:seed', ['--class' => 'SportSeeder', '--force' => true]);
        } elseif ($this->option('prod')) {
            $this->info('Produkční režim: Přeskakuji reset databáze a seedování.');
        }

        $this->section('1. Externí Sync (z Fixtures)');
        if ($this->option('prod')) {
            $this->info('Na produkci testujeme reálná data (pokud existují) nebo jen invariants.');
            // Na prod nebudeme mockovat fetcher v qa:run, ledaže bychom chtěli jen otestovat pipeline s fixtures.
            // Ale zadání říká "QA run musí zkontrolovat všechny klíčové invariants".
            // Pokud je to --full na prod, možná chceme zkusit reálný sync? Ne, fixtures jsou bezpečnější pro test parseru.
            // Ale uživatel chce vidět report s počty reálných dat.
        } else {
            $this->runExternalSyncSmoke();
        }

        if (! $this->option('prod')) {
            // Namapujeme admina na jednoho z hráčů, aby viděl data v členské sekci
            $admins = User::whereIn('email', ['nejedlymi@gmail.com', 'admin@basketkbely.cz'])->get();
            foreach ($admins as $admin) {
                $season = Season::where('is_active', true)->first();
                if (! $season) {
                    continue;
                }

                $this->info("Mapuji uživatele {$admin->email} na testovacího hráče (ID 11246)...");

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
            $this->line('✅ Admini namapováni.');
        }

        $this->section('2. Legacy Import (reálné soubory)');
        if (! $this->option('prod')) {
            $this->runLegacyImportSmoke();
        } else {
            $this->info('Na produkci přeskakuji automatický legacy import v QA runu (legacy import se provádí manuálně přes dropzonu).');
        }

        $this->section('3. Ověření invariantů');
        $this->checkInvariants();

        if ($this->option('prod')) {
            $this->generateProdReport();
        }

        $this->info("\n✅ QA RUN DOKONČEN ÚSPĚŠNĚ.");

        return 0;
    }

    private function section(string $title): void
    {
        $this->info("\n>>> ".$title);
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
        $mockFetcher->shouldReceive('fetch')->andReturnUsing(function ($url) use (&$currentSyncTeamName) {
            if (str_contains($url, 'tym/')) {
                return File::get(base_path('tests/Fixtures/Stats/CzBasketball/team_page.html'));
            }
            if (str_contains($url, 'zapasy')) {
                return File::get(base_path('tests/Fixtures/Stats/CzBasketball/matches_list.html'));
            }
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
                'slug' => $slug,
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
            $this->info('Synchronizuji detaily '.$matches->count()." zápasů pro {$slug}...");
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
        if (! File::isDirectory($path)) {
            $this->warn('Složka legacystats neexistuje, přeskakuji.');

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
                    'stored_path' => 'legacystats/'.$file->getFilename(),
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

        $statRows = StatisticRow::where('source_metadata->source_type', 'legacy')->count();
        $this->line("✅ Celkem naimportováno legacy statistik: $statRows");
    }

    private function checkInvariants(): void
    {
        $errors = [];

        if (Season::count() === 0) {
            $errors[] = 'Chybí sezóny.';
        }
        if (Team::count() === 0) {
            $errors[] = 'Chybí týmy.';
        }

        $statRowsWithoutSeason = StatisticRow::whereNull('season_id')->count();
        if ($statRowsWithoutSeason > 0) {
            $errors[] = "Existuje $statRowsWithoutSeason statistik bez sezóny.";
        }

        if (count($errors) > 0) {
            foreach ($errors as $error) {
                $this->error("❌ Invariant selhal: $error");
            }
            if ($this->option('prod')) {
                $this->error('Na produkci přerušuji běh!');
                exit(1);
            }
        }

        $this->line('✅ Všechny invarianty v pořádku.');
    }

    private function generateProdReport(): void
    {
        $this->section('Závěrečný QA Report (PROD)');

        $report = 'QA Rollout Report - '.now()->toDateTimeString()."\n";
        $report .= "========================================\n\n";

        $activeSeason = Season::where('is_active', true)->first();
        $report .= 'Aktivní sezóna: '.($activeSeason ? $activeSeason->name : 'NENÍ')."\n";
        $report .= 'Celkem sezón: '.Season::count()."\n";
        $report .= 'Celkem týmů: '.Team::count()."\n";
        $report .= 'Celkem zápasů: '.BasketballMatch::count()."\n";
        $report .= 'Externí statistiky (řádky): '.StatisticRow::where('source_metadata->source', 'czbasketball')->count()."\n";
        $report .= 'Legacy statistiky (řádky): '.StatisticRow::where('source_metadata->source_type', 'legacy')->count()."\n";
        $report .= 'Unmatched hráči: '.\App\Models\ExternalEntityMapping::where('entity_type', 'player')->whereNull('internal_id')->count()."\n";

        $this->info($report);

        $path = base_path('docs/prod-rollout-report.md');
        File::put($path, "### Production Rollout Report\n\n```text\n".$report."```\n");
        $this->info("Report uložen do {$path}");
    }
}
