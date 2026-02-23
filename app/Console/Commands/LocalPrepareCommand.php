<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;
use function Laravel\Prompts\info;
use function Laravel\Prompts\error;
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
    protected $description = 'Příprava všeho potřebného na localhostu pro následnou synchronizaci přes FTP (Vite build, ikony, cache).';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        info('📦 Lokální příprava projektu pro FTP synchronizaci');

        // 1. NPM Install
        spin(function () {
            $process = Process::run('npm install');
            if (!$process->successful()) {
                throw new \Exception("NPM install selhal: " . $process->errorOutput());
            }
        }, 'Instaluji NPM závislosti...');
        info('✓ NPM závislosti nainstalovány.');

        // 2. NPM Build
        spin(function () {
            $process = Process::run('npm run build');
            if (!$process->successful()) {
                throw new \Exception("NPM build selhal: " . $process->errorOutput());
            }
        }, 'Sestavuji produkční assety (Vite build)...');
        info('✓ Assety sestaveny (v public/build/).');

        // 3. Icons Sync
        spin(function () {
            $this->call('app:icons:sync');
        }, 'Synchronizuji ikony a generuji cache...');
        info('✓ Ikony synchronizovány (v public/webfonts/).');

        // 4. Optimize Clear (pro jistotu)
        spin(function () {
            $this->call('optimize:clear');
        }, 'Čistím lokální cache...');
        info('✓ Lokální cache vyčištěna.');

        $this->newLine();
        info('🎉 Vše je připraveno! Nyní můžete nahrát tyto složky na FTP:');
        $this->line('  - public/build/');
        $this->line('  - public/webfonts/');
        $this->line('  - (a případně změněné PHP soubory v app/, resources/, routes/, atd.)');
        $this->newLine();
        $this->line('Po nahrání na server nezapomeňte spustit:');
        $this->info('php artisan app:sync');

        return self::SUCCESS;
    }
}
