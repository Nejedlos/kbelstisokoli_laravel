<?php

namespace App\Services\Dmarc;

use App\Models\Dmarc\DmarcIpEnrichment;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class DmarcIpEnrichmentService
{
    protected bool $enabled;
    protected int $cacheHours;

    public function __construct()
    {
        $this->enabled = config('dmarc.ip_enrichment.enabled', (bool) env('DMARC_IP_ENRICHMENT_ENABLED', true));
        $this->cacheHours = (int) config('dmarc.ip_enrichment.cache_hours', (int) env('DMARC_IP_ENRICHMENT_CACHE_HOURS', 168));
    }

    public function enrich(string $ip): DmarcIpEnrichment
    {
        $enrichment = DmarcIpEnrichment::firstOrNew(['ip_address' => $ip]);

        if (!$this->enabled) {
            return $enrichment;
        }

        $now = now();
        $enrichment->times_seen++;
        $enrichment->last_seen_at = $now;
        if (!$enrichment->exists) {
            $enrichment->first_seen_at = $now;
        }

        // Pokud je lookup starší než cacheHours, provedeme nový
        if (!$enrichment->last_lookup_at || $enrichment->last_lookup_at->diffInHours($now) >= $this->cacheHours) {
            $this->performLookup($enrichment);
        }

        $enrichment->save();

        return $enrichment;
    }

    protected function performLookup(DmarcIpEnrichment $enrichment): void
    {
        try {
            $ip = $enrichment->ip_address;

            // Reverse DNS
            $hostname = gethostbyaddr($ip);
            $enrichment->reverse_dns = ($hostname !== $ip) ? $hostname : null;

            // V PHP bez externích knihoven těžko získáme ASN/Org/Country bez API.
            // Ale zkusíme aspoň něco z gethostbyaddr pokud se podařilo.

            $enrichment->last_lookup_at = now();
            $enrichment->lookup_status = 'success';

            Log::info("DMARC IP Enrichment: Lookup success for {$ip} -> {$enrichment->reverse_dns}");
        } catch (\Exception $e) {
            $enrichment->lookup_status = 'error';
            Log::warning("DMARC IP Enrichment: Lookup failed for {$enrichment->ip_address}: " . $e->getMessage());
        }
    }
}
