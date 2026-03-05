<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class QAPreflight extends Command
{
    protected $signature = 'qa:preflight {--prod : Ověří produkční nastavení}';
    protected $description = 'Ověří připravenost prostředí pro testování (DB, Storage, Queue, Classes)';

    public function handle()
    {
        $this->header('QA Preflight Checker' . ($this->option('prod') ? ' [PROD MODE]' : ''));

        if ($this->option('prod')) {
            if (config('app.env') !== 'production') {
                $this->error("❌ APP_ENV není nastaven na 'production'.");
                return 1;
            }
            if (config('app.debug')) {
                $this->error("❌ APP_DEBUG je zapnutý. Na produkci musí být vypnutý.");
                return 1;
            }
        }

        $checks = [
            'Databáze' => $this->checkDatabase(),
            'Migrace' => $this->checkMigrations(),
            'Storage' => $this->checkStorage(),
            'Queue' => $this->checkQueue(),
            'Scheduler' => $this->checkScheduler(),
            'Klíčové třídy' => $this->checkClasses(),
            'Závislosti' => $this->checkDependencies(),
            'Týmové konfigurace' => $this->checkTeamConfigs(),
        ];

        if (!$this->option('prod')) {
            $checks['Legacy Source'] = $this->checkLegacySource();
        }

        $failed = in_array(false, array_values($checks), true);

        if ($failed) {
            $this->error("\nQA Preflight selhal. Opravte výše uvedené problémy před spuštěním testů.");
            return 1;
        }

        $this->info("\nQA Preflight v pořádku. Prostředí je připraveno k testování.");
        return 0;
    }

    private function header(string $title): void
    {
        $this->info("========================================");
        $this->info("  " . $title);
        $this->info("========================================");
    }

    private function checkDatabase(): bool
    {
        try {
            DB::connection()->getPdo();
            $this->line("✅ Databáze: Připojeno.");
            return true;
        } catch (\Exception $e) {
            $this->error("❌ Databáze: Nelze se připojit - " . $e->getMessage());
            return false;
        }
    }

    private function checkMigrations(): bool
    {
        try {
            $pending = DB::table('migrations')->count();
            $this->line("✅ Migrace: Tabulka existuje.");
            return true;
        } catch (\Exception $e) {
            $this->error("❌ Migrace: Tabulka migrací nenalezena. Spusťte php artisan migrate.");
            return false;
        }
    }

    private function checkStorage(): bool
    {
        $testFile = 'qa_test_file.txt';
        try {
            Storage::disk('local')->put($testFile, 'QA TEST');
            if (Storage::disk('local')->get($testFile) === 'QA TEST') {
                Storage::disk('local')->delete($testFile);
                $this->line("✅ Storage: Zápis i čtení funguje.");
                return true;
            }
            throw new \Exception("Obsah souboru nesouhlasí.");
        } catch (\Exception $e) {
            $this->error("❌ Storage: Chyba - " . $e->getMessage());
            return false;
        }
    }

    private function checkQueue(): bool
    {
        $driver = config('queue.default');
        $this->line("ℹ️ Queue: Driver je nastaven na '{$driver}'.");
        return true;
    }

    private function checkScheduler(): bool
    {
        $lastRun = \Illuminate\Support\Facades\Cache::get('scheduler_last_heartbeat');
        if (!$lastRun) {
            $this->warn("⚠️ Scheduler: Heartbeat nenalezen. Ujistěte se, že cron běží.");
            return true; // Jen varování
        }

        $diff = now()->diffInMinutes(\Illuminate\Support\Carbon::createFromTimestamp($lastRun));
        if ($diff > 10) {
            $this->error("❌ Scheduler: Poslední běh před {$diff} minutami (příliš dlouho).");
            return false;
        }

        $this->line("✅ Scheduler: Aktivní (poslední běh před {$diff} min).");
        return true;
    }

    private function checkTeamConfigs(): bool
    {
        $season = \App\Models\Season::where('is_active', true)->first();
        if (!$season) {
            $this->error("❌ Sezóny: Žádná aktivní sezóna nalezena.");
            return false;
        }

        $this->line("ℹ️ Aktivní sezóna: {$season->name}");

        $teams = \App\Models\Team::whereIn('slug', ['muzi-c', 'muzi-e'])->get();
        if ($teams->count() < 2) {
            $this->warn("⚠️ Týmy: Muži C nebo Muži E v DB chybí.");
        }

        foreach ($teams as $team) {
            $config = \App\Models\ExternalTeamSeasonConfig::where('team_id', $team->id)
                ->where('season_id', $season->id)
                ->first();

            if ($config) {
                $this->line("✅ Config [{$team->slug}]: Nalezen (y={$config->external_season_year}).");
            } else {
                $this->warn("⚠️ Config [{$team->slug}]: Chybí pro sezónu {$season->name}.");
            }
        }

        return true;
    }

    private function checkLegacySource(): bool
    {
        $path = storage_path('app/legacystats');
        if (!File::isDirectory($path)) {
            $this->error("❌ Legacy Source: Složka '{$path}' neexistuje.");
            return false;
        }

        $files = File::files($path);
        $htmlFiles = array_filter($files, fn($f) => in_array($f->getExtension(), ['html', 'htm']));

        if (count($htmlFiles) === 0) {
            $this->error("❌ Legacy Source: Ve složce nejsou žádné HTML/HTM soubory.");
            return false;
        }

        $this->line("✅ Legacy Source: Nalezeno " . count($htmlFiles) . " souborů.");
        return true;
    }

    private function checkClasses(): bool
    {
        $classes = [
            'App\Services\Stats\Fetchers\CzBasketballFetcher',
            'App\Services\Stats\Extractors\CzBasketball\TeamRosterExtractor',
            'App\Services\Stats\Sync\ExternalStatsSyncService',
            'App\Services\Stats\Legacy\Extractors\LegacyStatExtractor',
            'App\Services\Stats\Sync\StatisticSyncService',
        ];

        $allOk = true;
        foreach ($classes as $class) {
            if (!class_exists($class)) {
                $this->error("❌ Třída: '{$class}' chybí.");
                $allOk = false;
            }
        }

        if ($allOk) {
            $this->line("✅ Klíčové třídy: Všechny nalezeny.");
        }
        return $allOk;
    }

    private function checkDependencies(): bool
    {
        $packages = [
            'symfony/dom-crawler' => \Symfony\Component\DomCrawler\Crawler::class,
            'filament' => \Filament\Facades\Filament::class,
            'spatie/laravel-permission' => \Spatie\Permission\PermissionServiceProvider::class,
        ];

        $allOk = true;
        foreach ($packages as $name => $class) {
            if (!class_exists($class)) {
                $this->error("❌ Závislost: Balíček '{$name}' pravděpodobně chybí.");
                $allOk = false;
            }
        }

        if ($allOk) {
            $this->line("✅ Závislosti: Klíčové balíčky nalezeny.");
        }
        return $allOk;
    }
}
