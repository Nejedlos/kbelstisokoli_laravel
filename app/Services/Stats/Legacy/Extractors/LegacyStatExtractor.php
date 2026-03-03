<?php

namespace App\Services\Stats\Legacy\Extractors;

use App\Services\Stats\DTO\NormalizedRowDTO;
use App\Services\Stats\DTO\NormalizedTableDTO;
use Symfony\Component\DomCrawler\Crawler;
use Illuminate\Support\Str;

class LegacyStatExtractor
{
    public function extract(string $content, string $fileType): NormalizedTableDTO
    {
        $crawler = new Crawler($content);

        // Hledání první tabulky, která vypadá jako datová
        $table = $crawler->filter('table')->first();

        if ($table->count() === 0) {
            return new NormalizedTableDTO('Unknown', [], [], ['No table found in HTML']);
        }

        $headers = $this->extractHeaders($table);
        $rows = $this->extractRows($table, $headers, $fileType);

        return new NormalizedTableDTO($fileType, $headers, $rows);
    }

    protected function extractHeaders(Crawler $table): array
    {
        $headers = [];
        $headerRow = $table->filter('tr')->first();

        $headerRow->filter('th, td')->each(function (Crawler $cell) use (&$headers) {
            $label = trim($cell->text());
            $key = $this->normalizeHeader($label);
            $headers[] = ['key' => $key, 'label' => $label];
        });

        return $headers;
    }

    protected function normalizeHeader(string $label): string
    {
        $label = Str::lower($label);
        $label = preg_replace('/\s+/', '', $label);

        return match ($label) {
            'hráč', 'jméno', 'name' => 'player_name',
            'ročník', 'nar.', 'rok' => 'birth_year',
            'zápasy', 'z', 'gp' => 'gp',
            'body', 'b', 'pts' => 'pts',
            '2b', '2b_m', '2p' => 'fg2_made',
            '3b', '3b_m', '3p' => 'fg3_made',
            'th', 'ft' => 'ft_made',
            'th%', 'ft%' => 'ft_percent',
            'fauly', 'f', 'fouls' => 'fouls',
            'číslo', 'č.', '#' => 'jersey_number',
            default => Str::snake($label),
        };
    }

    protected function extractRows(Crawler $table, array $headers, string $fileType): array
    {
        $rows = [];

        $table->filter('tr')->slice(1)->each(function (Crawler $tr) use (&$rows, $headers) {
            $values = [];
            $playerName = null;

            $tr->filter('td')->each(function (Crawler $td, $index) use (&$values, &$playerName, $headers) {
                if (!isset($headers[$index])) return;

                $key = $headers[$index]['key'];
                $value = trim($td->text());

                if ($key === 'player_name') {
                    $playerName = $value;
                }

                $values[$key] = $value;
            });

            if (!empty($values)) {
                $rows[] = new NormalizedRowDTO($values, null, null, $playerName);
            }
        });

        return $rows;
    }
}
