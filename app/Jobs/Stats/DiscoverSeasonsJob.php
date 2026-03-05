<?php

namespace App\Jobs\Stats;

use App\Services\Stats\Sync\SeasonDiscoveryService;
use App\Services\Support\ConsoleService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DiscoverSeasonsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var int Počet sekund, po kterých job vyprší (aby mohl běžet dlouho)
     */
    public $timeout = 600;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected ?string $teamSlug = null,
        protected ?string $seasonName = null,
        protected array $options = []
    ) {}

    /**
     * Execute the job.
     */
    public function handle(SeasonDiscoveryService $discoveryService): void
    {
        ConsoleService::log('Job: Zahajuji proces vyhledávání chybějících sezón (Discovery)...', 'info');

        try {
            $results = $discoveryService->discover($this->teamSlug, $this->seasonName, $this->options);

            $found = count(array_filter($results, fn ($r) => ! in_array($r['status'], ['not found', 'error'])));

            ConsoleService::log("Job: Discovery dokončeno. Nalezeno a vytvořeno: {$found} nových konfigurací.", 'success');
        } catch (\Exception $e) {
            ConsoleService::log("Job: Discovery selhalo s chybou: " . $e->getMessage(), 'error');
            throw $e;
        }
    }
}
