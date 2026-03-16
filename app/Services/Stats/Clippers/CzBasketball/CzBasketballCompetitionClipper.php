<?php

namespace App\Services\Stats\Clippers\CzBasketball;

use App\Services\Stats\Contracts\ClipperInterface;
use App\Services\Stats\DTO\ClipDTO;
use Symfony\Component\DomCrawler\Crawler;

class CzBasketballCompetitionClipper implements ClipperInterface
{
    /**
     * Vyřízne relevantní fragmenty ze stránky soutěže cz.basketball.
     */
    public function clip(string $html, ?string $baseUrl = 'https://cz.basketball'): array
    {
        $crawler = new Crawler($html);
        $clips = [];

        // 1. COMPETITION HEADER CLIP
        $header = $this->extractCompetitionHeader($crawler, $baseUrl);
        if ($header) $clips[] = $header;

        // 2. STANDINGS TABLE CLIP
        $standings = $this->extractStandingsTable($crawler, $baseUrl);
        if ($standings) $clips[] = $standings;

        // 3. SCHEDULE TABLE CLIP
        $schedule = $this->extractScheduleTable($crawler, $baseUrl);
        if ($schedule) $clips[] = $schedule;

        return array_values(array_filter($clips));
    }

    protected function extractCompetitionHeader(Crawler $crawler, ?string $baseUrl): ?ClipDTO
    {
        $h1 = $crawler->filter('h1')->first();
        if ($h1->count() === 0) {
            return null;
        }

        return new ClipDTO(
            id: 'competition_header',
            htmlFragment: '<h1>' . $h1->text() . '</h1>',
            textHint: 'Competition name',
            evidence: ['competition_name' => trim($h1->text())]
        );
    }

    protected function extractStandingsTable(Crawler $crawler, ?string $baseUrl): ?ClipDTO
    {
        // Standings table signature: "Pořadí", "Tým", "Z", "V", "P", "Skóre"
        $table = $crawler->filter('table')->reduce(function (Crawler $node) {
            $html = $node->text();
            return str_contains($html, 'Pořadí') && str_contains($html, 'Tým') &&
                   str_contains($html, 'V') && str_contains($html, 'P') &&
                   str_contains($html, 'Skóre');
        })->first();

        if ($table->count() === 0) {
            return null;
        }

        return new ClipDTO(
            id: 'competition_standings',
            htmlFragment: $this->sanitizeFragment($table, $baseUrl),
            textHint: 'League standings table',
            evidence: ['row_count' => $table->filter('tr')->count()]
        );
    }

    protected function extractScheduleTable(Crawler $crawler, ?string $baseUrl): ?ClipDTO
    {
        // Schedule table signature: "Datum", "Domácí", "Hosté", "Skóre", "Detail"
        // Nebo "Číslo utkání", "Kolo"
        $table = $crawler->filter('table')->reduce(function (Crawler $node) {
            $html = $node->text();
            return (str_contains($html, 'Domácí') && str_contains($html, 'Hosté') && str_contains($html, 'Skóre')) ||
                   (str_contains($html, 'Datum') && str_contains($html, 'Domácí') && str_contains($html, 'Hosté'));
        })->first();

        if ($table->count() === 0) {
            return null;
        }

        $links = $this->extractLinks($table, $baseUrl, 'matches');

        return new ClipDTO(
            id: 'competition_schedule',
            htmlFragment: $this->sanitizeFragment($table, $baseUrl),
            textHint: 'Competition schedule and results',
            links: $links,
            evidence: ['row_count' => $table->filter('tr')->count()]
        );
    }

    protected function sanitizeFragment(Crawler $node, ?string $baseUrl): string
    {
        $html = $node->outerHtml();

        // Odstranění nepotřebných skriptů, stylů atd. (jednoduchá verze)
        $html = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', "", $html);
        $html = preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', "", $html);

        return $html;
    }

    protected function extractLinks(Crawler $node, ?string $baseUrl, string $context): array
    {
        $links = [];
        $node->filter('a')->each(function (Crawler $a) use (&$links, $baseUrl, $context) {
            $href = $a->attr('href');
            if (!$href) return;

            // Normalize relative URL
            if (str_starts_with($href, '/') && $baseUrl) {
                $parts = parse_url($baseUrl);
                $href = ($parts['scheme'] ?? 'https') . '://' . ($parts['host'] ?? 'cz.basketball') . $href;
            }

            $links[] = [
                'url' => $href,
                'text' => trim($a->text()),
                'context' => $context
            ];
        });

        return array_unique($links, SORT_REGULAR);
    }

    public function buildCnh(array $clips): string
    {
        $cnh = '<!DOCTYPE html><html><body>';
        foreach ($clips as $clip) {
            $cnh .= "<!-- CLIP: {$clip->id} HINT: {$clip->textHint} -->\n";
            $cnh .= $clip->htmlFragment . "\n\n";
        }
        $cnh .= '</body></html>';
        return $cnh;
    }

    public function buildExtractedLinksJson(array $clips): string
    {
        $allLinks = [];
        foreach ($clips as $clip) {
            foreach ($clip->links as $link) {
                $allLinks[] = $link;
            }
        }
        return json_encode(array_values(array_unique($allLinks, SORT_REGULAR)), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
}
