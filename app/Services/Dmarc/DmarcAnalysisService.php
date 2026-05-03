<?php

namespace App\Services\Dmarc;

use App\Models\Dmarc\DmarcRecord;
use App\Models\Dmarc\DmarcReport;
use App\Models\Dmarc\DmarcAuthorizedSender;
use Illuminate\Support\Facades\Log;

class DmarcAnalysisService
{
    public function __construct(
        protected DmarcIpEnrichmentService $ipService,
        protected DmarcDnsCheckService $dnsService,
        protected DmarcRecommendationService $recommendationService
    ) {}

    public function analyze(DmarcRecord $record, DmarcReport $report): array
    {
        // 1. IP Enrichment
        $enrichment = $this->ipService->enrich($record->source_ip);

        // 2. Najít legitimního odesílatele
        $knownSender = $this->findKnownSender($record, $report, $enrichment);

        // 3. Analýza výsledků
        $dkimAligned = $record->dkim_aligned;
        $spfAligned = $record->spf_aligned;
        $dmarcPass = $dkimAligned || $spfAligned;

        $eventType = $this->determineEventType($record, $dmarcPass, $knownSender);
        $severity = $this->calculateSeverity($record, $dmarcPass, $knownSender, $eventType);
        $riskScore = $this->calculateRiskScore($record, $dmarcPass, $knownSender, $severity);

        $analysis = [
            'dkim_aligned' => $dkimAligned,
            'spf_aligned' => $spfAligned,
            'dmarc_pass' => $dmarcPass,
            'event_type' => $eventType,
            'source_ip' => $record->source_ip,
            'reverse_dns' => $enrichment->reverse_dns,
            'disposition' => $record->disposition,
            'known_sender' => $knownSender ? $knownSender->only(['id', 'name', 'sender_type']) : null,
            'severity' => $severity,
            'risk_score' => $riskScore,
        ];

        // 4. Doporučení
        $recommendations = $this->recommendationService->getRecommendations($analysis);

        // 5. Uložení výsledků do recordu
        $record->update([
            'known_sender_id' => $knownSender?->id,
            'analysis' => $analysis,
            'severity' => $severity,
            'risk_score' => $riskScore,
            'recommendations' => $recommendations,
            'analyzed_at' => now(),
        ]);

        return $analysis;
    }

    protected function findKnownSender(DmarcRecord $record, DmarcReport $report, $enrichment): ?DmarcAuthorizedSender
    {
        // Hledáme podle IP, CIDR, domény SPF/DKIM
        return DmarcAuthorizedSender::where('is_active', true)
            ->where(function($query) use ($record, $enrichment) {
                $query->whereJsonContains('allowed_ips', $record->source_ip)
                      ->orWhereJsonContains('allowed_spf_domains', $record->spf_domain)
                      ->orWhereJsonContains('allowed_dkim_domains', $record->dkim_domain);
            })->first();
    }

    protected function determineEventType(DmarcRecord $record, bool $dmarcPass, ?DmarcAuthorizedSender $knownSender): string
    {
        if ($dmarcPass) {
            return $knownSender ? 'legitimate_sender_ok' : 'unknown_sender_ok';
        }

        if ($knownSender) {
            return 'legitimate_sender_misconfigured';
        }

        // Pokud selhalo obojí a není to známý sender
        if (!$record->dkim_aligned && !$record->spf_aligned) {
            return 'spoofing_suspected';
        }

        return 'unknown_sender_fail';
    }

    protected function calculateSeverity(DmarcRecord $record, bool $dmarcPass, ?DmarcAuthorizedSender $knownSender, string $eventType): string
    {
        if ($dmarcPass) return 'info';

        if ($eventType === 'spoofing_suspected') {
            return ($record->count > 10) ? 'critical' : 'high';
        }

        if ($knownSender) {
            return 'medium'; // Legitimní, ale blbě nastavený
        }

        return 'low';
    }

    protected function calculateRiskScore(DmarcRecord $record, bool $dmarcPass, ?DmarcAuthorizedSender $knownSender, string $severity): int
    {
        if ($dmarcPass) return 5;

        $score = 50;
        if ($severity === 'critical') $score = 95;
        if ($severity === 'high') $score = 80;
        if ($severity === 'medium') $score = 40;

        if (!$knownSender) $score += 10;
        if ($record->disposition !== 'none') $score -= 5; // Už bylo zablokováno, riziko je nižší

        return min(100, max(0, $score));
    }
}
