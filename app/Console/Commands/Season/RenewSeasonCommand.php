<?php

namespace App\Console\Commands\Season;

use App\Actions\Season\RenewSeasonAction;
use Illuminate\Console\Command;

class RenewSeasonCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'season:renew {--target= : ID cílové sezóny} {--source= : ID zdrojové sezóny}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Hromadná inicializace nové sezóny na základě dat z předchozí.';

    /**
     * Execute the console command.
     */
    public function handle(RenewSeasonAction $renewSeasonAction)
    {
        $targetId = $this->option('target') ? (int) $this->option('target') : null;
        $sourceId = $this->option('source') ? (int) $this->option('source') : null;

        $this->info('Zahajuji obnovu sezóny...');

        $result = $renewSeasonAction->execute($targetId, $sourceId);

        $this->success("Obnova dokončena. Vytvořeno: {$result['created']}, Aktualizováno: {$result['updated']}.");
    }
}
