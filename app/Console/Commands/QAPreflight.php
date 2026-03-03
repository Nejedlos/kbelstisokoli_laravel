<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class QAPreflight extends Command
{
    protected $signature = 'qa:preflight';
    protected $description = 'Ověří připravenost prostředí pro testování (DB, Storage, Queue, Classes)';

    public function handle()
    {
        $this->header('QA Preflight Checker');

        $checks = [
            'Databáze' => $this->checkDatabase(),
            'Migrace' => $this->checkMigrations(),
            'Storage' => $this->checkStorage(),
            'Queue' => $this->checkQueue(),
            'Legacy Source' => $this->checkLegacySource(),
            'Klíčové třídy' => $this->checkClasses(),
            'Závislosti' => $this->checkDependencies(),
        ];

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
