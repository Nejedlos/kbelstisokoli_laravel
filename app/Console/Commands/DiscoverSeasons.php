<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class DiscoverSeasons extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stats:season-discover
                            {team? : Slug týmu (např. muzi-c)}
                            {season? : Název sezóny (např. 2019/2020)}
                            {--years= : Rozsah let (např. 2010..2025)}
                            {--max-attempts=5 : Maximální počet pokusů na sezónu}
                            {--dry-run : Pouze simulace}
                            {--sync : Po nalezení rovnou spustit synchronizaci}
                            {--force : Ignorovat existující konfigurace}';

    protected $description = 'Vyhledá a doplní chybějící konfigurace sezón z cz.basketball';

    public function handle(\App\Services\Stats\Sync\SeasonDiscoveryService $discoveryService)
    {
        $teamSlug = $this->argument('team');
        $seasonName = $this->argument('season');

        $options = [
            'years' => $this->option('years'),
            'max_attempts' => (int) $this->option('max-attempts'),
            'dry_run' => $this->option('dry-run'),
            'sync' => $this->option('sync'),
            'force' => $this->option('force'),
        ];

        $this->info('Zahajuji discovery proces...');
        if ($options['dry_run']) {
            $this->warn('REŽIM: DRY-RUN (žádné změny v DB)');
        }

        $results = $discoveryService->discover($teamSlug, $seasonName, $options);

        if (empty($results)) {
            $this->info('Nebyly nalezeny žádné sezóny k prověření.');

            return 0;
        }

        $headers = ['Team', 'Season', 'Year (y)', 'Confidence', 'Status'];
        $rows = array_map(function ($r) {
            return [
                $r['team'],
                $r['season'],
                $r['y'] ?? '-',
                $r['confidence'] ?? '-',
                $r['status'],
            ];
        }, $results);

        $this->table($headers, $rows);

        return 0;
    }
}
