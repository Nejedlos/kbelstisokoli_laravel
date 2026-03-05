<?php

namespace App\Services\Stats\Extractors\CzBasketball\DOM;

use Symfony\Component\DomCrawler\Crawler;

class CzBasketballMatchesListDomExtractor
{
    public function extract(string $html): array
    {
        $crawler = new Crawler($html);

        // IDENTIFIKACE HLAVNÍ TABULKY: Najdi table, která obsahuje nejvíce /zapas/ odkazů.
        $tables = $crawler->filter('table, .overflow-auto, .table-responsive');
        $targetTable = null;
        $maxMatchLinks = 0;

        foreach ($tables as $node) {
            $t = new Crawler($node);
            $count = $t->filter('a[href*="/zapas/"]')->count();
            if ($count > $maxMatchLinks) {
                $maxMatchLinks = $count;
                $targetTable = $t;
            }
        }

        // Pokud jsme nenašli nic jako tabulku, ale jsou tam odkazy, zkusíme najít divy s odkazy
        if ($maxMatchLinks < 1) {
             $possibleRows = $crawler->filter('a[href*="/zapas/"]');
             if ($possibleRows->count() >= 1) {
                 // Máme seznam zápasů v jiné struktuře než table
                 return $this->extractFromList($crawler);
             }
        }

        // Threshold: >= 1 match link (podle fixture)
        if ($maxMatchLinks < 1) {
            return [];
        }

        $headers = $this->getTableHeaders($targetTable);
        $rows = [];

        $targetTable->filter('tbody tr')->each(function (Crawler $tr) use (&$rows, $headers) {
            $rowData = [];
            $cells = $tr->filter('td');
            if ($cells->count() === 0) return;

            $matchLink = $tr->filter('a[href*="/zapas/"]')->first();
            if ($matchLink->count() > 0) {
                $href = $matchLink->attr('href');
                if (preg_match('/\/zapas\/(\d+)/', $href, $matches)) {
                    $rowData['match_external_id'] = $matches[1];
                }
            }

            foreach ($headers as $index => $label) {
                $cell = $cells->eq($index);
                if ($cell->count() === 0) continue;
                $val = trim($cell->text());

                // Mapování polí (heuristika podle názvů sloupců)
                if (str_contains($label, 'Datum')) $rowData['date_time'] = $val;
                if (str_contains($label, 'Domácí')) $rowData['home_team'] = $val;
                if (str_contains($label, 'Hosté')) $rowData['away_team'] = $val;
                if (str_contains($label, 'Skóre')) {
                    $rowData['score'] = $val;
                    $rowData['status'] = (str_contains($val, ':') && preg_match('/\d+/', $val)) ? 'completed' : 'planned';
                }
                if (str_contains($label, 'Soutěž')) $rowData['competition'] = $val;
                if (str_contains($label, 'Kolo')) $rowData['round'] = $val;
            }

            if (isset($rowData['match_external_id'])) {
                $rows[] = $rowData;
            }
        });

        return $rows;
    }

    protected function extractFromList(Crawler $crawler): array
    {
        $rows = [];
        $crawler->filter('a[href*="/zapas/"]')->each(function (Crawler $link) use (&$rows) {
            $rowData = [];
            $href = $link->attr('href');
            if (preg_match('/\/zapas\/(\d+)/', $href, $matches)) {
                $rowData['match_external_id'] = $matches[1];
            } else {
                return;
            }

            // Hledáme texty kolem odkazu
            $text = $link->text();
            $parent = $link->closest('div, li, tr');

            if ($parent && $parent->count() > 0) {
                $rowData['full_text'] = trim($parent->text());
                // Pokusíme se vyparsovat skóre a týmy z textu parentu
                // Např. "Sokol Kbely E  Fenix Modřany B  2025"
                $lines = explode("\n", str_replace("\r", "", $rowData['full_text']));
                $cleanLines = array_values(array_filter(array_map('trim', $lines)));

                if (count($cleanLines) >= 2) {
                    $rowData['home_team'] = $cleanLines[0];
                    $rowData['away_team'] = $cleanLines[1];
                }
            }

            $rows[] = $rowData;
        });

        return $rows;
    }

    protected function getTableHeaders(Crawler $table): array
    {
        $headers = [];
        $headerRow = $table->filter('tr')->first();
        if ($headerRow->count() > 0) {
            $headerRow->filter('th, td')->each(function (Crawler $node) use (&$headers) {
                $headers[] = trim($node->text());
            });
        }
        return $headers;
    }
}
