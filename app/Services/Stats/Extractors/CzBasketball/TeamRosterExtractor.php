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

            if ($playerLink->count() > 0) {
                $href = $playerLink->attr('href');
                $playerName = trim($playerLink->text());

                // Extrakt ID z /hrac/12345 nebo https://cz.basketball/hrac/12345
                if (preg_match('/\/hrac\/(\d+)/', $href, $matches)) {
                    $playerId = $matches[1];
                }
            } else {
                // Pokud není odkaz, zkusíme první buňku s textem jako jméno
                $playerName = trim($cells->first()->text());
            }

            // Rok narození - obvykle v jedné z buněk, zkusíme najít 4-místné číslo
            $birthYear = null;
            $cells->each(function (Crawler $td) use (&$birthYear) {
                $text = trim($td->text());
                if (preg_match('/^(19|20)\d{2}$/', $text)) {
                    $birthYear = $text;
                }
            });

            if ($playerId) {
                $rows[] = new NormalizedRowDTO(
                    values: [
                        'player_name' => $playerName,
                        'birth_year' => $birthYear,
                    ],
                    playerId: (int) $playerId,
                    metadata: [
                        'external_player_id' => $playerId,
                    ]
                );
            } else {
                $warnings[] = 'Player ID not found for row: '.$playerName;
                $rows[] = new NormalizedRowDTO(
                    values: [
                        'player_name' => $playerName,
                        'birth_year' => $birthYear,
                    ],
                    rowLabel: $playerName,
                    metadata: [
                        'warning' => 'Missing external ID',
                    ]
                );
            }
        });

        $dto = new NormalizedTableDTO(
            name: 'Soupiska',
            columns: [
                'player_name' => 'Jméno hráče',
                'birth_year' => 'Rok narození',
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
