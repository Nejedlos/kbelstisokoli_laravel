<?php

namespace Tests\Feature;

use App\Models\Dmarc\DmarcMailbox;
use App\Services\Dmarc\DmarcImapService;
use Mockery;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use RuntimeException;
use Tests\TestCase;

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class DmarcImapServiceTest extends TestCase
{
    private function mailbox(): DmarcMailbox
    {
        return DmarcMailbox::create([
            'email' => 'dmarc@example.test',
            'host' => 'mail.example.test',
            'port' => 993,
            'encryption' => 'ssl',
            'username' => 'dmarc@example.test',
            'password' => 'test-password',
            'status' => 'active',
        ]);
    }

    private function fakeImap(): Mockery\MockInterface
    {
        require __DIR__.'/../Fixtures/Dmarc/imap.php';
        $imap = Mockery::mock();
        $this->app->instance('test.imap', $imap);

        return $imap;
    }

    public function test_connection_failure_is_recorded_before_both_queues_are_drained(): void
    {
        $imap = $this->fakeImap();
        $mailbox = $this->mailbox();
        $error = 'Can not authenticate to IMAP server: [UNAVAILABLE] Temporary authentication failure.';
        $imap->shouldReceive('open')->once()->andReturn(false);
        $imap->shouldReceive('lastError')->once()->ordered()->andReturn($error);
        $imap->shouldReceive('close')->never();
        $imap->shouldReceive('errors')->once()->ordered()->andReturn([$error]);
        $imap->shouldReceive('alerts')->once()->ordered()->andReturn(['Temporary service problem']);

        $run = app(DmarcImapService::class)->ingest($mailbox);

        $this->assertSame(1, $run->errors_count);
        $this->assertNotNull($run->finished_at);
        $this->assertStringContainsString($error, $run->log);
        $this->assertSame($error, $mailbox->fresh()->last_error);
    }

    public function test_empty_mailbox_closes_connection_and_drains_queues(): void
    {
        $imap = $this->fakeImap();
        $imap->shouldReceive('open')->once()->andReturn('connection');
        $imap->shouldReceive('search')->once()->with('connection', 'ALL')->andReturn(false);
        $imap->shouldReceive('close')->once()->with('connection')->ordered()->andReturn(true);
        $imap->shouldReceive('errors')->once()->ordered()->andReturn(false);
        $imap->shouldReceive('alerts')->once()->ordered()->andReturn(false);

        $run = app(DmarcImapService::class)->ingest($this->mailbox());

        $this->assertNotNull($run->finished_at);
        $this->assertStringContainsString('Nenalezeny žádné e-maily.', $run->log);
    }

    public function test_completed_import_closes_connection_and_drains_queues(): void
    {
        $imap = $this->fakeImap();
        $imap->shouldReceive('open')->once()->andReturn('connection');
        $imap->shouldReceive('search')->once()->andReturn([1]);
        $imap->shouldReceive('overview')->once()->andReturn([]);
        $imap->shouldReceive('close')->once()->ordered()->andReturn(true);
        $imap->shouldReceive('errors')->once()->ordered()->andReturn(false);
        $imap->shouldReceive('alerts')->once()->ordered()->andReturn(false);

        $run = app(DmarcImapService::class)->ingest($this->mailbox());

        $this->assertSame(1, $run->messages_found);
        $this->assertSame(0, $run->errors_count);
        $this->assertNotNull($run->finished_at);
    }

    public function test_search_exception_still_closes_and_cleans_up_without_hiding_failure(): void
    {
        $imap = $this->fakeImap();
        $imap->shouldReceive('open')->once()->andReturn('connection');
        $imap->shouldReceive('search')->once()->andThrow(new RuntimeException('Search failed'));
        $imap->shouldReceive('close')->once()->ordered()->andReturn(true);
        $imap->shouldReceive('errors')->once()->ordered()->andReturn(['Search failed']);
        $imap->shouldReceive('alerts')->once()->ordered()->andReturn(false);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Search failed');
        app(DmarcImapService::class)->ingest($this->mailbox());
    }

    public function test_close_exception_still_drains_both_queues(): void
    {
        $imap = $this->fakeImap();
        $imap->shouldReceive('open')->once()->andReturn('connection');
        $imap->shouldReceive('search')->once()->andReturn(false);
        $imap->shouldReceive('close')->once()->ordered()->andThrow(new RuntimeException('Close failed'));
        $imap->shouldReceive('errors')->once()->ordered()->andReturn(['Close failed']);
        $imap->shouldReceive('alerts')->once()->ordered()->andReturn(false);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Close failed');
        app(DmarcImapService::class)->ingest($this->mailbox());
    }

    public function test_real_imap_failure_leaves_no_shutdown_errors_or_alerts(): void
    {
        if (! extension_loaded('imap')) {
            $this->markTestSkipped('The IMAP extension is not available.');
        }

        $service = Mockery::mock(DmarcImapService::class)->makePartial()->shouldAllowMockingProtectedMethods();
        // Invalid local syntax fails before any DNS lookup or network connection.
        $service->shouldReceive('getConnectionString')->andReturn('{');
        $mailbox = $this->mailbox();

        try {
            $run = $service->ingest($mailbox);
            $this->assertSame(1, $run->errors_count);
            $this->assertNotEmpty($mailbox->fresh()->last_error);
            $this->assertFalse(imap_errors());
            $this->assertFalse(imap_alerts());
        } finally {
            imap_errors();
            imap_alerts();
        }
    }
}
