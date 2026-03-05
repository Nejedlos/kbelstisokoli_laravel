<?php

namespace App\Services\Stats\Extractors\CzBasketball;

use App\Services\Stats\Contracts\StatExtractorInterface;
use App\Services\Stats\DTO\NormalizedRowDTO;
use App\Services\Stats\DTO\NormalizedTableDTO;
use Symfony\Component\DomCrawler\Crawler;

class MatchDetailBoxscoreExtractor implements StatExtractorInterface
{
    /**
     * Mapování českých zkratek na kanonické klíče.
     */
    protected array $columnMapping = [
        'B' => 'pts',
        'BODY' => 'pts',
        '2B' => 'fg2_made',
        '3B' => 'fg3_made',
        'TH' => 'ft_made',
        'F+' => 'fouls_drawn',
        'F-' => 'fouls',
        'CH' => 'fouls',
        'MIN' => 'minutes',
        '+/-' => 'plus_minus',
        'DOS' => 'rebounds',
        'AS' => 'assists',
        'ZIS' => 'steals',
        'ZTR' => 'turnovers',
        'BL' => 'blocks',
        'VAL' => 'efficiency',
    ];

    /**
     * Extrahuje boxscore ze stránky detailu zápasu.
     */
    public function extract(string $content, array $config = []): array
    {
        $crawler = new Crawler($content);
        $warnings = [];

        // 1. Hlavička zápasu
        $matchHeader = $this->extractHeader($crawler);

        // 2. Tabulky statistik
        // Na cz.basketball bývají pod sebou dvě tabulky .table-condensed
        $tables = $crawler->filter('table.table-condensed');

        $allTablesData = [];
        $allFragmentHtml = '';

        $tables->each(function (Crawler $table, $i) use (&$allTablesData, &$allFragmentHtml, &$warnings) {
            // Kontrola, zda je tabulka validní boxscore (musí mít aspoň 5 sloupců)
            if ($table->filter('thead th')->count() < 5) {
                return;
            }

            $tableName = $i === 0 ? ($matchHeader['home_team'] ?? 'Home Team Boxscore') : ($matchHeader['away_team'] ?? 'Away Team Boxscore');

            // Zkusíme najít název týmu nad tabulkou (např. v h3 nebo h4)
            // Nejprve zkusíme přímo nad tabulkou, pak nad jejím rodičem
            $teamNameNode = $table->previousAll()->filter('h3, h4, .title')->last();
            if ($teamNameNode->count() === 0) {
                $container = $table->closest('div');
                if ($container->count() > 0) {
                    $teamNameNode = $container->previousAll()->filter('h3, h4, .title')->last();
                }
            }

            if ($teamNameNode && $teamNameNode->count() > 0) {
                $tableName = trim($teamNameNode->text());
            }

            $allFragmentHtml .= '<h3>'.$tableName."</h3>\n";
            $allFragmentHtml .= $table->outerHtml()."\n";
            $tableDto = $this->processBoxscoreTable($table, $tableName, $warnings);
            $allTablesData[] = $tableDto;
        });

        // Pro zjednodušení vracíme první tabulku jako hlavní data, ale v metadatech máme vše
        $mainTable = $allTablesData[0] ?? new NormalizedTableDTO('Boxscore', [], [], ['warnings' => ['No tables found']]);

        $mainTable->metadata = array_merge($mainTable->metadata, [
            'header' => $matchHeader,
            'all_tables' => array_map(fn ($t) => $t->toArray(), $allTablesData),
            'warnings' => $warnings,
        ]);

        return [
            'tables' => $allTablesData,
            'data' => $mainTable,
            'fragment_html' => $allFragmentHtml,
        ];
    }

    protected function extractHeader(Crawler $crawler): array
    {
        $header = [];

        // Týmy (alfa / beta nebo team-name)
        $homeNode = $crawler->filter('.alfa')->first();
        if ($homeNode->count() > 0) {
            $header['home_team'] = trim($homeNode->text());
        }

        $awayNode = $crawler->filter('.beta')->first();
        if ($awayNode->count() > 0) {
            $header['away_team'] = trim($awayNode->text());
        }

        if (! isset($header['home_team'])) {
            $teams = $crawler->filter('.team-name, .match-teams h1, .match-teams h2');
            if ($teams->count() >= 2) {
                $header['home_team'] = trim($teams->eq(0)->text());
                $header['away_team'] = trim($teams->eq(1)->text());
            }
        }

        // Skóre
        $scoreNodes = $crawler->filter('.delta, .match-score, .score, .final-score');
        if ($scoreNodes->count() >= 2) {
            // Skóre rozdělené do dvou částí
            $header['score'] = trim($scoreNodes->eq(0)->text()).':'.trim($scoreNodes->eq(1)->text());
        } elseif ($scoreNodes->count() > 0) {
            $header['score'] = trim($scoreNodes->first()->text());
        }

        $dateNode = $crawler->filter('.match-date, .date-time')->first();
        if ($dateNode->count() > 0) {
            $header['date'] = trim($dateNode->text());
        }

        return $header;
    }

    protected function processBoxscoreTable(Crawler $table, string $tableName, array &$warnings): NormalizedTableDTO
    {
        // Pokud má tabulka v hlavičce th s colspan, obsahuje název týmu
        $headerTh = $table->filter('thead th[colspan]');
        if ($headerTh->count() > 0) {
            $tableName = trim($headerTh->first()->text());
        } elseif ($table->filter('thead th')->count() > 0 && str_contains($table->filter('thead th')->first()->attr('class') ?? '', 'title')) {
            $tableName = trim($table->filter('thead th')->first()->text());
        } else {
            // Zkusíme najít h4 nad tabulkou (v některých verzích HTML)
            try {
                $container = $table->closest('div.overflow-auto');
                if ($container && $container->count() > 0) {
                    $h4 = $container->previousAll()->filter('h4')->first();
                    if ($h4 && $h4->count() > 0) {
                        $tableName = trim($h4->text());
                    }
                }
            } catch (\Exception $e) {
                // Ignore DOM traversal errors
            }
        }

        $columns = [];
        $rows = [];

        // Hlavička tabulky pro mapování sloupců
        $table->filter('thead tr')->last()->filter('th')->each(function (Crawler $th, $i) use (&$columns, $table) {
            $label = trim($th->text());
            if ($th->attr('colspan') > 1 && $table->filter('thead tr')->count() > 1) {
                return; // Přeskočíme hlavičku s colspan (název týmu)
            }
            $normalizedLabel = mb_strtoupper(str_replace(' ', '', $label));

            $key = $this->columnMapping[$normalizedLabel] ?? 'col_'.$i;
            $columns[$key] = $label;
        });

        // Řádky hráčů
        $table->filter('tbody tr')->each(function (Crawler $tr) use (&$rows, $columns, &$warnings) {
            $cells = $tr->filter('td');
            if ($cells->count() < 2) {
                return;
            }

            $values = [];
            $playerId = null;
            $playerName = null;

            // Najdeme odkaz na hráče
            $playerLink = $tr->filter('a[href*="/hrac/"]')->first();
            if ($playerLink->count() > 0) {
                $playerName = trim($playerLink->text());
                if (preg_match('/\/hrac\/(\d+)/', $playerLink->attr('href'), $matches)) {
                    $playerId = $matches[1];
                }
            }

            // Mapujeme hodnoty buněk na klíče z hlavičky
            $i = 0;
            foreach ($columns as $key => $label) {
                if ($cells->count() > $i) {
                    $val = trim($cells->eq($i)->text());

                    // Pokud jsme jméno nenašli přes odkaz, zkusíme první buňky
                    if (! $playerName && ($key === 'col_0' || $key === 'col_1')) {
                        if (preg_match('/[a-zA-Z]/', $val)) {
                            $playerName = $val;
                        }
                    }

                    // Pokud hodnota obsahuje lomítko (např. 4/6), zkusíme ji rozdělit na made/att
                    if (str_contains($val, '/') && preg_match('/(\d+)\/(\d+)/', $val, $ratioMatches)) {
                        $made = (int) $ratioMatches[1];
                        $att = (int) $ratioMatches[2];

                        if ($key === 'fg2_made') {
                            $values['fg2_made'] = $made;
                            $values['fg2_att'] = $att;
                        } elseif ($key === 'fg3_made') {
                            $values['fg3_made'] = $made;
                            $values['fg3_att'] = $att;
                        } elseif ($key === 'ft_made') {
                            $values['ft_made'] = $made;
                            $values['ft_att'] = $att;
                        } else {
                            $values[$key] = $val;
                        }
                    } else {
                        // Převod na číslo, pokud to jde
                        if (is_numeric(str_replace(',', '.', $val))) {
                            $values[$key] = (float) str_replace(',', '.', $val);
                        } else {
                            $values[$key] = $val;
                        }
                    }
                }
                $i++;
            }

            if ($playerName) {
                $rows[] = new NormalizedRowDTO(
                    values: $values,
                    rowLabel: $playerName,
                    metadata: [
                        'external_player_id' => $playerId,
                    ]
                );
            }
        });

        return new NormalizedTableDTO($tableName, $columns, $rows);
    }
}
