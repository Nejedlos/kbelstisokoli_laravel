<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Dmarc\DmarcRecord;
use App\Models\Dmarc\DmarcReport;
use App\Services\Dmarc\DmarcAnalysisService;
use App\Services\Dmarc\DmarcAlertService;

class DmarcReanalyzeCommand extends Command
{
    protected $signature = 'dmarc:reanalyze {--domain=} {--from=} {--to=} {--send-alerts}';
    protected $description = 'Přepočítá DMARC analýzy pro existující záznamy.';

    public function handle(DmarcAnalysisService $analysisService, DmarcAlertService $alertService)
    {
        $query = DmarcRecord::with('report');

        if ($this->option('domain')) {
            $query->whereHas('report', function($q) {
                $q->where('domain', $this->option('domain'));
            });
        }

        if ($this->option('from')) {
            $query->where('created_at', '>=', $this->option('from'));
        }

        if ($this->option('to')) {
            $query->where('created_at', '<=', $this->option('to'));
        }

        $records = $query->get();
        $this->info("Zpracovávám {$records->count()} záznamů...");

        $bar = $this->output->createProgressBar($records->count());
        $bar->start();

        foreach ($records as $record) {
            $analysis = $analysisService->analyze($record, $record->report);

            if ($this->option('send-alerts')) {
                $alertService->handle($record, $record->report, $analysis);
            }

            $bar->advance();
        }

        $bar->finish();
        $this->info("\nHotovo.");
    }
}
