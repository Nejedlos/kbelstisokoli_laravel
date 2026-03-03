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
            Artisan::call('db:seed', ['--class' => 'SportSeeder', '--force' => true]);
        }

        $this->section("1. Externí Sync (z Fixtures)");
        $this->runExternalSyncSmoke();

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
        $season = Season::first() ?: Season::create(['name' => '2025/2026', 'is_active' => true]);
        $team = Team::where('slug', 'muzi-e')->first() ?: Team::create(['name' => ['cs' => 'Muzi E'], 'slug' => 'muzi-e']);

        $config = ExternalTeamSeasonConfig::updateOrCreate([
            'team_id' => $team->id,
            'season_id' => $season->id,
        ], [
            'source_key' => 'czbasketball',
            'external_team_id' => '7738',
            'external_season_year' => 2025,
            'team_season_url' => 'https://cz.basketball/tym/7738?y=2025',
            'matches_list_url' => 'https://smo.cz.basketball/zapasy?c=7738&y=2025',
            'is_enabled' => true,
        ]);

        // Mock fetcheru
        $mockFetcher = Mockery::mock(StatFetcherInterface::class);
        $mockFetcher->shouldReceive('fetch')->andReturnUsing(function($url) {
            if (str_contains($url, 'tym/')) return File::get(base_path('tests/Fixtures/Stats/CzBasketball/team_page.html'));
            if (str_contains($url, 'zapasy')) return File::get(base_path('tests/Fixtures/Stats/CzBasketball/matches_list.html'));
            if (str_contains($url, 'zapas/')) return File::get(base_path('tests/Fixtures/Stats/CzBasketball/match_detail.html'));
            return '';
        });
        app()->instance(StatFetcherInterface::class, $mockFetcher);

        $syncService = app(ExternalStatsSyncService::class);
        $this->info("Synchronizuji sezónu týmu {$team->slug}...");
        $syncService->syncTeamSeason($team->id, $season->id);

        $matchCount = BasketballMatch::where('team_id', $team->id)->count();
        $this->line("✅ Importováno zápasů: $matchCount");

        $match = BasketballMatch::first();
        if ($match) {
            $this->info("Synchronizuji detail zápasu {$match->id}...");
            $syncService->syncMatchDetail($match->id);
            $statRows = StatisticRow::where('basketball_match_id', $match->id)->count();
            $this->line("✅ Importováno řádků statistik: $statRows");
        }
    }

    private function runLegacyImportSmoke(): void
    {
        $path = storage_path('app/legacystats');
        if (!File::isDirectory($path)) {
            $this->warn("Složka legacystats neexistuje, přeskakuji.");
            return;
        }

        $this->info("Spouštím detekci souborů v {$path}...");
        // Zde jen simulujeme spuštění jobu pro první soubor pro smoke test účely
        $files = File::files($path);
        $htmlFiles = array_filter($files, fn($f) => in_array($f->getExtension(), ['html', 'htm']));

        $this->line("✅ Nalezeno " . count($htmlFiles) . " souborů.");

        if (count($htmlFiles) > 0) {
            $file = reset($htmlFiles);
            $this->info("Zpracovávám ukázkový soubor: " . $file->getFilename());

            // Použijeme QAMasterTest logiku pro smoke v commandu
            $classifier = app(\App\Services\Stats\Legacy\LegacyFileClassifier::class);
            $extractor = app(\App\Services\Stats\Legacy\Extractors\LegacyStatExtractor::class);

            $content = File::get($file->getPathname());
            $classification = $classifier->classify($file->getFilename(), $content);
            $extractedTables = $extractor->extract($content, $classification['file_type'], $classification['encoding']);

            foreach ($extractedTables as $dto) {
                $this->line("✅ Vyextrahováno " . count($dto->rows) . " řádků (Typ: {$dto->name}).");
            }
        }
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
