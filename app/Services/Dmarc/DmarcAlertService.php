<?php

namespace App\Services\Dmarc;

use App\Models\Dmarc\DmarcRecord;
use App\Models\Dmarc\DmarcReport;
use App\Models\Dmarc\DmarcAlertEvent;
use App\Notifications\DmarcCriticalEventNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class DmarcAlertService
{
    public function handle(DmarcRecord $record, DmarcReport $report, array $analysis): void
    {
        $enabled = config('dmarc.alerts.enabled', true);
        if (!$enabled) return;

        $minSeverity = config('dmarc.alerts.min_severity', 'critical');
        $severityLevels = ['info' => 0, 'low' => 1, 'medium' => 2, 'high' => 3, 'critical' => 4];

        $currentSeverity = $analysis['severity'] ?? 'info';
        if (($severityLevels[$currentSeverity] ?? 0) < ($severityLevels[$minSeverity] ?? 4)) {
            return;
        }

        // Fingerprint pro deduplikaci
        $fingerprint = md5(implode('|', [
            $report->domain,
            $record->source_ip,
            $analysis['event_type'],
            $record->dkim_result,
            $record->spf_result,
            $record->dkim_aligned ? '1' : '0',
            $record->spf_aligned ? '1' : '0'
        ]));

        $event = DmarcAlertEvent::firstOrNew(['fingerprint' => $fingerprint]);

        $now = now();
        $event->domain = $report->domain;
        $event->source_ip = $record->source_ip;
        $event->report_org = $report->org_name;
        $event->event_type = $analysis['event_type'];
        $event->severity = $currentSeverity;
        $event->risk_score = $analysis['risk_score'];
        $event->payload = $analysis;
        $event->occurrences++;
        $event->last_seen_at = $now;
        if (!$event->exists) {
            $event->first_seen_at = $now;
        }

        $event->save();

        $this->checkAndSendAlert($event);
    }

    protected function checkAndSendAlert(DmarcAlertEvent $event): void
    {
        $email = config('dmarc.alerts.technical_contact_email');
        if (!$email) {
            Log::warning("DMARC Alert: TECHNICAL_CONTACT_EMAIL is not set.");
            return;
        }

        $rateLimitHours = config('dmarc.alerts.rate_limit_hours', 12);
        if ($event->last_email_sent_at && $event->last_email_sent_at->diffInHours(now()) < $rateLimitHours) {
            Log::info("DMARC Alert: Rate limited for {$event->source_ip} on {$event->domain}");
            return;
        }

        try {
            Notification::route('mail', $email)->notify(new DmarcCriticalEventNotification($event));

            $event->last_email_sent_at = now();
            $event->save();

            Log::info("DEBUG_MAIL: DMARC Alert: Email sent to {$email} for {$event->source_ip} on {$event->domain}");
        } catch (\Exception $e) {
            Log::error("DEBUG_MAIL: DMARC Alert: Failed to send email: " . $e->getMessage());
        }
    }
}
