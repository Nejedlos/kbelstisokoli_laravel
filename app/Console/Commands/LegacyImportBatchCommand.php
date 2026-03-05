<?php

namespace App\Console\Commands;

use App\Jobs\Stats\Legacy\ProcessLegacyImportBatchJob;
use App\Models\LegacyImportBatch;
use Illuminate\Console\Command;

class LegacyImportBatchCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'legacy:import-batch
                            {batchId : ID dávky k importu}
                            {--sync : Spustí import synchronně (v tomto procesu)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Spustí zpracování dávky historického importu statistik.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $batchId = $this->argument('batchId');
        $batch = LegacyImportBatch::find($batchId);

        if (! $batch) {
            $this->error("Dávka s ID {$batchId} nebyla nalezena.");

            return self::FAILURE;
        }

        if ($batch->status === 'success') {
            $this->warn('Tato dávka již byla úspěšně zpracována.');
            if (! $this->confirm('Chcete ji přesto zkusit zpracovat znovu?')) {
                return self::SUCCESS;
            }
        }

        if ($this->option('sync')) {
            $this->info('Spouštím import dávky synchronně...');
            $job = new ProcessLegacyImportBatchJob($batch->id);
            $job->handle();
            $this->info('Import dávky dokončen.');
        } else {
            $this->info('Zařazuji import dávky do fronty (ProcessLegacyImportBatchJob)...');
            ProcessLegacyImportBatchJob::dispatch($batch->id);
            $this->info('Úloha byla zařazena.');
        }

        return self::SUCCESS;
    }
}
