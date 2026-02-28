<?php

namespace App\Services;

use Illuminate\Support\Str;

class TextExtractionService
{
    /**
     * Extrahuje plaintext z HTML, očištěný od šumu.
     */
    public function extractPlaintext(string $html): string
    {
        if (empty($html)) {
            return '';
        }

        // Odstranění nepotřebných tagů i s obsahem
        $html = preg_replace('/<(script|style|nav|footer|header|noscript)[^>]*>.*?<\/\1>/is', ' ', $html);

        // Odstranění komentářů
        $html = preg_replace('/<!--.*?-->/s', ' ', $html);

        // Nahrazení blokových tagů za mezery (aby se slova neslepila)
        $html = preg_replace('/<(p|div|h[1-6]|li|tr|section|article|header|footer|nav|aside|blockquote|canvas|details|figcaption|figure|form|output|pre|video|address|main|hr|table|td|th)[^>]*>/i', ' $0 ', $html);

        // Dekódování HTML entit
        $text = html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // Odstranění zbývajících HTML tagů
        $text = strip_tags($text);

        // Normalizace bílých znaků (nahrazení vícenásobných mezer, tabulátorů a konců řádků za jednu mezeru)
        $text = preg_replace('/\s+/', ' ', $text);

        return trim($text);
    }

    /**
     * Rozdělí text na chunky.
     */
    public function chunkText(string $text, int $chunkSize = 1000, int $overlap = 100): array
    {
        if (empty($text)) {
            return [];
        }

        if (mb_strlen($text) <= $chunkSize) {
            return [$text];
        }

        $chunks = [];
        $start = 0;
        $textLength = mb_strlen($text);

        while ($start < $textLength) {
            $chunk = mb_substr($text, $start, $chunkSize);
            $chunks[] = $chunk;
            $start += ($chunkSize - $overlap);
        }

        return $chunks;
    }
}
