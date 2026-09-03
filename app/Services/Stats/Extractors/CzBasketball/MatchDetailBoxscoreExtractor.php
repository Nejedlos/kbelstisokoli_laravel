<?php

namespace App\Services\Stats\Extractors\CzBasketball;

use App\Services\Stats\Contracts\StatExtractorInterface;
use App\Services\Stats\DTO\NormalizedRowDTO;
use App\Services\Stats\DTO\NormalizedTableDTO;
use Carbon\Carbon;
use Symfony\Component\DomCrawler\Crawler;

class MatchDetailBoxscoreExtractor implements StatExtractorInterface
{
    /**
     * Mapování českých zkratek na kanonické klíče.
     */
    protected array $columnMapping = [
        'HRÁČ' => 'name',
        'JMÉNO' => 'name',
        'HRÁČ-TÝM' => 'name',
        '#' => 'number',
        'Č.' => 'number',
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
        'FAULY' => 'fouls',
        'MIN' => 'minutes',
        '+/-' => 'plus_minus',
        'DOS' => 'rebounds_total',
        'DOS-Ú' => 'rebounds_offensive',
        'DOS-O' => 'rebounds_defensive',
        'U' => 'rebounds_offensive',
        'O' => 'rebounds_defensive',
        'DOSKOKY' => 'rebounds_total',
        'AS' => 'assists',
        'A' => 'assists',
        'ASISTENCE' => 'assists',
        'ZIS' => 'steals',
        'ZÍS' => 'steals',
        'Z' => 'steals',
        'ZTR' => 'turnovers',
        'T' => 'turnovers',
        'BL' => 'blocks',
        'VAL' => 'efficiency',
        'VALUACE' => 'efficiency',
        'EF' => 'efficiency',
        'EFKT' => 'efficiency',
        'EF-K' => 'efficiency',
        'VAL-K' => 'efficiency',
        'VAL-Ú' => 'efficiency',
        'UŽIT' => 'efficiency',
        'UŽITNOST' => 'efficiency',
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

        // 4a. Vzájemné zápasy
        $mutualMatches = $this->extractMutualMatches($crawler);

        // 5. Tabulky statistik
        // Dříve: table.table-condensed, nyní stačí prostě table, jelikož boxscore tabulky jsou ty hlavní na stránce
        $tables = $crawler->filter('table');

        $allTablesData = [];
        $allFragmentHtml = "<!-- Match Header -->\n".json_encode($matchHeader)."\n";
        $allFragmentHtml .= "<!-- Leaders -->\n".json_encode($bestPlayers)."\n";
        $allFragmentHtml .= "<!-- Comparison -->\n".json_encode($teamComparison)."\n";
        $allFragmentHtml .= "<!-- Mutual -->\n".json_encode($mutualMatches)."\n";

        $tables->each(function (Crawler $table, $i) use (&$allTablesData, &$allFragmentHtml, &$warnings, $matchHeader, $bestPlayers, $teamComparison, $lastMatches, $mutualMatches) {
            // Kontrola, zda je tabulka validní boxscore (musí mít aspoň 5 sloupců)
            if ($table->filter('thead th')->count() < 8) {
                return;
            }

            // Ignorujeme tabulky, které nejsou boxscore (např. "Poslední zápasy", "Vzájemné zápasy")
            $tableText = mb_strtolower($table->text());
            if (str_contains($tableText, 'poslední zápasy') || str_contains($tableText, 'vzájemné zápasy') || str_contains($tableText, 'fáze sezony')) {
                return;
            }

            $tableName = $i === 0 ? ($matchHeader['home_team'] ?? 'Home Team Boxscore') : ($matchHeader['away_team'] ?? 'Away Team Boxscore');

            // Zkusíme najít název týmu nad tabulkou.
            $teamNameNode = $table->previousAll()->filter('h3, h4, .title')->last();
            if ($teamNameNode->count() === 0) {
                $container = $table->closest('div');
                $depth = 0;
                $lastContainerHash = null;
                while ($container->count() > 0 && $teamNameNode->count() === 0 && $depth < 8) {
                    $currentHash = spl_object_hash($container->getNode(0));
                    if ($lastContainerHash === $currentHash) {
                        break;
                    }
                    $lastContainerHash = $currentHash;

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
                'mutual_matches' => $mutualMatches,
            ]);

            $allTablesData[] = $tableDto;
        });

        // Pokud nejsou žádné tabulky a zápas nemá žádné detaily o čtvrtinách, pravděpodobně jde o budoucí zápas.
        // Vše podstatné by už mělo být vyřešeno v extractHeader().

        // Pro zjednodušení vracíme první tabulku jako hlavní data, ale v metadatech máme vše
        $mainTable = $allTablesData[0] ?? new NormalizedTableDTO(
            name: 'Boxscore',
            columns: [],
            rows: [],
            warnings: ['No tables found'],
        );

        $mainTable->metadata = array_merge($mainTable->metadata, [
            'header' => $matchHeader,
            'best_players' => $bestPlayers,
            'team_comparison' => $teamComparison,
            'last_matches' => $lastMatches,
            'mutual_matches' => $mutualMatches,
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

        // Týmy (pokročilá detekce s podporou pro .alfa u obou týmů a h4 jako fallback)
        $allPossibleTeams = $searchIn->filter('.alfa, .beta, .score-home-team, .score-away-team, .team-home h1, .team-home h2, .team-away h1, .team-away h2, h4.text-center');
        // Odfiltrujeme skóre, které má často také třídu .alfa a .article-title
        $teamNodes = $allPossibleTeams->reduce(function (Crawler $node) {
            $classes = explode(' ', (string) $node->attr('class'));
            if (in_array('article-title', $classes) || in_array('score', $classes) || in_array('match-header-score', $classes)) {
                return false;
            }

            return true;
        });

        if ($teamNodes->count() >= 2) {
            $header['home_team'] = trim($teamNodes->eq(0)->text());
            $header['away_team'] = trim($teamNodes->eq(1)->text());
        } else {
            // Původní fallback logika
            $homeNode = $searchIn->filter('.alfa, .score-home-team, .team-home h1, .team-home h2, h4.text-center')->first();
            if ($homeNode->count() > 0) {
                try {
                    $header['home_team'] = trim($homeNode->text());
                } catch (\Exception $e) {
                }
            }

            $awayNodes = $searchIn->filter('.beta, .score-away-team, .team-away h1, .team-away h2, h4.text-center');
            if ($awayNodes->count() >= 2) {
                try {
                    $header['away_team'] = trim($awayNodes->eq(1)->text());
                } catch (\Exception $e) {
                }
            } elseif ($awayNodes->count() === 1) {
                $beta = $searchIn->filter('.beta, .score-away-team, .team-away h1, .team-away h2')->first();
                if ($beta->count() > 0) {
                    try {
                        $header['away_team'] = trim($beta->text());
                    } catch (\Exception $e) {
                    }
                }
            }
        }

        // Skóre
        $scoreNodes = $searchIn->filter('.match-header-score, .alfa.article-title, .match-score, .score, h1');
        if ($scoreNodes->count() > 0) {
            foreach ($scoreNodes as $node) {
                $text = trim($node->nodeValue);

                // Odstraníme závorky pro lepší parsování (časté na cz.basketball v hlavičce)
                $text = str_replace(['(', ')'], ' ', $text);

                // Regex pro skóre (např. 82:55), kterému nepředchází jiná čísla (aby se nevzalo datum 3.8.)
                // Upraveno pro ignorování závorek a bílých znaků uvnitř závorek
                if (preg_match('/(?<![\d:])(\d{1,3})\s*:\s*(\d{1,3})(?![\d:])/u', $text, $m)) {
                    $header['score'] = $m[1].':'.$m[2];
                    break;
                }
            }
        }

        // Skóre po čtvrtinách (periods)
        $periods = [];
        $periodsNode = $searchIn->filter('.periods, .score-periods, .score-quarters, .match-quarters, .match-score-quarters');
        if ($periodsNode->count() > 0) {
            try {
                $header['periods_text'] = trim($periodsNode->text());
                // Zkusíme naparsovat čtvrtiny (např. 20:15, 10:12, ...)
                if (preg_match_all('/(\d+)\s*:\s*(\d+)/', $header['periods_text'], $m)) {
                    foreach ($m[0] as $i => $pair) {
                        $periods[] = [
                            'home' => (int) $m[1][$i],
                            'away' => (int) $m[2][$i],
                        ];
                    }
                }
            } catch (\Exception $e) {
            }
        }

        if (empty($periods)) {
            // Hledáme tabulku s průběhem skóre po čtvrtinách (často v detailu zápasu)
            $scoreByQuartersTable = $crawler->filter('.table-quarters, .score-quarters-table')->first();
            if ($scoreByQuartersTable->count() === 0) {
                // XPath fallback pro tabulku obsahující text čtvrtin
                try {
                    $scoreByQuartersTable = $crawler->filterXPath("//table[contains(., '1.č') or contains(., '1. č')]")->first();
                } catch (\Exception $e) {
                    // Ignorujeme chyby v XPath
                }
            }

            if ($scoreByQuartersTable && $scoreByQuartersTable->count() > 0) {
                $qRows = $scoreByQuartersTable->filter('tr');
                if ($qRows->count() >= 2) {
                    $homeRow = $qRows->eq(0)->filter('td, th');
                    $awayRow = $qRows->eq(1)->filter('td, th');

                    for ($i = 1; $i < $homeRow->count(); $i++) {
                        $hVal = trim($homeRow->eq($i)->text());
                        $aVal = trim($awayRow->eq($i)->text());
                        if (is_numeric($hVal) && is_numeric($aVal)) {
                            $periods[] = [
                                'home' => (int) $hVal,
                                'away' => (int) $aVal,
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
                            'home' => (int) $parts[0],
                            'away' => (int) $parts[1],
                        ];
                    }
                });

                // Pokud jsme našli stavy, pravděpodobně jsou kumulativní (na cz.basketball běžné)
                if (! empty($periods)) {
                    // 1. Přidáme konečné skóre jako poslední periodu, pokud se liší od poslední nalezené
                    if (isset($header['score'])) {
                        $scoreParts = explode(':', $header['score']);
                        if (count($scoreParts) === 2) {
                            $finalHome = (int) $scoreParts[0];
                            $finalAway = (int) $scoreParts[1];
                            $lastPeriod = end($periods);

                            if ($lastPeriod['home'] !== $finalHome || $lastPeriod['away'] !== $finalAway) {
                                $periods[] = [
                                    'home' => $finalHome,
                                    'away' => $finalAway,
                                ];
                            }
                        }
                    }

                    // 2. Převod kumulativního skóre na body v jednotlivých čtvrtinách
                    $normalizedPeriods = [];
                    $prevHome = 0;
                    $prevAway = 0;
                    foreach ($periods as $period) {
                        $normalizedPeriods[] = [
                            'home' => $period['home'] - $prevHome,
                            'away' => $period['away'] - $prevAway,
                        ];
                        $prevHome = $period['home'];
                        $prevAway = $period['away'];
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
                    foreach ($pm[0] as $i => $pair) {
                        $periods[] = [
                            'home' => (int) $pm[1][$i],
                            'away' => (int) $pm[2][$i],
                        ];
                    }
                }
            }
        }
        if (! empty($periods) && empty($header['periods_text'])) {
            $header['periods_text'] = implode(', ', array_map(fn ($p) => $p['home'].':'.$p['away'], $periods));
        }
        $header['periods'] = $periods;

        $dateNode = $searchIn->filter('.match-date, .date-time, .datetime, .font-size-smaller.font-size-md-normal.ml-md-4.mr-4.mb-2.mb-md-4')->first();
        if ($dateNode->count() > 0) {
            $dateStr = trim($dateNode->text());
            $header['date'] = $dateStr;

            // Pokud je tam více divů se stejnou třídou, zkusíme najít soutěž a halu
            $infoNodes = $searchIn->filter('.font-size-smaller.font-size-md-normal.ml-md-4.mr-4.mb-2.mb-md-4');
            $infoNodes->each(function (Crawler $node) use (&$header) {
                $text = trim($node->text());
                if (str_contains($text, ':') && (str_contains($text, 'kolo') || str_contains($text, 'liga') || str_contains($text, 'přebor'))) {
                    $header['competition'] = $text;
                    if (preg_match('/\((.*kolo.*)\)/i', $text, $km)) {
                        $header['round'] = $km[1];
                    }
                } elseif ($node->filter('a')->count() > 0 && str_contains($node->filter('a')->attr('href') ?: '', 'haly')) {
                    $header['venue'] = $text;
                }
            });

            // Pokus o parsování na Carbon
            try {
                // Formát: 7. 1. 2026 - 19:15
                // Odstraníme pomlčku a dny v týdnu
                $cleanDateStr = preg_replace('/(Po|Út|St|Čt|Pá|So|Ne)\s*/', '', $dateStr);
                $cleanDateStr = str_replace('-', '', $cleanDateStr);
                $cleanDateStr = preg_replace('/\s+/', ' ', trim($cleanDateStr));

                // Formát: 7. 1. 2026 19:15
                $header['scheduled_at'] = Carbon::createFromFormat('j. n. Y H:i', $cleanDateStr, 'Europe/Prague')->toDateTimeString();
            } catch (\Exception $e) {
                // Pokud selže, zkusíme najít datum v textu obecněji
                if (preg_match('/(\d+\.\s*\d+\.\s*\d{4})\s*[\-\s]*(\d{1,2}:\d{2})/', $dateStr, $m)) {
                    $cleanDateStr = preg_replace('/\s+/', ' ', trim($m[1].' '.$m[2]));
                    try {
                        $header['scheduled_at'] = Carbon::createFromFormat('j. n. Y G:i', $cleanDateStr, 'Europe/Prague')->toDateTimeString();
                    } catch (\Exception $e2) {
                        // Ignorujeme
                    }
                }
            }

            // Pokud skóre vypadá jako čas a shoduje se s časem začátku, nebo pokud je zápas v budoucnu a skóre má podezřelý formát, zrušíme ho.
            if (isset($header['score']) && isset($header['scheduled_at'])) {
                $scheduledAt = Carbon::parse($header['scheduled_at']);
                $scheduledTime = $scheduledAt->format('H:i');

                // Pokud scheduled_at nemá čas (je tam jen datum), zkusíme ho vzít ze skóre (pokud vypadá jako čas)
                if (str_contains($header['scheduled_at'], '00:00:00') && preg_match('/^\d{1,2}:\d{2}$/', $header['score'])) {
                    $datePart = explode(' ', $header['scheduled_at'])[0];
                    $header['scheduled_at'] = $datePart.' '.$header['score'].':00';
                    $header['is_future'] = true;
                    unset($header['score']);
                }
                // Přesná shoda s časem (již nastaveným)
                elseif ($header['score'] === $scheduledTime) {
                    $header['is_future'] = true;
                    unset($header['score']);
                }
                // Zápas je v budoucnu (více než 15 minut) a skóre vypadá jako čas (HH:MM)
                elseif ($scheduledAt->isFuture() && preg_match('/^\d{1,2}:\d{2}$/', $header['score'])) {
                    // Pokud nemáme žádné čtvrtiny, je to skoro jistě čas
                    if (empty($periods)) {
                        $header['is_future'] = true;
                        unset($header['score']);
                    }
                }
            }
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
                    if (! empty($clean)) {
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

            if (! $category) {
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
            if ($src && ! str_contains($src, 'data:image')) {
                // Převod na absolutní URL pokud je relativní
                if (str_starts_with($src, '/')) {
                    $src = 'https://cz.basketball'.$src;
                }
                $player['photo_url'] = $src;
            }
        }

        // Hodnota (např. 18.0 bodů)
        $valueNode = $card->filter('.gamma.text-green, .gamma.text-secondary, .value, .pts, .score')->first();
        $player['value'] = $valueNode->count() > 0 ? trim($valueNode->text()) : '';

        return ! empty($player['name']) ? $player : null;
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
            'Body na zápas' => 'pts_per_game',
            'Doskoky' => 'rebounds_per_game',
            'Asistence' => 'assists_per_game',
            'Ztráty' => 'turnovers_per_game',
            'Zisky' => 'steals_per_game',
            'Uspěšnost trestných hodů' => 'ft_pct',
            'Uspěšnost 2b' => 'fg2_pct',
            'Uspěšnost 3b' => 'fg3_pct',
        ];

        // Varianta 1: Flexibilní řádky s h4 (současná implementace)
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

        // Varianta 2: Tabulka s "Rozvahou" (časté u neodehraných zápasů)
        if (empty($comparison)) {
            $previewTable = $crawler->filter('table')->reduce(function (Crawler $node) {
                $text = $node->text();

                return str_contains($text, 'Body na zápas') || str_contains($text, 'Doskoky') || str_contains($text, 'Průměrný věk');
            })->first();

            if ($previewTable->count() > 0) {
                $previewTable->filter('tr')->each(function (Crawler $tr) use (&$comparison, $labelMapping) {
                    $tds = $tr->filter('td');
                    if ($tds->count() === 3) {
                        $originalLabel = trim($tds->eq(1)->text());
                        $label = $labelMapping[$originalLabel] ?? strtolower(str_replace(' ', '_', $originalLabel));
                        $comparison[$label] = [
                            'label' => $originalLabel,
                            'home' => trim($tds->eq(0)->text()),
                            'away' => trim($tds->eq(2)->text()),
                        ];
                    }
                });
            }
        }

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
                            $date = trim(str_replace("\n", ' ', $dateNode->text()));
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
                                'score_home' => str_replace('.', '', $score1),
                                'score_away' => str_replace('.', '', $score2),
                                'external_id' => $matchId,
                            ];
                        }
                    });
                });
            });
        }

        return $lastMatches;
    }

    protected function extractMutualMatches(Crawler $crawler): array
    {
        $mutualMatches = [];

        // Hledáme nadpis "Vzájemné zápasy"
        $header = $crawler->filter('h3')->reduce(function (Crawler $node) {
            return str_contains($node->text(), 'Vzájemné zápasy');
        })->first();

        if ($header->count() > 0) {
            // Tabulka by měla být hned pod nadpisem v divu overflow-auto
            $table = $header->nextAll()->filter('table')->first();
            if ($table->count() > 0) {
                $table->filter('tbody tr')->each(function (Crawler $tr) use (&$mutualMatches) {
                    $tds = $tr->filter('td');
                    if ($tds->count() >= 4) {
                        $kolo = trim($tds->eq(0)->text());
                        $dateText = trim($tds->eq(1)->text());
                        $date = preg_replace('/\s+/', ' ', $dateText);

                        $teamsNode = $tds->eq(2);
                        $teamNames = $teamsNode->filter('.text-nowrap');
                        $homeTeam = $teamNames->count() > 0 ? trim($teamNames->eq(0)->text()) : '';
                        $awayTeam = $teamNames->count() > 1 ? trim($teamNames->eq(1)->text()) : '';

                        $scoreNode = $tds->eq(3);
                        $scoreDivs = $scoreNode->filter('div'); // Skóre může být v divu s font-weight-bold
                        // Jednodušší přístup: vyčistit text od čtvrtin
                        $scoreText = trim($scoreNode->text());
                        // Skóre je obvykle první dvě čísla
                        $scoreLines = array_values(array_filter(array_map('trim', explode("\n", strip_tags(str_replace(['</div>', '<div>', '<br>', '<br/>'], "\n", $scoreNode->html()))))));
                        $scoreHome = str_replace('.', '', $scoreLines[0] ?? '');
                        $scoreAway = str_replace('.', '', $scoreLines[1] ?? '');

                        $link = $tds->filter('a[href*="/zapas/"]')->first();
                        $matchId = null;
                        if ($link->count() > 0 && preg_match('/\/zapas\/(\d+)/', $link->attr('href'), $m)) {
                            $matchId = $m[1];
                        }

                        $mutualMatches[] = [
                            'round' => $kolo,
                            'date' => $date,
                            'team_home' => $homeTeam,
                            'team_away' => $awayTeam,
                            'score_home' => $scoreHome,
                            'score_away' => $scoreAway,
                            'external_id' => $matchId,
                        ];
                    }
                });
            }
        }

        return $mutualMatches;
    }

    protected function processBoxscoreTable(Crawler $table, string $tableName, array &$warnings): NormalizedTableDTO
    {
        // Hlavička tabulky pro mapování sloupců
        $columns = [];
        $headerRows = $table->filter('thead tr');
        $lastHeaderRow = $headerRows->last();

        $lastHeaderRow->filter('th')->each(function (Crawler $th, $i) use (&$columns, $headerRows) {
            $label = trim($th->text());
            if ($th->attr('colspan') > 1 && $headerRows->count() > 1) {
                return; // Přeskočíme hlavičku s colspan (např. "2 body")
            }

            $abbr = $th->filter('abbr');
            $abbrTitle = $abbr->count() > 0 ? trim($abbr->attr('title') ?: '') : '';

            $normalizedLabel = mb_strtoupper(str_replace(' ', '', $label));
            $normalizedAbbrTitle = mb_strtoupper(str_replace(' ', '', $abbrTitle));

            $key = $this->columnMapping[$normalizedLabel] ?? $this->columnMapping[$normalizedAbbrTitle] ?? 'col_'.$i;
            $columns[$key] = $label ?: $abbrTitle;
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
                            if ($key === 'fg2_made') {
                                $prefix = 'fg2';
                            } elseif ($key === 'fg3_made') {
                                $prefix = 'fg3';
                            } elseif ($key === 'ft_made') {
                                $prefix = 'ft';
                            }

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
