<?php

namespace App\Console\Commands\Matches;

use Illuminate\Console\Command;

class UpdatePastMatchesStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'matches:update-past-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Aktualizuje stav zápasů v minulosti na "odehráno" (played/completed).';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Aktualizuji stav zápasů v minulosti...');

        $query = \App\Models\BasketballMatch::whereIn('status', ['planned', 'scheduled'])
            ->where('scheduled_at', '<', now()->subHours(2));

        $count = $query->count();

        if ($count === 0) {
            $this->info('Žádné zápasy k aktualizaci nebyly nalezeny.');

            return;
        }

        $query->each(function ($match) {
            if ($match->score_home !== null && $match->score_away !== null) {
                $match->status = 'completed';
            } else {
                $match->status = 'played';
            }
            $match->save();
        });

        $this->info("Aktualizováno {$count} zápasů.");
    }
}
