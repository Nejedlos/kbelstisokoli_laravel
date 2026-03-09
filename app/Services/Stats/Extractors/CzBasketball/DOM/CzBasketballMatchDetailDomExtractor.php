<?php

namespace App\Services\Stats\Extractors\CzBasketball\DOM;

use Symfony\Component\DomCrawler\Crawler;

class CzBasketballMatchDetailDomExtractor
{
    public function extract(string $html): array
    {
        $crawler = new Crawler($html);
        $warnings = [];

        // 3.1 HEADER
        $header = $this->extractHeader($crawler);

        // 3.2 BOXscore sekce
        $teamBlocks = $this->extractBoxscore($crawler);

        // 3.3 PREVIEW (ROZVAHA)
        $preview = $this->extractPreview($crawler);

        // 6. VALIDACE
        if (empty($teamBlocks) && empty($preview)) {
            $warnings[] = "Neither boxscore nor preview section found.";
        }

        return [
            'header' => $header,
            'team_blocks' => $teamBlocks,
            'preview' => $preview,
            'warnings' => $warnings,
        ];
    }

    protected function extractPreview(Crawler $crawler): array
    {
        $preview = [];

        // Tabulka s rozvahou obsahuje porovnání
        // 3 sloupce: home_value, label, away_value
        $table = $crawler->filter('table')->reduce(function (Crawler $node) {
            $text = $node->text();
            return str_contains($text, 'Body na zápas') || str_contains($text, 'Doskoky');
        })->first();

        if ($table->count() > 0) {
            $table->filter('tr')->each(function (Crawler $tr) use (&$preview) {
                $tds = $tr->filter('td');
                if ($tds->count() === 3) {
                    $label = trim($tds->eq(1)->text());
                    $preview[$label] = [
                        'home' => trim($tds->eq(0)->text()),
                        'away' => trim($tds->eq(2)->text()),
                    ];
                }
            });
        }

        return $preview;
    }

    protected function extractHeader(Crawler $crawler): array
    {
        $info = [
            'date_time' => '',
            'competition' => '',
            'venue' => '',
            'home_team' => '',
            'away_team' => '',
            'score_home' => '',
            'score_away' => '',
        ];

        // Skóre bývá v fi-match-header__score nebo v h1 s delta
        $score = $crawler->filter('.fi-match-header__score, .delta.mb-0');
        if ($score->count() >= 2) {
            $info['score_home'] = trim($score->eq(0)->text());
            $info['score_away'] = trim($score->eq(1)->text());
        } elseif ($score->count() === 1 && str_contains($score->text(), ':')) {
            $parts = explode(':', trim($score->text()));
            $info['score_home'] = trim($parts[0] ?? '');
            $info['score_away'] = trim($parts[1] ?? '');
        }

        // Týmy v h1 a tagu
        $teams = $crawler->filter('.fi-match-header__team-name, h1 a[href*="/tym/"]');
        if ($teams->count() >= 2) {
            $info['home_team'] = trim($teams->eq(0)->text());
            $info['away_team'] = trim($teams->eq(1)->text());
        }

        // Detaily (datum, hala, soutěž)
        $details = $crawler->filter('.fi-match-header__details-item, .article-title');
        $details->each(function (Crawler $node) use (&$info) {
            $text = trim($node->text());
            if (preg_match('/\d+\.\s*\d+\.\s*\d{4}/', $text)) {
                $info['date_time'] = $text . ($info['date_time'] ? ' ' . $info['date_time'] : '');
            } elseif (preg_match('/\d{2}:\d{2}/', $text)) {
                $info['date_time'] .= ($info['date_time'] ? ' ' : '') . $text;
            } elseif (str_contains($text, 'hala') || $node->filter('a[href*="/hala/"]')->count() > 0) {
                $info['venue'] = $text;
            } else {
                // Možná soutěž
                if (!empty($text) && !is_numeric($text)) {
                    $info['competition'] = $text;
                }
            }
        });

        return $info;
    }

    protected function extractBoxscore(Crawler $crawler): array
    {
        $teamBlocks = [];

        // Najdi element s "Boxscore"
        $boxscoreHeading = $crawler->filterXPath("//*[contains(normalize-space(.), 'Boxscore')][self::h3 or self::h2 or self::div]");

        // V reálu to může být i prostě heading
        if ($boxscoreHeading->count() === 0) {
            // Zkusíme najít h3
            $boxscoreHeading = $crawler->filter('h3')->reduce(function(Crawler $node) {
                return str_contains($node->text(), 'Boxscore');
            });
        }

        // Hledáme bloky týmů
        // Prompt říká: #### {TeamName} (následuje tabulka)
        $teamHeadings = $crawler->filter('h4');
        $teamHeadings->each(function (Crawler $h4) use (&$teamBlocks) {
            $teamName = trim($h4->text());

            // Najdi nejbližší následující tabulku
            $table = $h4->filterXPath("following-sibling::table[1]");
            if ($table->count() === 0) {
                // Možná je to zabaleno v divu
                $table = $h4->closest('div')->filter('table')->first();
                if ($table->count() === 0 || !str_contains($table->filter('tr')->first()->text(), 'Hráč')) {
                    return;
                }
            }

            $rows = $this->extractBoxscoreTable($table);

            if (!empty($rows)) {
                $teamBlocks[] = [
                    'team_label' => $teamName,
                    'rows' => $rows
                ];
            }
        });

        return $teamBlocks;
    }

    protected function extractBoxscoreTable(Crawler $table): array
    {
        $headers = [];
        $headerRow = $table->filter('tr')->first();
        if ($headerRow->count() > 0) {
            $headerRow->filter('th, td')->each(function (Crawler $node) use (&$headers) {
                $headers[] = trim($node->text());
            });
        }

        $rows = [];
        $table->filter('tbody tr')->each(function (Crawler $tr) use (&$rows, $headers) {
            $cells = $tr->filter('td');
            if ($cells->count() === 0) return;

            $playerCell = null;
            $playerCellIndex = -1;
            foreach ($headers as $idx => $label) {
                if (str_contains($label, 'Hráč')) {
                    $playerCellIndex = $idx;
                    break;
                }
            }

            if ($playerCellIndex === -1) return;
            $playerName = trim($cells->eq($playerCellIndex)->text());

            // FILTRY: ignoruj řádky, kde Hráč obsahuje “Tým/trenéři” nebo “Celkem”
            if (str_contains($playerName, 'Tým/trenéři') || str_contains($playerName, 'Celkem')) {
                return;
            }

            $rowData = [
                'player_name' => $playerName,
                'player_external_id' => null,
                'jersey_number' => null,
                'values' => []
            ];

            $playerLink = $cells->eq($playerCellIndex)->filter('a[href*="/hrac/"]')->first();
            if ($playerLink->count() > 0) {
                if (preg_match('/\/hrac\/(\d+)/', $playerLink->attr('href'), $m)) {
                    $rowData['player_external_id'] = $m[1];
                }
            }

            foreach ($headers as $index => $label) {
                $cell = $cells->eq($index);
                if ($cell->count() === 0) continue;
                $val = trim($cell->text());

                if (str_contains($label, 'Číslo')) $rowData['jersey_number'] = $val;
                if ($label === '2B') $rowData['values']['fg2_pts'] = $val;
                if ($label === '3B') $rowData['values']['fg3_pts'] = $val;
                if ($label === 'TH') {
                    if (str_contains($val, '/')) {
                        $parts = explode('/', $val);
                        $rowData['values']['ft_made'] = trim($parts[0]);
                        $rowData['values']['ft_att'] = trim($parts[1]);
                    } else {
                        $rowData['values']['ft_made'] = '0';
                        $rowData['values']['ft_att'] = '0';
                    }
                }
                if ($label === 'F-') $rowData['values']['fouls'] = $val;
                if ($label === 'B') $rowData['values']['pts'] = $val;
                if ($label === '+/-') $rowData['values']['plus_minus'] = $val;
            }

            // Validace: hráčský řádek je ten, kde existuje /hrac/{id} link nebo player_name není prázdné a číslo je číslo
            if ($rowData['player_external_id'] || (!empty($rowData['player_name']) && !empty($rowData['jersey_number']))) {
                $rows[] = $rowData;
            }
        });

        return $rows;
    }
}
