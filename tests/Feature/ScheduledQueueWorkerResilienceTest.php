<?php

namespace Tests\Feature;

use App\Services\System\ScheduledQueueWorker;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Exceptions;
use Illuminate\Support\Facades\Log;
use Mockery;
use PDOException;
use Tests\TestCase;

class ScheduledQueueWorkerResilienceTest extends TestCase
{
    public function test_it_reports_database_outage_and_skips_queue_worker(): void
    {
        Exceptions::fake();
        Log::spy();

        $exception = new PDOException('SQLSTATE[HY000] [2002] Connection refused', 2002);
        $connection = Mockery::mock();
        $connection->shouldReceive('getPdo')->once()->andThrow($exception);

        DB::shouldReceive('connection')->once()->with(null)->andReturn($connection);
        Artisan::shouldReceive('call')->never();

        $this->app->make(ScheduledQueueWorker::class)->run('default', [
            '--stop-when-empty' => true,
        ]);

        Exceptions::assertReported(fn (PDOException $reported): bool => $reported === $exception);
        Log::shouldHaveReceived('warning')
            ->once()
            ->withArgs(fn (string $message, array $context): bool => $message === 'Scheduled queue worker skipped because the database is unavailable.'
                && $context['worker'] === 'default');
    }

    public function test_it_runs_database_queue_after_successful_preflight(): void
    {
        $connection = Mockery::mock();
        $connection->shouldReceive('getPdo')->once()->andReturn(new \stdClass);

        DB::shouldReceive('connection')->once()->with(null)->andReturn($connection);
        Artisan::shouldReceive('call')
            ->once()
            ->with('queue:work', [
                'connection' => 'database',
                '--queue' => 'critical-mail',
                '--stop-when-empty' => true,
            ])
            ->andReturn(0);

        $this->app->make(ScheduledQueueWorker::class)->run('critical-mail', [
            '--queue' => 'critical-mail',
            '--stop-when-empty' => true,
        ]);
    }
}
