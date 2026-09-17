<?php

namespace Tests\Feature;

use App\Jobs\RunCronTaskJob;
use App\Mail\ErrorMail;
use App\Support\ErrorMailThrottle;
use App\Support\ErrorReportSanitizer;
use Illuminate\Contracts\Queue\Job;
use Illuminate\Database\QueryException;
use Illuminate\Queue\MaxAttemptsExceededException;
use Illuminate\Queue\TimeoutExceededException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Mockery;
use PDOException;
use RuntimeException;
use Tests\TestCase;

class ErrorReportHotfixTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();
        config([
            'mail.error_reporting.email' => 'errors@example.test',
            'mail.error_reporting.environments' => ['testing'],
            'mail.error_reporting.dedup_enabled' => false,
        ]);
    }

    public function test_it_does_not_email_the_terminal_retry_failure_already_recorded_by_a_cron_job(): void
    {
        report($this->queueException(MaxAttemptsExceededException::class, RunCronTaskJob::class));

        Mail::assertNothingSent();
    }

    public function test_it_still_emails_timeouts_and_unrelated_failures(): void
    {
        report($this->queueException(TimeoutExceededException::class, RunCronTaskJob::class));
        report($this->queueException(MaxAttemptsExceededException::class, RuntimeException::class));

        Mail::assertSent(ErrorMail::class, 2);
    }

    public function test_it_removes_credentials_from_the_entire_error_report(): void
    {
        $secret = 'scheduler-secret-value';
        $report = ErrorReportSanitizer::sanitize([
            'request' => [
                'url' => "https://kbelstisokoli.cz/system/schedule/{$secret}/?token=query-secret&json=1",
                'encoded_url' => 'https://kbelstisokoli.cz/system/%73chedule/encoded-secret?credentials%5Bpassword%5D=nested-query-secret',
                'encoded_separator_url' => 'https://kbelstisokoli.cz/system%2Fschedule/encoded-separator-secret',
                'prefixed_url' => 'https://kbelstisokoli.cz/app/system/schedule/prefixed-secret',
                'network_path_url' => '//kbelstisokoli.cz/system/schedule/network-path-secret',
                'input' => ['nested' => ['api_token' => 'input-secret']],
            ],
            'headers' => [
                'authorization' => 'Bearer authorization-secret',
                'cookie' => 'session=cookie-secret',
                'referer' => "https://kbelstisokoli.cz/system/schedule/{$secret}?json=1",
            ],
            'exception' => [
                'trace' => "Request URL: https://kbelstisokoli.cz/system/schedule/{$secret}?signature=signature-secret",
            ],
        ]);
        $encoded = json_encode($report, JSON_THROW_ON_ERROR);

        $this->assertStringNotContainsString($secret, $encoded);
        $this->assertStringNotContainsString('query-secret', $encoded);
        $this->assertStringNotContainsString('encoded-secret', $encoded);
        $this->assertStringNotContainsString('encoded-separator-secret', $encoded);
        $this->assertStringNotContainsString('prefixed-secret', $encoded);
        $this->assertStringNotContainsString('network-path-secret', $encoded);
        $this->assertStringNotContainsString('nested-query-secret', $encoded);
        $this->assertStringNotContainsString('input-secret', $encoded);
        $this->assertStringNotContainsString('authorization-secret', $encoded);
        $this->assertStringNotContainsString('cookie-secret', $encoded);
        $this->assertStringNotContainsString('signature-secret', $encoded);
        $this->assertStringContainsString('json=1', $encoded);
    }

    public function test_it_deduplicates_database_outage_across_exception_types_and_urls(): void
    {
        Cache::flush();
        config([
            'app.env' => 'production',
            'mail.error_reporting.dedup_enabled' => true,
            'mail.error_reporting.dedup_environments' => ['production'],
            'mail.error_reporting.dedup_ttl' => 900,
        ]);

        $pdoException = new PDOException('SQLSTATE[HY000] [2002] Connection refused', 2002);
        $queryException = new QueryException(
            'mysql',
            'select * from `new_pages` where `slug` = ?',
            ['legacy/path.php'],
            new PDOException('SQLSTATE[HY000] [2002] Connection refused', 2002),
        );

        $this->assertFalse(ErrorMailThrottle::shouldThrottle(
            $pdoException,
            'https://example.test/system/schedule/[hidden]?json=1',
        ));
        $this->assertTrue(ErrorMailThrottle::shouldThrottle(
            $queryException,
            'https://example.test/legacy/path.php',
        ));
    }

    /**
     * @param  class-string<MaxAttemptsExceededException>  $exceptionClass
     * @param  class-string  $queuedJobClass
     */
    private function queueException(string $exceptionClass, string $queuedJobClass): MaxAttemptsExceededException
    {
        $job = Mockery::mock(Job::class);
        $job->shouldReceive('resolveQueuedJobClass')->zeroOrMoreTimes()->andReturn($queuedJobClass);

        $exception = new $exceptionClass('Queue lifecycle failure.');
        $exception->job = $job;

        return $exception;
    }
}
