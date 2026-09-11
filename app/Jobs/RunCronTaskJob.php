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
     * Dynamické úlohy se plánují znovu vlastním cron výrazem. Opakování stejné
     * zprávy ve frontě jen zvyšuje zátěž a zamlží původní chybu.
     */
    public int $tries = 1;

    /**
     * Musí zůstat kratší než retry_after databázové fronty. Delší importy
     * statistik tak nedostanou druhý pokus, zatímco první ještě běží.
     */
    public int $timeout = 240;

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

    /**
     * Zachytí i selhání, při němž Laravel nedojde do handle() (například
     * vypršení timeoutu workeru), aby stav v administraci nezůstal „running“.
     */
    public function failed(Throwable $exception): void
    {
        $task = $this->task->fresh();

        if (! $task) {
            Log::error('Cron task job failed after its task was removed.', [
                'cron_task_id' => $this->task->getKey(),
                'exception' => $exception,
            ]);

            return;
        }

        Log::error("Cron task [{$task->name}] failed in the queue: ".$exception->getMessage());

        $attributes = [
            'finished_at' => now(),
            'status' => 'failed',
            'error_message' => $exception->getMessage()."\n".$exception->getTraceAsString(),
        ];

        $log = $task->logs()
            ->where('status', 'running')
            ->latest('started_at')
            ->first();

        if ($log) {
            $log->update($attributes + [
                'duration_ms' => (int) ($log->started_at->diffInMilliseconds(now())),
            ]);
        } else {
            $task->logs()->create($attributes + [
                'started_at' => now(),
                'duration_ms' => 0,
            ]);
        }

        $task->update([
            'last_run_at' => now(),
            'last_status' => 'failed',
            'last_error_message' => $exception->getMessage(),
        ]);
    }
}
