<?php

namespace App\Services\Dmarc;

use Illuminate\Support\Facades\Log;
use ZipArchive;

class AttachmentDecoder
{
    /**
     * Decode attachment content (ZIP or GZIP) and return XML string.
     */
    public function decode(string $content, string $filename): ?string
    {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        try {
            if ($extension === 'zip') {
                return $this->decodeZip($content);
            }

            if ($extension === 'gz' || $extension === 'gzip') {
                return $this->decodeGzip($content);
            }

            if ($extension === 'xml') {
                return $content;
            }
        } catch (\Exception $e) {
            Log::error("DMARC AttachmentDecoder error: " . $e->getMessage(), [
                'filename' => $filename,
            ]);
        }

        return null;
    }

    protected function decodeZip(string $content): ?string
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'dmarc_zip');
        file_put_contents($tempFile, $content);

        $zip = new ZipArchive();
        if ($zip->open($tempFile) === true) {
            // Predpokladame, ze v ZIPu je jen jeden soubor (XML)
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $name = $zip->getNameIndex($i);
                if (str_ends_with(strtolower($name), '.xml')) {
                    $xmlContent = $zip->getFromIndex($i);
                    $zip->close();
                    unlink($tempFile);
                    return $xmlContent;
                }
            }
            $zip->close();
        }

        unlink($tempFile);
        return null;
    }

    protected function decodeGzip(string $content): ?string
    {
        return gzdecode($content) ?: null;
    }
}
