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
        'TH-Ú' => 'ft_made',
        'TH-P' => 'ft_att',
        'F+' => 'fouls_drawn',
        'F-' => 'fouls',
        'CH' => 'fouls',
        'MIN' => 'minutes',
        '+/-' => 'plus_minus',
        'DOS' => 'rebounds',
        'DOS-Ú' => 'rebounds_off',
        'DOS-O' => 'rebounds_def',
        'U' => 'rebounds_off',
        'O' => 'rebounds_def',
        'AS' => 'assists',
        'A' => 'assists',
        'ZIS' => 'steals',
        'Z' => 'steals',
        'ZTR' => 'turnovers',
        'T' => 'turnovers',
        'BL' => 'blocks',
        'VAL' => 'efficiency',
        'U%' => 'fg_pct',
        '2B-Ú' => 'fg2_made',
        '2B-P' => 'fg2_att',
        '3B-Ú' => 'fg3_made',
        '3B-P' => 'fg3_att',
    ];

    /**
     * Extrahuje boxscore a detailní informace ze stránky detailu zápasu.
     */
    public function extract(string $content, array $config = []): array
    {
        $crawler = new Crawler($content);
        $warnings = [];

        // 1. Hlavička zápasu (včetně rozpisu čtvrtin, rozhodčích, atd.)
        $matchHeader = $this->extractHeader($crawler);

        // 2. Nejlepší hráči (Best player)
        $bestPlayers = $this->extractBestPlayers($crawler);

        // 3. Tabulky statistik
        $tables = $crawler->filter('table.table-condensed');

        $allTablesData = [];
        $allFragmentHtml = '';

        $tables->each(function (Crawler $table, $i) use (&$allTablesData, &$allFragmentHtml, &$warnings, $matchHeader) {
            // Kontrola, zda je tabulka validní boxscore (musí mít aspoň 5 sloupců)
            if ($table->filter('thead th')->count() < 5) {
                return;
            }

            $tableName = $i === 0 ? ($matchHeader['home_team'] ?? 'Home Team Boxscore') : ($matchHeader['away_team'] ?? 'Away Team Boxscore');

            // Zkusíme najít název týmu nad tabulkou.
            $teamNameNode = $table->previousAll()->filter('h3, h4, .title')->last();
            if ($teamNameNode->count() === 0) {
                $container = $table->closest('div');
                $depth = 0;
                while ($container->count() > 0 && $teamNameNode->count() === 0 && $depth < 5) {
                    $teamNameNode = $container->previousAll()->filter('h3, h4, .title')->last();
                    if ($teamNameNode->count() > 0) {
                        break;
                    }
                    $container = $container->ancestors()->first();
                    $depth++;
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
            'best_players' => $bestPlayers,
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

        // Najdeme hlavní kontejner zápasu
        $mainContainer = $crawler->filter('.match-detail-header, .match-teams, .match-summary, .match_box, .match-header')->first();
        $searchIn = $mainContainer->count() > 0 ? $mainContainer : $crawler;

        // Týmy
        $homeNode = $searchIn->filter('.alfa, .score-home-team, .team-name, .team-home h1, .team-home h2, h1, h2, h4.text-center')->first();
        if ($homeNode->count() > 0) {
            $header['home_team'] = trim($homeNode->text());
        }

        $awayNodes = $searchIn->filter('.beta, .score-away-team, .team-name, .team-away h1, .team-away h2, h1, h2, h4.text-center');
        if ($awayNodes->count() >= 2) {
            $header['away_team'] = trim($awayNodes->eq(1)->text());
        } elseif ($awayNodes->count() === 1) {
             $beta = $searchIn->filter('.beta, .score-away-team, .team-away h1, .team-away h2')->first();
             if ($beta->count() > 0) {
                 $header['away_team'] = trim($beta->text());
             }
        }

        // Skóre
        $scoreNodes = $searchIn->filter('.match-header-score, .alfa.article-title, .match-score, .score, h1');
        if ($scoreNodes->count() > 0) {
            foreach ($scoreNodes as $node) {
                $text = trim($node->nodeValue);
                // Regex pro skóre (např. 82:55), kterému nepředchází jiná čísla (aby se nevzalo datum 3.8.)
                if (preg_match('/(?<![\d:])(\d{1,3}\s*:\s*\d{1,3})(?![\d:])/u', $text, $m)) {
                    $header['score'] = str_replace(' ', '', $m[1]);
                    break;
                }
            }
        }

        // Skóre po čtvrtinách (periods)
        $periods = [];
        $periodsNode = $searchIn->filter('.periods, .score-periods, .score-quarters, .match-quarters, .match-score-quarters');
        if ($periodsNode->count() > 0) {
            $header['periods_text'] = trim($periodsNode->text());
            // Zkusíme naparsovat čtvrtiny (např. 20:15, 10:12, ...)
            if (preg_match_all('/(\d+)\s*:\s*(\d+)/', $header['periods_text'], $m)) {
                foreach ($m[0] as $i => $pair) {
                    $periods[] = [
                        'home' => (int)$m[1][$i],
                        'away' => (int)$m[2][$i],
                    ];
                }
            }
        }

        if (empty($periods)) {
            // Hledáme tabulku s průběhem skóre po čtvrtinách (často v detailu zápasu)
            $scoreByQuartersTable = $crawler->filter('.table-quarters, .score-quarters-table, table:contains("1.č"), table:contains("1. č")')->first();
            if ($scoreByQuartersTable->count() > 0) {
                $qRows = $scoreByQuartersTable->filter('tr');
                if ($qRows->count() >= 2) {
                    $homeRow = $qRows->eq(0)->filter('td, th');
                    $awayRow = $qRows->eq(1)->filter('td, th');

                    for ($i = 1; $i < $homeRow->count(); $i++) {
                        $hVal = trim($homeRow->eq($i)->text());
                        $aVal = trim($awayRow->eq($i)->text());
                        if (is_numeric($hVal) && is_numeric($aVal)) {
                            $periods[] = [
                                'home' => (int)$hVal,
                                'away' => (int)$aVal,
                            ];
                        }
                    }
                }
            }
        }

        if (empty($periods)) {
            // Hledáme strukturu pod skóre v hlavičce (časté na cz.basketball)
            $scoreContainer = $searchIn->filter('.font-size-normal.font-weight-normal.mt-1.d-flex.justify-content-center')->first();
            if ($scoreContainer->count() > 0) {
                $scoreContainer->filter('.font-size-smaller.text-gray.font-weight-bold')->each(function (Crawler $div) use (&$periods) {
                    // Použijeme html() a nahradíme <br> mezerou, aby se čísla nespojila (např. 18<br>18 -> "18 18")
                    $html = $div->html();
                    $text = trim(str_replace(['<br>', '<br/>', '<br />'], ' ', $html));
                    $parts = preg_split('/\s+/', $text);
                    if (count($parts) >= 2 && is_numeric($parts[0]) && is_numeric($parts[1])) {
                        $periods[] = [
                            'home' => (int)$parts[0],
                            'away' => (int)$parts[1],
                        ];
                    }
                });

                // Pokud jsme našli průběžné stavy, musíme je převést na skóre jednotlivých čtvrtin
                if (!empty($periods)) {
                    $normalizedPeriods = [];
                    $lastHome = 0;
                    $lastAway = 0;
                    foreach ($periods as $p) {
                        $normalizedPeriods[] = [
                            'home' => $p['home'] - $lastHome,
                            'away' => $p['away'] - $lastAway,
                        ];
                        $lastHome = $p['home'];
                        $lastAway = $p['away'];
                    }
                    // Přidáme i konečné skóre jako poslední čtvrtinu, pokud se liší
                    if (isset($header['score']) && preg_match('/(\d+)\s*:\s*(\d+)/', $header['score'], $sm)) {
                        $finalHome = (int)$sm[1];
                        $finalAway = (int)$sm[2];
                        if ($finalHome > $lastHome || $finalAway > $lastAway) {
                            $normalizedPeriods[] = [
                                'home' => $finalHome - $lastHome,
                                'away' => $finalAway - $lastAway,
                            ];
                        }
                    }
                    $periods = $normalizedPeriods;
                }
            }
        }

        if (empty($periods)) {
            $allText = $searchIn->text();
            // Zkusíme najít čtvrtiny v závorkách (např. 26:8, 44:27, 60:48, 82:55)
            if (preg_match('/\(((\d+\s*:\s*\d+[\s,]*)+)\)/', $allText, $m)) {
                $header['periods_text'] = trim($m[1]);
                if (preg_match_all('/(\d+)\s*:\s*(\d+)/', $header['periods_text'], $pm)) {
                    $lastHome = 0;
                    $lastAway = 0;
                    foreach ($pm[0] as $i => $pair) {
                        $currentHome = (int)$pm[1][$i];
                        $currentAway = (int)$pm[2][$i];

                        // Pokud skóre roste, je to pravděpodobně průběžný stav
                        if ($currentHome >= $lastHome && $currentAway >= $lastAway && $i > 0) {
                             $periods[] = [
                                'home' => $currentHome - $lastHome,
                                'away' => $currentAway - $lastAway,
                            ];
                        } else {
                            $periods[] = [
                                'home' => $currentHome,
                                'away' => $currentAway,
                            ];
                        }
                        $lastHome = $currentHome;
                        $lastAway = $currentAway;
                    }
                }
            }
        }
        $header['periods'] = $periods;

        $dateNode = $searchIn->filter('.match-date, .date-time, .datetime')->first();
        if ($dateNode->count() > 0) {
            $header['date'] = trim($dateNode->text());
        }

        // Hala / Venue
        $venueNode = $searchIn->filter('.venue, .match-location, .location')->first();
        if ($venueNode->count() > 0) {
            $header['venue'] = trim($venueNode->text());
        }

        // Rozhodčí (Referees)
        $refereeNode = $searchIn->filter('.referees, .match-referees')->first();
        if ($refereeNode->count() > 0) {
            $header['referees'] = trim(str_replace('Rozhodčí:', '', $refereeNode->text()));
        }

        // Diváci (Attendance)
        $attendanceNode = $searchIn->filter('.attendance, .match-attendance, .spectators')->first();
        if ($attendanceNode->count() > 0) {
            $header['attendance'] = trim(str_replace('Diváci:', '', $attendanceNode->text()));
        } else {
            $allText = $searchIn->text();
            if (preg_match('/Diváci:\s*(\d+)/u', $allText, $m)) {
                $header['attendance'] = $m[1];
            }
        }

        // Komisař / Technical Delegate
        if (preg_match('/Komisař:\s*([^<>\n]+)/u', $searchIn->text(), $m)) {
            $header['commissioner'] = trim($m[1]);
        }

        return $header;
    }

    protected function extractBestPlayers(Crawler $crawler): array
    {
        $bestPlayers = [];

        // Na cz.basketball jsou nejlepší hráči v sekci s ID "nejlepsi-hrac" nebo v .best-player-card
        $bestPlayerSection = $crawler->filter('#nejlepsi-hrac, .best-players-section, .match-best-players, .best-players, .match-top-players');

        if ($bestPlayerSection->count() > 0) {
            $bestPlayerSection->filter('.best-player-card, .player-card, .best-player-item')->each(function (Crawler $card) use (&$bestPlayers) {
                $player = [];

                // Jméno a odkaz
                $nameNode = $card->filter('.player-name, .name, h4, h5, a')->first();
                if ($nameNode->count() > 0) {
                    $player['name'] = trim($nameNode->text());
                    $link = $card->filter('a[href*="/hrac/"]')->first();
                    if ($link->count() > 0 && preg_match('/\/hrac\/(\d+)/', $link->attr('href'), $m)) {
                        $player['external_id'] = $m[1];
                    }
                }

                // Fotka
                $imgNode = $card->filter('img')->first();
                if ($imgNode->count() > 0) {
                    $src = $imgNode->attr('src');
                    if ($src && !str_contains($src, 'data:image')) {
                        // Převod na absolutní URL pokud je relativní
                        if (str_starts_with($src, '/')) {
                            $src = 'https://cz.basketball' . $src;
                        }
                        $player['photo_url'] = $src;
                    }
                }

                // Tým (obvykle v záhlaví karty nebo jako text)
                $teamNode = $card->filter('.team-name, .team, small')->first();
                if ($teamNode->count() > 0) {
                    $player['team'] = trim($teamNode->text());
                }

                if (!empty($player)) {
                    $bestPlayers[] = $player;
                }
            });
        }

        return $bestPlayers;
    }

    protected function processBoxscoreTable(Crawler $table, string $tableName, array &$warnings): NormalizedTableDTO
    {
        // Hlavička tabulky pro mapování sloupců
        $columns = [];
        $headerRows = $table->filter('thead tr');
        $lastHeaderRow = $headerRows->last();

        $lastHeaderRow->filter('th')->each(function (Crawler $th, $i) use (&$columns, $table, $headerRows) {
            $label = trim($th->text());
            if ($th->attr('colspan') > 1 && $headerRows->count() > 1) {
                return; // Přeskočíme hlavičku s colspan (např. "2 body")
            }
            $normalizedLabel = mb_strtoupper(str_replace(' ', '', $label));

            // Pokus o kontext z nadřazené hlavičky, pokud je label "ÚSP", "POK" nebo "%"
            if ($headerRows->count() > 1 && in_array($normalizedLabel, ['ÚSP', 'POK', '%', 'Ú', 'P'])) {
                // Najdeme index sloupce v rámci všech TH v tomto řádku (včetně těch s colspan)
                // Tohle je složitější, ale zkusíme aspoň jednoduchý mapping podle pořadí
                // Na cz.basketball je to většinou 2b, 3b, TH v tomto pořadí
            }

            $key = $this->columnMapping[$normalizedLabel] ?? 'col_'.$i;
            $columns[$key] = $label;
        });

        // Řádky hráčů (včetně případné patičky s týmovými statistikami)
        $rows = [];
        $table->filter('tbody tr, tfoot tr')->each(function (Crawler $tr) use (&$rows, $columns, &$warnings) {
            $cells = $tr->filter('td, th');
            if ($cells->count() < 2) {
                return;
            }

            $values = [];
            $playerId = null;
            $playerName = null;
            $isCaptain = false;
            $isStarter = false;

            // Najdeme odkaz na hráče
            $playerLink = $tr->filter('a[href*="/hrac/"]')->first();
            if ($playerLink->count() > 0) {
                $playerName = trim($playerLink->text());
                if (preg_match('/\/hrac\/(\d+)/', $playerLink->attr('href'), $matches)) {
                    $playerId = $matches[1];
                }
            }

            // Pokud jméno obsahuje "Trenér", řádek přeskočíme
            if ($playerName && (str_contains(mb_strtolower($playerName), 'trenér') || str_contains(mb_strtolower($playerName), 'coach'))) {
                return;
            }

            // Mapujeme hodnoty buněk na klíče z hlavičky
            $i = 0;
            foreach ($columns as $key => $label) {
                if ($cells->count() > $i) {
                    $cell = $cells->eq($i);
                    $val = trim($cell->text());

                    // Ignorujeme řádky s trenéry i podle čísla dresu (pokud obsahuje licenci místo čísla)
                    if ($i === 0 && preg_match('/[A-Z]{2}\d+/', $val)) {
                         return;
                    }

                    // Pokud jsme jméno nenašli přes odkaz, zkusíme první buňky
                    if (! $playerName && ($key === 'col_0' || $key === 'col_1' || $key === 'player_name')) {
                        if (preg_match('/[a-zA-Z]/', $val)) {
                            $playerName = $val;
                        }
                    }

                    // Detekce kapitána (C) a startovní pětky (*) ve jméně nebo v buňce
                    if ($key === 'col_0' || $key === 'col_1' || $key === 'player_name' || $key === 'number') {
                        if (str_contains($val, '(C)') || str_contains($val, ' C')) {
                            $isCaptain = true;
                        }
                        if (str_contains($val, '*') || $cell->filter('i.fa-star, .starter')->count() > 0) {
                            $isStarter = true;
                        }
                    }

                    // Pokud hodnota obsahuje lomítko (např. 4/6), zkusíme ji rozdělit na made/att
                    if (str_contains($val, '/') && preg_match('/(\d+)\s*\/\s*(\d+)/', $val, $ratioMatches)) {
                        $made = (int) $ratioMatches[1];
                        $att = (int) $ratioMatches[2];

                        if (str_contains($key, 'fg2')) {
                            $values['fg2_made'] = $made;
                            $values['fg2_att'] = $att;
                        } elseif (str_contains($key, 'fg3')) {
                            $values['fg3_made'] = $made;
                            $values['fg3_att'] = $att;
                        } elseif (str_contains($key, 'ft')) {
                            $values['ft_made'] = $made;
                            $values['ft_att'] = $att;
                        } else {
                            // Fallback pro 2B, 3B, TH klíče
                            $prefix = '';
                            if ($key === 'fg2_made') $prefix = 'fg2';
                            elseif ($key === 'fg3_made') $prefix = 'fg3';
                            elseif ($key === 'ft_made') $prefix = 'ft';

                            if ($prefix) {
                                $values[$prefix.'_made'] = $made;
                                $values[$prefix.'_att'] = $att;
                            } else {
                                $values[$key] = $val;
                            }
                        }
                    } else {
                        // Převod na číslo, pokud to jde
                        $cleanVal = str_replace(',', '.', $val);
                        if (is_numeric($cleanVal)) {
                            $values[$key] = (float) $cleanVal;
                        } else {
                            $values[$key] = $val;
                        }
                    }
                }
                $i++;
            }

            // Pokud je to sumární řádek (tým)
            $classString = $tr->attr('class') ?: '';
            $isTotal = str_contains($classString, 'total') ||
                       str_contains($classString, 'success') ||
                       str_contains($classString, 'info') ||
                       str_contains(mb_strtolower($playerName ?? ''), 'celkem') ||
                       (str_contains(mb_strtolower($playerName ?? ''), 'tým') && ! str_contains(mb_strtolower($playerName ?? ''), '/'));

            if ($playerName || $isTotal) {
                $rowLabel = $isTotal ? ($playerName ?: 'Tým celkem') : $playerName;

                // Vyčištění jména (pokud obsahuje hvězdičku nebo (C))
                if ($rowLabel) {
                    $rowLabel = trim(str_replace(['*', '(C)'], '', $rowLabel));
                }

                $rows[] = new NormalizedRowDTO(
                    values: $values,
                    rowLabel: $rowLabel,
                    metadata: array_filter([
                        'external_player_id' => $playerId,
                        'is_captain' => $isCaptain ?: null,
                        'is_starter' => $isStarter ?: null,
                        'is_total' => $isTotal ?: null,
                    ])
                );
            }
        });

        return new NormalizedTableDTO($tableName, $columns, $rows);
    }
}
