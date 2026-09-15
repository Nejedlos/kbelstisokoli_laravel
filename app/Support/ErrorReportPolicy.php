<?php

namespace App\Support;

use App\Jobs\RunCronTaskJob;
use Illuminate\Contracts\Queue\Job;
use Illuminate\Queue\MaxAttemptsExceededException;
use Throwable;

final class ErrorReportPolicy
{
    /**
     * A terminal retry failure of a dynamic cron job is already persisted by
     * RunCronTaskJob::failed(). Keep actual timeouts and all unrelated errors
     * visible in error-report emails.
     */
    public static function shouldSendEmail(Throwable $exception): bool
    {
        if ($exception::class !== MaxAttemptsExceededException::class) {
            return true;
        }

        $job = $exception->job;

        if (! $job instanceof Job) {
            return true;
        }

        try {
            return $job->resolveQueuedJobClass() !== RunCronTaskJob::class;
        } catch (Throwable) {
            return true;
        }
    }
}
