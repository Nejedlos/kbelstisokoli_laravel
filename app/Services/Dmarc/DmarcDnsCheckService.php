<?php

namespace App\Services\Dmarc;

use App\Models\Dmarc\DmarcDnsSnapshot;
use Illuminate\Support\Facades\Log;

class DmarcDnsCheckService
{
    protected bool $enabled;
    protected int $cacheHours;

    public function __construct()
    {
        $this->enabled = config('dmarc.dns_check.enabled', (bool) env('DMARC_DNS_CHECK_ENABLED', true));
        $this->cacheHours = (int) config('dmarc.dns_check.cache_hours', (int) env('DMARC_DNS_CHECK_CACHE_HOURS', 24));
    }

    public function check(string $domain): DmarcDnsSnapshot
    {
        $snapshot = DmarcDnsSnapshot::where('domain', $domain)
            ->orderBy('checked_at', 'desc')
            ->first();

        if (!$this->enabled) {
            return $snapshot ?: new DmarcDnsSnapshot(['domain' => $domain, 'checked_at' => now()]);
        }

        if (!$snapshot || $snapshot->checked_at->diffInHours(now()) >= $this->cacheHours) {
            $snapshot = $this->performDnsCheck($domain);
        }

        return $snapshot;
    }

    protected function performDnsCheck(string $domain): DmarcDnsSnapshot
    {
        $snapshot = new DmarcDnsSnapshot([
            'domain' => $domain,
            'checked_at' => now(),
            'warnings' => [],
            'recommendations' => [],
        ]);

        try {
            // DMARC check
            $dmarcRecords = @dns_get_record("_dmarc.{$domain}", DNS_TXT);
            if ($dmarcRecords) {
                foreach ($dmarcRecords as $record) {
                    $txt = $record['txt'] ?? '';
                    if (str_starts_with($txt, 'v=DMARC1')) {
                        $snapshot->dmarc_record = $txt;
                        $this->parseDmarcRecord($txt, $snapshot);
                        break;
                    }
                }
            }

            // SPF check
            $spfRecords = @dns_get_record($domain, DNS_TXT);
            $spfFound = [];
            if ($spfRecords) {
                foreach ($spfRecords as $record) {
                    $txt = $record['txt'] ?? '';
                    if (str_starts_with($txt, 'v=spf1')) {
                        $spfFound[] = $txt;
                    }
                }
            }

            $snapshot->spf_exists = count($spfFound) > 0;
            $snapshot->spf_multiple_records = count($spfFound) > 1;
            if ($snapshot->spf_exists) {
                $snapshot->spf_record = $spfFound[0];
            }

            $this->generateDnsRecommendations($snapshot);

            $snapshot->save();
        } catch (\Exception $e) {
            Log::error("DMARC DNS Check: Failed for {$domain}: " . $e->getMessage());
        }

        return $snapshot;
    }

    protected function parseDmarcRecord(string $record, DmarcDnsSnapshot $snapshot): void
    {
        $parts = explode(';', $record);
        $data = [];
        foreach ($parts as $part) {
            $kv = explode('=', trim($part), 2);
            if (count($kv) === 2) {
                $data[strtolower(trim($kv[0]))] = trim($kv[1]);
            }
        }

        $snapshot->dmarc_policy = $data['p'] ?? null;
        $snapshot->dmarc_subdomain_policy = $data['sp'] ?? null;
        $snapshot->dmarc_pct = isset($data['pct']) ? (int) $data['pct'] : 100;
        $snapshot->dmarc_adkim = $data['adkim'] ?? 'r';
        $snapshot->dmarc_aspf = $data['aspf'] ?? 'r';

        if (isset($data['rua'])) {
            $snapshot->dmarc_rua = explode(',', $data['rua']);
        }
        if (isset($data['ruf'])) {
            $snapshot->dmarc_ruf = explode(',', $data['ruf']);
        }
    }

    protected function generateDnsRecommendations(DmarcDnsSnapshot $snapshot): void
    {
        $warnings = [];
        $recs = [];

        if (!$snapshot->dmarc_record) {
            $warnings[] = "DMARC záznam nebyl nalezen.";
            $recs[] = "Vytvořte TXT záznam pro _dmarc.{$snapshot->domain} s hodnotou 'v=DMARC1; p=none; rua=mailto:dmarc@{$snapshot->domain}'.";
        } else {
            if ($snapshot->dmarc_policy === 'none') {
                $recs[] = "Aktuálně používáte politiku 'p=none' (pouze monitoring). Po ověření všech legitimních zdrojů doporučujeme přejít na 'p=quarantine'.";
            }
        }

        if (!$snapshot->spf_exists) {
            $warnings[] = "SPF záznam nebyl nalezen.";
            $recs[] = "Vytvořte SPF záznam pro hlavní doménu.";
        }

        if ($snapshot->spf_multiple_records) {
            $warnings[] = "Nalezeno více SPF záznamů. To je nevalidní konfigurace.";
            $recs[] = "Slučte všechny SPF pravidla do jednoho záznamu.";
        }

        $snapshot->warnings = $warnings;
        $snapshot->recommendations = $recs;
    }
}
