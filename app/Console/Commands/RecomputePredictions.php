<?php

namespace App\Console\Commands;

use App\Models\BasketballMatch;
use App\Jobs\ComputeMatchPredictionJob;
use Illuminate\Console\Command;

class RecomputePredictions extends Command
{
    protected $signature = 'stats:predictions:recompute {--all : Recompute for all matches, not just future ones}';
    protected $description = 'Recompute match predictions';

    public function handle(): int
    {
        $query = BasketballMatch::query();

        if (!$this->option('all')) {
            $query->whereIn('status', ['planned', 'scheduled']);
        }

        $matches = $query->get();

        $this->info("Dispatching prediction jobs for {$matches->count()} matches...");

        $bar = $this->output->createProgressBar($matches->count());

        foreach ($matches as $match) {
            ComputeMatchPredictionJob::dispatch($match->id);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Jobs dispatched.');

        return 0;
    }
}
