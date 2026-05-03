<?php

namespace App\Services\Dmarc;

use App\Models\Dmarc\DmarcRecord;
use App\Models\Dmarc\DmarcDnsSnapshot;

class DmarcPolicyReadinessService
{
    public function evaluate(string $domain): array
    {
        $records = DmarcRecord::whereHas('report', function($q) use ($domain) {
            $q->where('domain', $domain);
        })->where('created_at', '>=', now()->subDays(30))->get();

        if ($records->isEmpty()) {
            return [
                'readiness_score' => 0,
                'explanation' => 'Nedostatek dat pro vyhodnocení (posledních 30 dní).',
                'recommended_next_policy' => 'keep_none',
            ];
        }

        $totalCount = $records->sum('count');
        $passCount = $records->filter(fn($r) => $r->dkim_aligned || $r->spf_aligned)->sum('count');

        $passPercentage = ($totalCount > 0) ? ($passCount / $totalCount) * 100 : 0;

        $blockers = [];
        $requiredActions = [];

        $misconfiguredKnownSenders = $records->filter(fn($r) => $r->known_sender_id && !$r->dkim_aligned && !$r->spf_aligned);

        if ($misconfiguredKnownSenders->isNotEmpty()) {
            $blockers[] = "Existují legitimní odesílatelé se špatnou konfigurací.";
            foreach ($misconfiguredKnownSenders->pluck('knownSender.name')->unique() as $name) {
                $requiredActions[] = "Opravit konfiguraci pro {$name}.";
            }
        }

        $score = $passPercentage;
        if (!empty($blockers)) {
            $score = min($score, 40);
        }

        $recommendedPolicy = 'keep_none';
        if ($score > 99) {
            $recommendedPolicy = 'quarantine_pct_100';
        } elseif ($score > 90) {
            $recommendedPolicy = 'quarantine_pct_25';
        }

        return [
            'domain' => $domain,
            'readiness_score' => round($score, 2),
            'pass_percentage' => round($passPercentage, 2),
            'total_messages' => $totalCount,
            'blockers' => $blockers,
            'required_actions' => $requiredActions,
            'recommended_next_policy' => $recommendedPolicy,
            'explanation' => $this->getExplanation($score, $blockers),
        ];
    }

    protected function getExplanation(float $score, array $blockers): string
    {
        if (!empty($blockers)) {
            return "Doména není připravena na zpřísnění politiky kvůli existujícím blokátorům u legitimních služeb.";
        }
        if ($score > 99) {
            return "Doména vykazuje výbornou stabilitu. Je bezpečné přejít na přísnější politiku.";
        }
        if ($score > 90) {
            return "Většina provozu je v pořádku. Doporučujeme začít s mírnou politikou quarantine (např. pct=25).";
        }
        return "Příliš mnoho e-mailů neprochází autentizací. Prověřte neznámé zdroje nebo chybějící konfiguraci.";
    }
}
