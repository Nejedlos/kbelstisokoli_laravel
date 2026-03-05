<?php

namespace App\Jobs\Stats;

use App\Services\Stats\Sync\ExternalStatsSyncService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncMatchDetailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var int
     */
    public $tries = 3;

    /**
     * @var int
     */
    public $timeout = 60;

    public function __construct(
        protected int $matchId,
        protected array $options = []
    ) {}

    public function handle(ExternalStatsSyncService $service): void
    {
        $service->syncMatchDetail($this->matchId, $this->options);
    }
}
