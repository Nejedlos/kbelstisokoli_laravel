<?php

namespace App\Services\Stats\Extractors\CzBasketball;

use App\Services\Stats\Contracts\StatExtractorInterface;
use App\Services\Stats\DTO\NormalizedRowDTO;
use App\Services\Stats\DTO\NormalizedTableDTO;
use Symfony\Component\DomCrawler\Crawler;

class TeamRosterExtractor implements StatExtractorInterface
{
    /**
     * Extrahuje soupisku z HTML stránky týmu.
     */
    public function extract(string $content, array $config = []): array
    {
        $crawler = new Crawler($content);
        $warnings = [];

        // Extrahujeme název týmu z nadpisu h1
        $teamName = trim($crawler->filter('h1')->first()->text() ?? '');
        // Pokud h1 obsahuje i sezónu (např. "Tým XYZ - 2024/25"), ořízneme to
        $teamName = explode('-', $teamName)[0];
        $teamName = trim($teamName);

        // Hledáme tabulku soupisky - obvykle table.js-table-fixed-order v sekci "Soupiska"
        // Zkusíme najít tabulku, která obsahuje odkaz na /hrac/
        $table = $crawler->filter('table.js-table-fixed-order')->first();

        if ($table->count() === 0) {
            // Fallback na jakoukoli tabulku obsahující /hrac/
            $table = $crawler->filter('table')->reduce(function (Crawler $node) {
                return str_contains($node->html(), '/hrac/');
            })->first();
        }

        if ($table->count() === 0) {
            return [
                'data' => new NormalizedTableDTO('Soupiska', [], [], ['warnings' => ['Table not found']]),
                'fragment_html' => '',
            ];
        }

        $fragmentHtml = $table->outerHtml();
        $rows = [];

        $table->filter('tbody tr')->each(function (Crawler $tr) use (&$rows, &$warnings) {
            $cells = $tr->filter('td');
            if ($cells->count() < 2) {
                return;
            }

            // Najdeme odkaz na hráče pro získání ID
            $playerLink = $tr->filter('a[href*="/hrac/"]')->first();
            $playerId = null;
            $playerName = null;
            $jerseyNumber = null;
            $position = null;
            $height = null;
            $weight = null;
            $birthYear = null;
            $nationality = null;

            // Číslo dresu (obvykle první buňka, pokud je to číslo)
            $firstCellText = trim($cells->first()->text());
            if (is_numeric($firstCellText)) {
                $jerseyNumber = (int) $firstCellText;
            }

            if ($playerLink->count() > 0) {
                $href = $playerLink->attr('href');
                $playerName = trim($playerLink->text());

                // Extrakt ID z /hrac/12345 nebo https://cz.basketball/hrac/12345
                if (preg_match('/\/hrac\/(\d+)/', $href, $matches)) {
                    $playerId = $matches[1];
                }
            } else {
                // Pokud není odkaz, zkusíme druhou buňku jako jméno, pokud první je číslo
                if ($jerseyNumber !== null && $cells->count() > 1) {
                    $playerName = trim($cells->eq(1)->text());
                } else {
                    $playerName = trim($cells->first()->text());
                }
            }

            // Procházíme ostatní buňky a hledáme specifické údaje
            $cells->each(function (Crawler $td, $i) use (&$birthYear, &$height, &$weight, &$position, &$nationality, $jerseyNumber) {
                $text = trim($td->text());
                if (empty($text)) return;

                // Rok narození (4-místné číslo 19xx nebo 20xx)
                if (preg_match('/^(19|20)\d{2}$/', $text)) {
                    $birthYear = (int) $text;
                }
                // Výška (3-místné číslo, obvykle 150-230)
                elseif (preg_match('/^\d{3}$/', $text) && (int)$text > 140 && (int)$text < 230 && $i > 1) {
                    $height = (int) $text;
                }
                // Pozice (1, 2, 3, 4, 5 nebo kód jako G, F, C, PG, SG, SF, PF)
                elseif (preg_match('/^[1-5]$/', $text) || in_array(mb_strtoupper($text), ['G', 'F', 'C', 'PG', 'SG', 'SF', 'PF'])) {
                    $position = $text;
                }
                // Národnost (často vlajka nebo zkratka státu CZE, SVK, atd.)
                elseif (preg_match('/^[A-Z]{3}$/', $text) && !in_array($text, ['MIN', 'VAL', 'PTS'])) {
                    $nationality = $text;
                }
            });

            // Speciální detekce z CSS tříd nebo obrázků (vlajky)
            $flagImg = $tr->filter('img[src*="/flags/"], img[alt*="Flag"]')->first();
            if ($flagImg->count() > 0 && !$nationality) {
                $nationality = $flagImg->attr('alt') ?: preg_replace('/.*\/([a-z]{2,3})\..*/i', '$1', $flagImg->attr('src'));
            }

            if ($playerName) {
                $rows[] = new NormalizedRowDTO(
                    values: array_filter([
                        'player_name' => $playerName,
                        'jersey_number' => $jerseyNumber,
                        'position' => $position,
                        'height' => $height,
                        'weight' => $weight,
                        'birth_year' => $birthYear,
                        'nationality' => $nationality,
                    ]),
                    playerId: $playerId ? (int) $playerId : null,
                    metadata: array_filter([
                        'external_player_id' => $playerId,
                        'warning' => $playerId ? null : 'Missing external ID',
                    ])
                );
            }
        });

        $dto = new NormalizedTableDTO(
            name: 'Soupiska',
            columns: [
                'player_name' => 'Jméno hráče',
                'jersey_number' => 'Č.',
                'position' => 'Pozice',
                'height' => 'Výška',
                'birth_year' => 'Rok narození',
                'nationality' => 'Národnost',
            ],
            rows: $rows,
            metadata: [
                'warnings' => $warnings,
                'source' => 'czbasketball',
                'team_name' => $teamName,
            ]
        );

        return [
            'data' => $dto,
            'fragment_html' => $fragmentHtml,
        ];
    }
}
