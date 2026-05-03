<?php

namespace App\Services\Dmarc;

class DmarcClassifier
{
    public const STATUS_OK = 'OK';
    public const STATUS_WARNING = 'Warning';
    public const STATUS_CRITICAL = 'Critical';

    /**
     * Classify a DMARC record and return status and recommended action.
     */
    public function classify(array $record, array $metadata): array
    {
        $dkim = $record['dkim_result'] === 'pass';
        $spf = $record['spf_result'] === 'pass';
        $disposition = $record['disposition'];

        // Alignment detection (simple version)
        $headerFrom = $this->getMainDomain($record['identifiers']['header_from']);

        $dkimAligned = false;
        foreach ($record['auth_results']['dkim'] as $d) {
            if ($d['result'] === 'pass' && $this->isAligned($headerFrom, $d['domain'], $metadata['policy_published']['adkim'] ?? 'r')) {
                $dkimAligned = true;
                break;
            }
        }

        $spfAligned = false;
        foreach ($record['auth_results']['spf'] as $s) {
            if ($s['result'] === 'pass' && $this->isAligned($headerFrom, $s['domain'], $metadata['policy_published']['aspf'] ?? 'r')) {
                $spfAligned = true;
                break;
            }
        }

        $status = self::STATUS_OK;
        $recommendation = null;

        if (!$dkimAligned && !$spfAligned) {
            $status = self::STATUS_CRITICAL;
            if ($disposition === 'none') {
                $recommendation = "Kritické selhání: E-mail neprošel SPF ani DKIM zarovnáním. Aktuální politika 'none' jej propustila, ale při 'quarantine/reject' bude zahozen. Prověřte, zda IP {$record['source_ip']} je legitimní odesílatel.";
            } else {
                $recommendation = "Kritické selhání: E-mail byl zablokován nebo doručen do spamu ({$disposition}). Prověřte nastavení SPF a DKIM pro IP {$record['source_ip']}.";
            }
        } elseif (!$dkimAligned || !$spfAligned) {
            $status = self::STATUS_WARNING;
            $missing = !$dkimAligned ? 'DKIM' : 'SPF';
            $recommendation = "Varování: Chybí {$missing} zarovnání. E-mail sice prošel díky druhému faktoru, ale konfigurace není ideální. Zkontrolujte nastavení pro IP {$record['source_ip']}.";
        }

        return [
            'status' => $status,
            'recommended_action' => $recommendation,
            'dkim_aligned' => $dkimAligned,
            'spf_aligned' => $spfAligned,
        ];
    }

    protected function isAligned(string $source, string $target, string $mode): bool
    {
        if ($mode === 's') { // strict
            return strtolower($source) === strtolower($target);
        }
        // relaxed (default)
        return $this->getMainDomain($source) === $this->getMainDomain($target);
    }

    protected function getMainDomain(string $domain): string
    {
        $domain = strtolower(trim($domain));
        $parts = explode('.', $domain);
        if (count($parts) <= 2) {
            return $domain;
        }
        return implode('.', array_slice($parts, -2));
    }
}
