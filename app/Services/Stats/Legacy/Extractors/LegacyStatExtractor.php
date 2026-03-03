<?php

namespace App\Services\Stats\Legacy\Extractors;

use App\Services\Stats\DTO\NormalizedRowDTO;
use App\Services\Stats\DTO\NormalizedTableDTO;
use Symfony\Component\DomCrawler\Crawler;
use Illuminate\Support\Str;

class LegacyStatExtractor
{
    public function extract(string $content, string $fileType, string $encoding = 'UTF-8'): array
    {
        if ($encoding === 'Windows-1250') {
            $content = iconv('Windows-1250', 'UTF-8//IGNORE', $content);
        }

        $crawler = new Crawler($content);
        $tables = $crawler->filter('table');

        $extractedTables = [];
        $tableIndex = 0;

        $tables->each(function (Crawler $table) use (&$extractedTables, &$tableIndex, $fileType) {
            $tableIndex++;

            // Filtrování tabulek, které nejsou data
            if ($this->shouldIgnoreTable($table, $fileType, $tableIndex)) {
                return;
            }

            $headers = $this->extractHeaders($table);

            // Pokud tabulka nemá hlavičky, zkusíme ji přeskočit nebo brát první řádek jako hlavičku
            if (empty($headers)) {
                return;
            }

            $rows = $this->extractRows($table, $headers, $fileType);

            if (count($rows) > 0) {
                $type = $this->refineTableType($headers, $fileType);
                $extractedTables[] = new NormalizedTableDTO($type, $headers, $rows);
            }
        });

        if (empty($extractedTables)) {
            return [new NormalizedTableDTO('Unknown', [], [], ['No valid data tables found in HTML'])];
        }

        return $extractedTables;
    }

    protected function shouldIgnoreTable(Crawler $table, string $fileType, int $index): bool
    {
        $text = Str::lower($table->text());

        // Navigační tabulky v ČBF souborech
        if (Str::contains($text, ['detaily', 'seznam hráčů', 'statistiky', 'vzájemné zápasy'])) {
            return true;
        }

        // Rozpis kol v konečné tabulce
        if ($fileType === 'league_table' && $index > 1) {
            if (Str::contains($text, ['kolo', 'domácí', 'hosté'])) {
                return true;
            }
        }

        // Tabulky s partnery
        if ($table->attr('class') === 'partners') {
            return true;
        }

        return false;
    }

    protected function extractHeaders(Crawler $table): array
    {
        $headers = [];
        $headerRow = $table->filter('tr')->first();

            // ČBF soubory často nemají <thead>, ale první <tr> obsahuje popisky
        $headerRow->filter('th, td')->each(function (Crawler $cell) use (&$headers) {
            $label = trim($cell->text());
            if ($label === '') {
                $headers[] = ['key' => 'col_' . count($headers), 'label' => ''];
                return;
            }

            $headers[] = ['key' => $this->normalizeHeader($label, count($headers)), 'label' => $label];
        });

        // Kontrola, zda první řádek je skutečně hlavička (např. obsahuje "Jméno", "Body", "TH")
        $headerLabels = collect($headers)->pluck('label')->implode(' ');
        if (!preg_match('/jméno|hráč|tým|body|pts|th|2b|3b|skóre|z/i', $headerLabels)) {
             // Pokud to nevypadá jako hlavička, možná je to jen prázdná tabulka nebo divný formát
             return [];
        }

        return $headers;
    }

    protected function normalizeHeader(string $label, int $index): string
    {
        $label = Str::lower($label);
        $label = preg_replace('/\s+/', '', $label);

        // Speciální případ pro TH, který se v ČBF tabulce střelby vyskytuje 2x
        if ($label === 'th') {
            return $index <= 5 ? 'ft_att_made' : 'ft_percent';
        }

        if ($label === 'b.' || $label === 'b') {
            return $index > 5 ? 'pts_pg' : 'pts';
        }

        if ($label === 'f-') {
            return $index > 3 ? 'fouls_pg' : 'fouls';
        }

        if ($label === 'val') {
            return $index > 4 ? 'efficiency_pg' : 'efficiency';
        }

        return match ($label) {
            'hráč', 'jméno', 'name' => 'player_name',
            'ročník', 'nar.', 'rok' => 'birth_year',
            'zápasy', 'z', 'gp' => 'gp',
            'body', 'b', 'pts' => 'pts',
            '2b', '2b.', '2b_m', '2p' => 'fg2_made',
            '3b', '3b.', '3b_m', '3p' => 'fg3_made',
            'celkem' => 'fg_made',
            'th%', 'ft%' => 'ft_percent',
            'fauly', 'f', 'fouls', 'f-' => 'fouls',
            'číslo', 'č.', '#' => 'jersey_number',
            'tým' => 'team_name',
            'skóre' => 'score',
            'val' => 'efficiency',
            default => Str::snake($label),
        };
    }

    protected function refineTableType(array $headers, string $baseType): string
    {
        $keys = collect($headers)->pluck('key')->toArray();

        if (in_array('ft_att_made', $keys)) {
            return 'players_shooting';
        }

        if (in_array('efficiency', $keys) && in_array('player_name', $keys)) {
            return 'players_summary';
        }

        if (in_array('efficiency', $keys) && !in_array('player_name', $keys)) {
            return 'team_matches_fouls';
        }

        return $baseType;
    }

    protected function extractRows(Crawler $table, array $headers, string $fileType): array
    {
        $rows = [];

        // Přeskočíme první řádek, protože ten jsme použili jako hlavičku
        $table->filter('tr')->slice(1)->each(function (Crawler $tr) use (&$rows, $headers) {
            $values = [];
            $playerName = null;
            $rowLabel = null;

            $tr->filter('td')->each(function (Crawler $td, $index) use (&$values, &$playerName, &$rowLabel, $headers) {
                if (!isset($headers[$index])) return;

                $key = $headers[$index]['key'];
                $value = trim($td->text());

                // Speciální parsování pro "ATT/MADE" (např. 47/37)
                if ($key === 'ft_att_made' && Str::contains($value, '/')) {
                    [$att, $made] = explode('/', $value);
                    $values['ft_att'] = (int) $att;
                    $values['ft_made'] = (int) $made;
                }

                if ($key === 'player_name') {
                    $playerName = $value;
                }

                $values[$key] = $value;
            });

            // ČBF tabulky mají souhrnné řádky na konci
            if ($playerName === 'Celkem' || $playerName === 'Tým/trenéři') {
                $rowLabel = $playerName;
                $playerName = null;
            }

            if (!empty($values)) {
                $rows[] = new NormalizedRowDTO($values, null, null, $rowLabel);
            }
        });

        return $rows;
    }
}
