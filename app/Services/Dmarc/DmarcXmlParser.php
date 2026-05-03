<?php

namespace App\Services\Dmarc;

use SimpleXMLElement;

class DmarcXmlParser
{
    /**
     * Parse DMARC aggregate report XML.
     */
    public function parse(string $xmlContent): array
    {
        $xml = new SimpleXMLElement($xmlContent);

        $metadata = [
            'org_name' => (string) $xml->report_metadata->org_name,
            'report_id' => (string) $xml->report_metadata->report_id,
            'date_range' => [
                'begin' => (int) $xml->report_metadata->date_range->begin,
                'end' => (int) $xml->report_metadata->date_range->end,
            ],
            'policy_published' => [
                'domain' => (string) $xml->policy_published->domain,
                'adkim' => (string) $xml->policy_published->adkim,
                'aspf' => (string) $xml->policy_published->aspf,
                'p' => (string) $xml->policy_published->p,
                'sp' => (string) $xml->policy_published->sp,
                'pct' => (int) $xml->policy_published->pct,
            ],
        ];

        $records = [];
        foreach ($xml->record as $record) {
            $records[] = [
                'source_ip' => (string) $record->row->source_ip,
                'count' => (int) $record->row->count,
                'disposition' => (string) $record->row->policy_evaluated->disposition,
                'dkim_result' => (string) $record->row->policy_evaluated->dkim,
                'spf_result' => (string) $record->row->policy_evaluated->spf,
                'identifiers' => [
                    'header_from' => (string) $record->identifiers->header_from,
                    'envelope_from' => (string) $record->identifiers->envelope_from,
                ],
                'auth_results' => [
                    'dkim' => $this->parseDkimResults($record->auth_results),
                    'spf' => $this->parseSpfResults($record->auth_results),
                ],
            ];
        }

        return [
            'metadata' => $metadata,
            'records' => $records,
        ];
    }

    protected function parseDkimResults($authResults): array
    {
        $results = [];
        if (isset($authResults->dkim)) {
            foreach ($authResults->dkim as $dkim) {
                $results[] = [
                    'domain' => (string) $dkim->domain,
                    'result' => (string) $dkim->result,
                    'selector' => (string) $dkim->selector,
                ];
            }
        }
        return $results;
    }

    protected function parseSpfResults($authResults): array
    {
        $results = [];
        if (isset($authResults->spf)) {
            foreach ($authResults->spf as $spf) {
                $results[] = [
                    'domain' => (string) $spf->domain,
                    'result' => (string) $spf->result,
                    'scope' => (string) $spf->scope,
                ];
            }
        }
        return $results;
    }
}
