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

        // 3. Srovnání týmů
        $teamComparison = $this->extractTeamComparison($crawler);

        // 4. Poslední zápasy
        $lastMatches = $this->extractLastMatches($crawler);

        // 5. Tabulky statistik
        $tables = $crawler->filter('table.table-condensed');

        $allTablesData = [];
        $allFragmentHtml = '';

        $tables->each(function (Crawler $table, $i) use (&$allTablesData, &$allFragmentHtml, &$warnings, $matchHeader, $bestPlayers, $teamComparison, $lastMatches) {
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

            // Přidáme globální metadata ke každé tabulce
            $tableDto->metadata = array_merge($tableDto->metadata, [
                'header' => $matchHeader,
                'best_players' => $bestPlayers,
                'team_comparison' => $teamComparison,
                'last_matches' => $lastMatches,
            ]);

            $allTablesData[] = $tableDto;
        });

        // Pro zjednodušení vracíme první tabulku jako hlavní data, ale v metadatech máme vše
        $mainTable = $allTablesData[0] ?? new NormalizedTableDTO('Boxscore', [], [], ['warnings' => ['No tables found']]);

        $mainTable->metadata = array_merge($mainTable->metadata, [
            'header' => $matchHeader,
            'best_players' => $bestPlayers,
            'team_comparison' => $teamComparison,
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
        $mainContainer = $crawler->filter('.match-detail-header, .match-teams, .match-summary, .match_box, .match-header, .wrapper.bg-white.box-shadow')->first();
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

                // Pokud jsme našli stavy, uložíme je tak, jak jsou (kumulativně)
                if (!empty($periods)) {
                    // Už neprovádíme normalizaci (odečítání) ani nepřidáváme poslední čtvrtinu
                    // Uživatel chce vidět přesně to, co je na webu (stavy po Q1, Q2, Q3)
                }
            }
        }

        if (empty($periods)) {
            $allText = $searchIn->text();
            // Zkusíme najít čtvrtiny v závorkách (např. 26:8, 44:27, 60:48, 82:55)
            if (preg_match('/\(((\d+\s*:\s*\d+[\s,]*)+)\)/', $allText, $m)) {
                $header['periods_text'] = trim($m[1]);
                if (preg_match_all('/(\d+)\s*:\s*(\d+)/', $header['periods_text'], $pm)) {
                    foreach ($pm[0] as $i => $pair) {
                        $periods[] = [
                            'home' => (int)$pm[1][$i],
                            'away' => (int)$pm[2][$i],
                        ];
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
        } else {
            // Fallback pro strukturu, kde je "Rozhodčí:" v textu divu (časté na cz.basketball)
            $searchIn->filter('div, p, span')->each(function (Crawler $node) use (&$header) {
                $text = trim($node->text());
                if (str_contains($text, 'Rozhodčí:')) {
                    $clean = trim(str_replace('Rozhodčí:', '', $text));
                    if (!empty($clean)) {
                        $header['referees'] = $clean;
                    }
                }
            });
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

        // Komisař (Commissioner)
        $commissionerNode = $searchIn->filter('.commissioner, .match-commissioner')->first();
        if ($commissionerNode->count() > 0) {
            $header['commissioner'] = trim(str_replace('Komisař:', '', $commissionerNode->text()));
        } else {
            // Regex fallback
            if (preg_match('/Komisař:\s*([^<>\n]+)/u', $searchIn->text(), $m)) {
                $header['commissioner'] = trim($m[1]);
            }
        }

        return $header;
    }

    protected function extractBestPlayers(Crawler $crawler): array
    {
        $bestPlayers = [];

        // Mapování českých popisků kategorií na kanonické klíče
        $categoryMapping = [
            'Body' => 'points',
            'Doskoky' => 'rebounds',
            'Asistence' => 'assists',
            'Zisky' => 'steals',
            'Bloky' => 'blocks',
        ];

        // Hledáme řádky s kategoriemi nejlepších hráčů
        // Každá kategorie (Body, Doskoky, ...) je v jednom řádku (row)
        $crawler->filter('.row')->each(function (Crawler $row) use (&$bestPlayers, $categoryMapping) {
            $categoryNode = $row->filter('h4')->first();
            if ($categoryNode->count() === 0) {
                return;
            }

            $originalCategory = trim($categoryNode->text());
            $category = $categoryMapping[$originalCategory] ?? null;

            if (!$category) {
                return;
            }

            $playersInCategory = [
                'label' => $originalCategory,
                'home' => null,
                'away' => null,
            ];

            // Domácí hráč (vlevo, obvykle order-xl-1 nebo order-md-1)
            $homeCard = $row->filter('.order-xl-1 .box-shadow, .order-md-1 .box-shadow, .order-xl-1 .bg-white, .order-md-1 .bg-white')->first();
            if ($homeCard->count() > 0) {
                $playersInCategory['home'] = $this->parsePlayerCard($homeCard);
            }

            // Hostující hráč (vpravo, obvykle order-xl-3 nebo order-md-3)
            $awayCard = $row->filter('.order-xl-3 .box-shadow, .order-md-3 .box-shadow, .order-xl-3 .bg-white, .order-md-3 .bg-white')->first();
            if ($awayCard->count() > 0) {
                $playersInCategory['away'] = $this->parsePlayerCard($awayCard);
            }

            if ($playersInCategory['home'] || $playersInCategory['away']) {
                $bestPlayers[$category] = $playersInCategory;
            }
        });

        // Fallback pro starší strukturu (pokud se nepoužívá nová řádková struktura)
        if (empty($bestPlayers)) {
            $bestPlayerSection = $crawler->filter('#nejlepsi-hrac, .best-players-section, .match-best-players, .best-players, .match-top-players');
            if ($bestPlayerSection->count() > 0) {
                $bestPlayerSection->filter('.best-player-card, .player-card, .best-player-item')->each(function (Crawler $card) use (&$bestPlayers) {
                    $player = $this->parsePlayerCard($card);
                    if ($player) {
                        $bestPlayers['General'][] = $player;
                    }
                });
            }
        }

        return $bestPlayers;
    }

    protected function parsePlayerCard(Crawler $card): ?array
    {
        $player = [];

        // Jméno a odkaz
        $nameNode = $card->filter('.player-name, .name, h4, h5, a .text-primary, .gamma a')->first();
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
            $src = $imgNode->attr('data-src') ?: $imgNode->attr('src');
            if ($src && !str_contains($src, 'data:image')) {
                // Převod na absolutní URL pokud je relativní
                if (str_starts_with($src, '/')) {
                    $src = 'https://cz.basketball' . $src;
                }
                $player['photo_url'] = $src;
            }
        }

        // Hodnota (např. 18.0 bodů)
        $valueNode = $card->filter('.gamma.text-green, .gamma.text-secondary, .value, .pts, .score')->first();
        $player['value'] = $valueNode->count() > 0 ? trim($valueNode->text()) : '';

        return !empty($player['name']) ? $player : null;
    }

    protected function extractTeamComparison(Crawler $crawler): array
    {
        $comparison = [];

        // Mapování českých popisků na kanonické klíče
        $labelMapping = [
            'Průměrný věk' => 'average_age',
            'Počet národností' => 'nationality_count',
            'Prům. zápasová zkušenost' => 'average_match_experience',
            'Průměrná výška' => 'average_height',
        ];

        // Iterujeme přes všechny řádky v sekci srovnání
        // Na cz.basketball jsou to řádky s h4 jako titulkem uprostřed
        $crawler->filter('.row.no-gutters.justify-content-md-center')->each(function (Crawler $row) use (&$comparison, $labelMapping) {
            $labelNode = $row->filter('h4')->first();
            if ($labelNode->count() === 0) {
                return;
            }

            $originalLabel = trim($labelNode->text());
            $label = $labelMapping[$originalLabel] ?? strtolower(str_replace(' ', '_', $originalLabel));

            $homeValNode = $row->filter('.order-md-1 .delta, .order-1 .delta')->first();
            $awayValNode = $row->filter('.order-md-3 .delta, .order-3 .delta')->first();

            if ($homeValNode->count() > 0 && $awayValNode->count() > 0) {
                $comparison[$label] = [
                    'label' => $originalLabel,
                    'home' => trim($homeValNode->text()),
                    'away' => trim($awayValNode->text()),
                ];
            }
        });

        return $comparison;
    }

    protected function extractLastMatches(Crawler $crawler): array
    {
        $lastMatches = [
            'home' => [],
            'away' => [],
        ];

        $sections = $crawler->filter('.row.mb-10');
        if ($sections->count() >= 1) {
            // První sekce .row.mb-10 po "Poslední zápasy"
            $sections->each(function (Crawler $section, $i) use (&$lastMatches) {
                // Obvykle jsou tam dva sloupce (home team last matches, away team last matches)
                $columns = $section->filter('.col-12.col-md-6');

                $columns->each(function (Crawler $column, $colIdx) use (&$lastMatches) {
                    $side = $colIdx === 0 ? 'home' : 'away';

                    $column->filter('.d-flex.rounded')->each(function (Crawler $matchRow) use (&$lastMatches, $side) {
                        $dateNode = $matchRow->filter('.col-12.col-md-2')->first();
                        $teamsNode = $matchRow->filter('.col-auto.col-md-6')->first();
                        $scoreNode = $matchRow->filter('.col-2.text-center')->first();

                        if ($dateNode->count() > 0 && $teamsNode->count() > 0 && $scoreNode->count() > 0) {
                            $date = trim(str_replace("\n", " ", $dateNode->text()));
                            $date = preg_replace('/\s+/', ' ', $date);

                            $linkNode = $teamsNode->filter('a')->first();
                            $teamHtml = $linkNode->count() > 0 ? $linkNode->html() : $teamsNode->html();
                            $teamLines = array_values(array_filter(array_map('trim', explode("\n", strip_tags(str_replace(['</div>', '<div>', '<br>', '<br/>'], "\n", $teamHtml))))));

                            $team1 = $teamLines[0] ?? '';
                            $team2 = $teamLines[1] ?? '';

                            $scoreHtml = $scoreNode->html();
                            $scoreLines = array_values(array_filter(array_map('trim', explode("\n", strip_tags(str_replace(['</div>', '<div>', '<br>', '<br/>'], "\n", $scoreHtml))))));

                            $score1 = $scoreLines[0] ?? '';
                            $score2 = $scoreLines[1] ?? '';

                            $link = $teamsNode->filter('a')->attr('href');
                            $matchId = null;
                            if (preg_match('/\/zapas\/(\d+)/', $link, $m)) {
                                $matchId = $m[1];
                            }

                            $lastMatches[$side][] = [
                                'date' => $date,
                                'team_home' => $team1,
                                'team_away' => $team2,
                                'score_home' => $score1,
                                'score_away' => $score2,
                                'external_id' => $matchId,
                            ];
                        }
                    });
                });
            });
        }

        return $lastMatches;
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

            // Mapujeme hodnoty buněk na klíče z hlavičky
            $i = 0;
            foreach ($columns as $key => $label) {
                if ($cells->count() > $i) {
                    $cell = $cells->eq($i);
                    // Pro buňky se statistikami (2B, 3B, TH) zkusíme vyčistit vnořené tagy,
                    // které by mohly způsobit spojení textu (např. procenta u celkem)
                    if ($cell->filter('div, span, small')->count() > 0) {
                        $html = $cell->html();
                        $val = trim(str_replace(['<br>', '<br/>', '<br />', '<div>', '</div>', '<span>', '</span>', '<small>', '</small>'], ' ', $html));
                        // Nahradíme vícenásobné mezery jednou
                        $val = preg_replace('/\s+/', ' ', $val);
                    } else {
                        $val = trim($cell->text());
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
                    // Odstraníme případná procenta nebo doplňující text za čísly (např. "12/17 70%")
                    $cleanRatio = preg_replace('/[^\d\/].*$/', '', $val);
                    if (str_contains($cleanRatio, '/') && preg_match('/(\d+)\s*\/\s*(\d+)/', $cleanRatio, $ratioMatches)) {
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
