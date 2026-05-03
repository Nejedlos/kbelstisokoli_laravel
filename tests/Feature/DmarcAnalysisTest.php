<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Dmarc\DmarcReport;
use App\Models\Dmarc\DmarcRecord;
use App\Models\Dmarc\DmarcMailbox;
use App\Models\Dmarc\DmarcAuthorizedSender;
use App\Services\Dmarc\DmarcAnalysisService;
use App\Services\Dmarc\DmarcIpEnrichmentService;
use App\Services\Dmarc\DmarcDnsCheckService;
use App\Services\Dmarc\DmarcRecommendationService;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class DmarcAnalysisTest extends TestCase
{
    use DatabaseTransactions;

    protected DmarcAnalysisService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new DmarcAnalysisService(
            app(DmarcIpEnrichmentService::class),
            app(DmarcDnsCheckService::class),
            app(DmarcRecommendationService::class)
        );
    }

    public function test_it_can_analyze_a_critical_record()
    {
        $mailbox = DmarcMailbox::create([
            'email' => 'test@example.com',
            'status' => 'active'
        ]);

        $report = DmarcReport::create([
            'mailbox_id' => $mailbox->id,
            'message_uid' => '1',
            'attachment_filename' => 'report.xml',
            'attachment_sha256' => 'abc',
            'org_name' => 'google.com',
            'report_id' => '123',
            'domain' => 'kbelstisokoli.cz',
            'date_start' => now(),
            'date_end' => now(),
        ]);

        $record = DmarcRecord::create([
            'report_id' => $report->id,
            'source_ip' => '1.2.3.4',
            'count' => 100,
            'disposition' => 'none',
            'dkim_result' => 'fail',
            'spf_result' => 'fail',
            'dkim_aligned' => false,
            'spf_aligned' => false,
            'status' => 'Critical',
        ]);

        $analysis = $this->service->analyze($record, $report);

        $this->assertEquals('critical', $analysis['severity']);
        $this->assertEquals('spoofing_suspected', $analysis['event_type']);
        $this->assertGreaterThanOrEqual(90, $analysis['risk_score']);

        $record->refresh();
        $this->assertEquals('critical', $record->severity);
        $this->assertNotNull($record->recommendations);
        $this->assertNotNull($record->analysis);
    }

    public function test_known_sender_reduces_risk()
    {
        $mailbox = DmarcMailbox::create(['email' => 'test2@example.com']);
        $report = DmarcReport::create([
            'mailbox_id' => $mailbox->id,
            'message_uid' => '2',
            'attachment_filename' => 'report2.xml',
            'attachment_sha256' => 'def',
            'org_name' => 'seznam.cz',
            'report_id' => '456',
            'domain' => 'kbelstisokoli.cz',
        ]);

        // Přidáme známého odesílatele
        DmarcAuthorizedSender::create([
            'name' => 'Trusted Service',
            'domain_name' => 'kbelstisokoli.cz',
            'allowed_ips' => ['5.6.7.8'],
            'is_active' => true,
        ]);

        $record = DmarcRecord::create([
            'report_id' => $report->id,
            'source_ip' => '5.6.7.8', // Tato IP je známá
            'count' => 10,
            'dkim_aligned' => false,
            'spf_aligned' => false,
            'status' => 'Warning',
        ]);

        $analysis = $this->service->analyze($record, $report);

        $this->assertEquals('medium', $analysis['severity']);
        $this->assertEquals('legitimate_sender_misconfigured', $analysis['event_type']);
        $this->assertNotNull($analysis['known_sender']);
    }
}
