<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;

use function Laravel\Prompts\info;
use function Laravel\Prompts\spin;

class LocalPrepareCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:local:prepare';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Příprava Vite assetů na localhostu pro následné nahrání přes FTP.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        info('📦 Lokální příprava projektu pro FTP synchronizaci');

        // 1. NPM Install
        spin(function () {
            $process = Process::run('npm ci --no-audit --no-fund');
            if (! $process->successful()) {
                throw new \Exception('NPM install selhal: '.$process->errorOutput());
            }
        }, 'Instaluji NPM závislosti...');
        info('✓ NPM závislosti nainstalovány.');

        // 2. NPM Build
        spin(function () {
            $process = Process::run('npm run build');
            if (! $process->successful()) {
                throw new \Exception('NPM build selhal: '.$process->errorOutput());
            }
        }, 'Sestavuji produkční assety (Vite build)...');
        info('✓ Assety sestaveny (v public/build/).');

        $this->newLine();
        info('🎉 Vše je připraveno! Nyní můžete nahrát tyto složky na FTP:');
        $this->line('  - public/build/');
        $this->line('  - public/assets/');
        $this->line('  - (a případně změněné PHP soubory v app/, resources/, routes/, atd.)');
        $this->newLine();
        $this->line('Po nahrání na server spusťte pouze migrace a cache:');
        $this->info('php artisan migrate --force && php artisan optimize');

        return self::SUCCESS;
    }
}
