<?php

namespace App\Console\Commands;

use App\Models\ExternalImportRun;
use Illuminate\Console\Command;

class ExternalImportCancelCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:external-import:cancel {id : ID synchronizace k zrušení}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Zruší běžící synchronizaci (nastaví stav cancelled).';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $id = (int) $this->argument('id');
        $run = ExternalImportRun::find($id);

        if (!$run) {
            $this->error("Import s ID #{$id} nebyl nalezen.");
            return 1;
        }

        if ($run->status !== 'running') {
            $this->warn("Import #{$id} není ve stavu 'running' (aktuální stav: {$run->status}).");
            return 1;
        }

        $this->info("Ruším import #{$id} ({$run->run_type})...");
        $run->cancel();

        $this->info("Import byl označen jako 'cancelled'. Pokud proces stále běží, měl by se ukončit při příští kontrole stavu.");
        return 0;
    }
}
