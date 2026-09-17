<?php

namespace App\Services\System;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class ScheduledQueueWorker
{
    /**
     * Run a bounded database queue worker without taking down the HTTP
     * scheduler when the shared database is temporarily unavailable.
     *
     * @param  array<string, bool|int|string>  $options
     */
    public function run(string $workerName, array $options): void
    {
        try {
            $databaseConnection = config('queue.connections.database.connection');
            DB::connection($databaseConnection)->getPdo();
        } catch (Throwable $exception) {
            Log::warning('Scheduled queue worker skipped because the database is unavailable.', [
                'worker' => $workerName,
                'exception' => $exception::class,
                'message' => $exception->getMessage(),
            ]);

            // Zachováme jeden skutečný alert o výpadku; ErrorMailThrottle sloučí
            // další PDO/QueryException projevy stejného incidentu.
            report($exception);

            return;
        }

        try {
            Artisan::call('queue:work', [
                'connection' => 'database',
                ...$options,
            ]);
        } catch (Throwable $exception) {
            // Queue command může interně výjimku nahlásit ještě před návratem.
            // Zde ji už pouze provozně zaznamenáme a neshodíme celý scheduler.
            Log::error('Scheduled queue worker failed.', [
                'worker' => $workerName,
                'exception' => $exception::class,
                'message' => $exception->getMessage(),
            ]);
        }
    }
}
