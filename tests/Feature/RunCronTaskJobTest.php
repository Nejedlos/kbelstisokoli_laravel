<?php

namespace Tests\Feature;

use App\Jobs\RunCronTaskJob;
use App\Models\CronTask;
use RuntimeException;
use Tests\TestCase;

class RunCronTaskJobTest extends TestCase
{
    public function test_it_marks_a_queue_level_failure_on_the_task_and_its_running_log(): void
    {
        $task = CronTask::create([
            'name' => 'Slow statistics import',
            'command' => 'stats:import',
            'expression' => '0 * * * *',
            'is_active' => true,
        ]);
        $log = $task->logs()->create([
            'started_at' => now()->subSeconds(5),
            'status' => 'running',
        ]);

        (new RunCronTaskJob($task))->failed(new RuntimeException('Worker timed out.'));

        $this->assertSame('failed', $task->fresh()->last_status);
        $this->assertSame('Worker timed out.', $task->fresh()->last_error_message);
        $this->assertSame('failed', $log->fresh()->status);
        $this->assertSame('Worker timed out.', str($log->fresh()->error_message)->before("\n")->toString());
        $this->assertNotNull($log->fresh()->finished_at);
    }

    public function test_it_uses_one_attempt_and_a_timeout_shorter_than_the_queue_retry_window(): void
    {
        $job = new RunCronTaskJob(CronTask::create([
            'name' => 'Queue configuration check',
            'command' => 'stats:import',
            'expression' => '0 * * * *',
            'is_active' => true,
        ]));

        $this->assertSame(1, $job->tries);
        $this->assertLessThan(config('queue.connections.database.retry_after'), $job->timeout);
    }
}
