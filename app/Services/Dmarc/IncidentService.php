<?php

namespace App\Services\Dmarc;

use App\Models\Dmarc\DmarcIncident;
use App\Models\Dmarc\DmarcRecord;
use App\Models\Dmarc\DmarcReport;
use App\Notifications\Dmarc\CriticalDmarcIncidentNotification;
use Illuminate\Support\Facades\Notification;

class IncidentService
{
    public function handleRecord(DmarcRecord $record, DmarcReport $report): void
    {
        if ($record->status !== DmarcClassifier::STATUS_CRITICAL) {
            return;
        }

        $deduplicationHours = config('dmarc.incident_deduplication_hours', 24);

        $incident = DmarcIncident::where('domain', $report->domain)
            ->where('source_ip', $record->source_ip)
            ->where('state', 'open')
            ->where('last_seen_at', '>=', now()->subHours($deduplicationHours))
            ->first();

        if ($incident) {
            $incident->occurrences_count += $record->count;
            $incident->last_seen_at = now();
            $incident->save();
        } else {
            $incident = DmarcIncident::create([
                'record_id' => $record->id,
                'report_id' => $report->id,
                'domain' => $report->domain,
                'source_ip' => $record->source_ip,
                'severity' => $record->status,
                'title' => "Kritický DMARC fail: {$report->domain} z IP {$record->source_ip}",
                'description' => "Zjištěno v reportu od {$report->org_name}. Disposition: {$record->disposition}, SPF: {$record->spf_result}, DKIM: {$record->dkim_result}.",
                'recommended_action' => $record->recommended_action,
                'occurrences_count' => $record->count,
                'first_seen_at' => now(),
                'last_seen_at' => now(),
                'state' => 'open',
            ]);
        }

        $this->notifyIfNeeded($incident);
    }

    protected function notifyIfNeeded(DmarcIncident $incident): void
    {
        // Notifikovat pouze pokud je to nový incident nebo nebyl notifikován v posledních hodinách dle configu
        $cooldown = config('dmarc.notification_cooldown_hours', 12);

        if (!$incident->notified_at || $incident->notified_at < now()->subHours($cooldown)) {
            $email = config('dmarc.alert_to');
            if ($email) {
                Notification::route('mail', $email)->notify(new CriticalDmarcIncidentNotification($incident));
                $incident->notified_at = now();
                $incident->save();
            }
        }
    }
}
