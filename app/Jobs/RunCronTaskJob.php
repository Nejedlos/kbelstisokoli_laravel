<?php

namespace App\Jobs;

use App\Models\CronLog;
use App\Models\CronTask;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Throwable;

class RunCronTaskJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public CronTask $task) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if (! $this->task->is_active) {
            return;
        }

        $startTime = microtime(true);
        $log = CronLog::create([
            'cron_task_id' => $this->task->id,
            'started_at' => now(),
            'status' => 'running',
        ]);

        try {
            // Spuštění Artisan příkazu
            // Poznámka: Pokud command obsahuje argumenty, Artisan::call je zvládne
            Artisan::call($this->task->command);
            $output = Artisan::output();

            $log->update([
                'finished_at' => now(),
                'status' => 'success',
                'output' => $output,
                'duration_ms' => (int) ((microtime(true) - $startTime) * 1000),
            ]);

            $this->task->update([
                'last_run_at' => now(),
                'last_status' => 'success',
                'last_error_message' => null,
            ]);

        } catch (Throwable $e) {
            Log::error("Cron task [{$this->task->name}] failed: ".$e->getMessage());

            $log->update([
                'finished_at' => now(),
                'status' => 'failed',
                'error_message' => $e->getMessage()."\n".$e->getTraceAsString(),
                'duration_ms' => (int) ((microtime(true) - $startTime) * 1000),
            ]);

            $this->task->update([
                'last_run_at' => now(),
                'last_status' => 'failed',
                'last_error_message' => $e->getMessage(),
            ]);
        }
    }
}
