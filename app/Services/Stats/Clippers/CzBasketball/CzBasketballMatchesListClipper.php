<?php

namespace App\Services\Stats\Clippers\CzBasketball;

use App\Services\Stats\Contracts\ClipperInterface;
use App\Services\Stats\DTO\ClipDTO;
use Symfony\Component\DomCrawler\Crawler;

class CzBasketballMatchesListClipper implements ClipperInterface
{
    /**
     * Vyřízne hlavní tabulku se seznamem zápasů.
     */
    public function clip(string $html, ?string $baseUrl = null): array
    {
        $crawler = new Crawler($html);

        $tables = $crawler->filter('table');
        \Log::debug("Clipper: Found {$tables->count()} tables on page.");

        // Heuristika: <table> kde >= 5 řádků obsahuje /zapas/
        $matchesTable = $tables->reduce(function (Crawler $node, $i) {
            $matchLinks = $node->filter('a[href*="/zapas/"]');
            \Log::debug("Clipper: Table #{$i} has {$matchLinks->count()} match links.");
            return $matchLinks->count() >= 5;
        })->first();

        if ($matchesTable->count() === 0) {
            \Log::warning("Clipper: No matches list table found using primary heuristic (>= 5 match links).");
            return [];
        }

        $fragmentHtml = $matchesTable->outerHtml();
        $prev = $matchesTable->previousAll()->first();
        if ($prev->count() > 0 && in_array($prev->nodeName(), ['h1', 'h2', 'h3'])) {
            $fragmentHtml = $prev->outerHtml() . $fragmentHtml;
        }

        $links = [];
        $matchesTable->filter('a[href*="/zapas/"]')->each(function (Crawler $a) use (&$links) {
            $href = $a->attr('href');
            if (str_starts_with($href, '/')) {
                $href = 'https://cz.basketball' . $href;
            }
            $links[] = [
                'id' => preg_match('/\/zapas\/(\d+)/', $href, $m) ? $m[1] : null,
                'url' => $href,
                'name' => trim($a->text()),
            ];
        });

        // Chunking pokud je tabulka moc velká (nad 60 řádků by mohlo být moc pro AI v jednom promptu)
        // Musíme ale správně filtrovat řádky v těle tabulky
        $rows = $matchesTable->filter('tbody tr');
        if ($rows->count() === 0) {
            $rows = $matchesTable->filter('tr'); // Pokud není tbody
        }

        if ($rows->count() > 60) {
            return $this->chunkTable($matchesTable, $links);
        }

        return [
            new ClipDTO(
                id: 'matches_list_table',
                htmlFragment: $fragmentHtml,
                textHint: 'Main matches list table',
                links: $links,
                evidence: [
                    'row_count' => $rows->count(),
                    'match_link_count' => count($links),
                ]
            )
        ];
    }

    protected function chunkTable(Crawler $table, array $allLinks): array
    {
        $clips = [];
        $header = $table->filter('thead')->count() > 0 ? $table->filter('thead')->outerHtml() : '';

        $rows = $table->filter('tbody tr');
        if ($rows->count() === 0) {
            $rows = $table->filter('tr');
        }

        $chunkSize = 25;
        $totalRows = $rows->count();

        for ($i = 0; $i < $totalRows; $i += $chunkSize) {
            $chunkRowsHtml = '';
            for ($j = $i; $j < $i + $chunkSize && $j < $totalRows; $j++) {
                $chunkRowsHtml .= $rows->eq($j)->outerHtml();
            }

            $chunkHtml = "<table>{$header}<tbody>{$chunkRowsHtml}</tbody></table>";
            $chunkIndex = ($i / $chunkSize) + 1;

            $clips[] = new ClipDTO(
                id: "matches_list_table_chunk_{$chunkIndex}",
                htmlFragment: $chunkHtml,
                textHint: "Matches list table chunk {$chunkIndex}",
                links: array_slice($allLinks, $i, $chunkSize),
                evidence: [
                    'chunk_index' => $chunkIndex,
                    'rows_in_chunk' => min($chunkSize, $totalRows - $i),
                    'total_rows' => $totalRows
                ]
            );
        }

        return $clips;
    }
}
