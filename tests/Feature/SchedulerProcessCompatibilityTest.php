<?php

namespace Tests\Feature;

use Illuminate\Console\Scheduling\CallbackEvent;
use Illuminate\Console\Scheduling\Schedule;
use Tests\TestCase;

class SchedulerProcessCompatibilityTest extends TestCase
{
    public function test_default_queue_worker_runs_inside_the_scheduler_process(): void
    {
        $event = collect($this->app->make(Schedule::class)->events())
            ->first(fn ($event): bool => $event->description === 'queue-worker-maintenance');

        $this->assertInstanceOf(CallbackEvent::class, $event);
    }
}
