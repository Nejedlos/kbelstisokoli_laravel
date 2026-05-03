<?php

namespace App\Services\Dmarc;

use App\Models\Dmarc\DmarcMailbox;
use App\Models\Dmarc\DmarcRecord;
use App\Models\Dmarc\DmarcReport;
use App\Models\Dmarc\DmarcRun;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class DmarcImapService
{
    public function __construct(
        protected AttachmentDecoder $decoder,
        protected DmarcXmlParser $parser,
        protected DmarcClassifier $classifier,
        protected IncidentService $incidentService
    ) {}

    public function ingest(DmarcMailbox $mailbox): DmarcRun
    {
        $run = DmarcRun::create([
            'mailbox_id' => $mailbox->id,
            'started_at' => now(),
            'log' => "Spouštím import pro {$mailbox->email}...\n",
        ]);

        $connectionString = $this->getConnectionString($mailbox);
        $inbox = @imap_open($connectionString, $mailbox->username, $mailbox->password);

        if (!$inbox) {
            $error = imap_last_error();
            $run->update([
                'finished_at' => now(),
                'errors_count' => 1,
                'log' => $run->log . "Chyba připojení k IMAP: {$error}\n",
            ]);
            $mailbox->update(['last_error' => $error]);
            return $run;
        }

        $emails = imap_search($inbox, 'ALL'); // Můžeme později optimalizovat na UNSEEN nebo SINCE date

        if (!$emails) {
            $run->update([
                'finished_at' => now(),
                'log' => $run->log . "Nenalezeny žádné e-maily.\n",
            ]);
            imap_close($inbox);
            return $run;
        }

        $foundCount = count($emails);
        $processedCount = 0;
        $errorsCount = 0;

        foreach ($emails as $mailUid) {
            try {
                $overview = imap_fetch_overview($inbox, $mailUid, 0);
                $overview = $overview[0] ?? null;

                if (!$overview) continue;

                $structure = imap_fetchstructure($inbox, $mailUid);

                if (isset($structure->parts)) {
                    foreach ($structure->parts as $partNum => $part) {
                        if ($this->isDmarcAttachment($part)) {
                            $attachment = imap_fetchbody($inbox, $mailUid, $partNum + 1);
                            $attachment = $this->decodeBody($attachment, $part->encoding);
                            $filename = $this->getFilename($part);

                            if ($this->processAttachment($mailbox, $attachment, $filename, (string)$overview->uid, $overview->date, $run)) {
                                $processedCount++;
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                $errorsCount++;
                $run->log .= "Chyba u UID {$mailUid}: " . $e->getMessage() . "\n";
            }
        }

        $run->update([
            'finished_at' => now(),
            'messages_found' => $foundCount,
            'reports_processed' => $processedCount,
            'errors_count' => $errorsCount,
            'log' => $run->log . "Import dokončen. Zpracováno {$processedCount} reportů.\n",
        ]);

        $mailbox->update([
            'last_checked_at' => now(),
            'last_error' => null,
        ]);

        imap_close($inbox);
        return $run;
    }

    protected function getConnectionString(DmarcMailbox $mailbox): string
    {
        $options = '/imap';
        if ($mailbox->encryption === 'ssl') $options .= '/ssl';
        if ($mailbox->encryption === 'tls') $options .= '/tls/novalidate-cert';

        return "{" . "{$mailbox->host}:{$mailbox->port}{$options}" . "}INBOX";
    }

    protected function isDmarcAttachment($part): bool
    {
        $filename = $this->getFilename($part);
        if (!$filename) return false;

        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        return in_array($ext, ['zip', 'gz', 'gzip', 'xml']);
    }

    protected function getFilename($part): ?string
    {
        $filename = null;

        if ($part->ifdparameters) {
            foreach ($part->dparameters as $object) {
                $attr = strtolower($object->attribute);
                if ($attr === 'filename' || $attr === 'filename*') {
                    $filename = $object->value;
                    break;
                }
            }
        }

        if (!$filename && $part->ifparameters) {
            foreach ($part->parameters as $object) {
                $attr = strtolower($object->attribute);
                if ($attr === 'name' || $attr === 'name*') {
                    $filename = $object->value;
                    break;
                }
            }
        }

        if ($filename && (str_contains($filename, "''") || str_contains($filename, "?="))) {
            // Velmi hrubé pročištění pro RFC 2231/2047 pokud je to nutné
            // Pro účely testu to zkusíme nechat tak, isDmarcAttachment kouká na příponu
            $filename = urldecode(preg_replace('/^.*\'\'/', '', $filename));
        }

        return $filename;
    }

    protected function decodeBody($data, $encoding): string
    {
        if ($encoding == 3) return base64_decode($data);
        if ($encoding == 4) return quoted_printable_decode($data);
        return $data;
    }

    protected function processAttachment(DmarcMailbox $mailbox, string $content, string $filename, string $uid, string $date, DmarcRun $run): bool
    {
        $sha256 = hash('sha256', $content);

        if (DmarcReport::where('mailbox_id', $mailbox->id)->where('attachment_sha256', $sha256)->exists()) {
            return false;
        }

        $xmlContent = $this->decoder->decode($content, $filename);
        if (!$xmlContent) {
            $run->log .= "Nepodařilo se dekódovat přílohu {$filename} (UID {$uid})\n";
            return false;
        }

        $parsedData = $this->parser->parse($xmlContent);
        $metadata = $parsedData['metadata'];

        $receivedAt = date('Y-m-d H:i:s', strtotime($date));
        $dateStart = date('Y-m-d H:i:s', $metadata['date_range']['begin']);
        $dateEnd = date('Y-m-d H:i:s', $metadata['date_range']['end']);

        // Save XML for later download/viewing
        $xmlPath = "dmarc/reports/" . date('Y/m/d') . "/{$sha256}.xml";
        Storage::put($xmlPath, $xmlContent);

        $report = DmarcReport::create([
            'mailbox_id' => $mailbox->id,
            'message_uid' => $uid,
            'attachment_filename' => $filename,
            'attachment_sha256' => $sha256,
            'org_name' => $metadata['org_name'],
            'report_id' => $metadata['report_id'],
            'domain' => $metadata['policy_published']['domain'],
            'date_start' => $dateStart,
            'date_end' => $dateEnd,
            'policy_published_json' => $metadata['policy_published'],
            'raw_xml_path' => $xmlPath,
            'received_at' => $receivedAt,
        ]);

        foreach ($parsedData['records'] as $recordData) {
            $classification = $this->classifier->classify($recordData, $metadata);

            $record = DmarcRecord::create([
                'report_id' => $report->id,
                'source_ip' => $recordData['source_ip'],
                'count' => $recordData['count'],
                'disposition' => $recordData['disposition'],
                'dkim_result' => $recordData['dkim_result'],
                'spf_result' => $recordData['spf_result'],
                'dkim_aligned' => $classification['dkim_aligned'],
                'spf_aligned' => $classification['spf_aligned'],
                'header_from' => $recordData['identifiers']['header_from'],
                'envelope_from' => $recordData['identifiers']['envelope_from'],
                'dkim_domain' => $recordData['auth_results']['dkim'][0]['domain'] ?? null,
                'spf_domain' => $recordData['auth_results']['spf'][0]['domain'] ?? null,
                'status' => $classification['status'],
                'recommended_action' => $classification['recommended_action'],
            ]);

            $this->incidentService->handleRecord($record, $report);
        }

        return true;
    }
}
