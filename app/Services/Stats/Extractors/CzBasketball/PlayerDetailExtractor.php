<?php

namespace App\Services\Stats\Extractors\CzBasketball;

use App\Services\Stats\Contracts\StatExtractorInterface;
use Symfony\Component\DomCrawler\Crawler;

class PlayerDetailExtractor implements StatExtractorInterface
{
    /**
     * Extrahuje detail hráče z HTML stránky.
     */
    public function extract(string $content, array $config = []): array
    {
        $crawler = new Crawler($content);
        $warnings = [];

        // Jméno hráče z h1
        $name = trim($crawler->filter('h1')->first()->text() ?? '');

        // Fotografie hráče
        $photoImg = $crawler->filter('img.rounded.img-fluid')->first();
        $photoUrl = null;
        if ($photoImg->count() > 0) {
            $photoUrl = $photoImg->attr('src');
            if ($photoUrl && !str_starts_with($photoUrl, 'http')) {
                $photoUrl = 'https://cz.basketball' . $photoUrl;
            }
        }

        // Osobní údaje
        $details = [];
        $crawler->filter('.font-weight-bold.mb-1')->each(function (Crawler $node) use (&$details) {
            $label = trim($node->text());
            $valueNode = $node->nextAll()->first();
            if ($valueNode->count() > 0) {
                $value = trim($valueNode->text());
                $details[$label] = $value;
            }
        });

        // Statistiky ze záložek a kariéry
        $stats = $this->extractAllStats($crawler);

        // Zápasy ze záložky Zápasy
        $matches = $this->extractMatches($crawler);

        // Rekordy ze záložky Křížové
        $records = $this->extractRecords($crawler);

        // Extrakce dostupných sezón z tabulky kariéry
        $seasons = [];
        foreach ($stats as $stat) {
            if (!empty($stat['season_label']) && !($stat['is_career_total'] ?? false) && !in_array($stat['season_label'], $seasons)) {
                $seasons[] = $stat['season_label'];
            }
        }

        $data = [
            'name' => $name,
            'photo_url' => $photoUrl,
            'birth_year' => $this->extractBirthYear($details['Ročník narození'] ?? $details['Datum narození'] ?? null),
            'height' => $this->extractInt($details['Výška'] ?? null),
            'position' => $details['Pozice'] ?? null,
            'current_club' => $details['Aktuální klub'] ?? null,
            'stats' => $stats,
            'matches' => $matches,
            'records' => $records,
            'available_seasons' => $seasons,
        ];

        return [
            'data' => $data,
            'warnings' => $warnings,
        ];
    }

    protected function extractBirthYear(?string $text): ?int
    {
        if (!$text) return null;
        if (preg_match('/(\d{4})/', $text, $matches)) {
            return (int) $matches[1];
        }
        return null;
    }

    protected function extractInt(?string $text): ?int
    {
        if ($text === null || $text === '' || $text === '-') return null;
        if (preg_match('/(\d+)/', $text, $matches)) {
            return (int) $matches[1];
        }
        return null;
    }

    protected function extractFloat(?string $text): ?float
    {
        if ($text === null || $text === '' || $text === '-') return null;
        $text = str_replace(',', '.', $text);
        if (preg_match('/(\d+(\.\d+)?)/', $text, $matches)) {
            return (float) $matches[1];
        }
        return null;
    }

    protected function extractAllStats(Crawler $crawler): array
    {
        $allStats = [];

        // 1. Sekce Kariéra (obvykle nejpodrobnější historická data)
        // Zkusíme najít tabulku, která následuje po nadpisu Kariéra
        $careerHeading = $crawler->filterXPath("//h3[contains(., 'Kariéra')]");
        if ($careerHeading->count() > 0) {
            $careerTable = $careerHeading->nextAll()->filter('table')->first();
            if ($careerTable->count() > 0) {
                $allStats = array_merge($allStats, $this->parseTable($careerTable));
            }
        }

        // 2. Záložka Statistiky (může obsahovat aktuální sezónu podrobněji)
        // Selektor #tab-pane-one je v HTML přítomen, ale může mít div s role="tab-pane"
        $statsTab = $crawler->filter('#tab-pane-one table, [id="tab-pane-one"] table');
        if ($statsTab->count() > 0) {
            $statsTab->each(function (Crawler $table) use (&$allStats) {
                $allStats = array_merge($allStats, $this->parseTable($table));
            });
        }

        // Odstranění duplicit na základě kombinace sezóna, soutěž, tým
        $uniqueStats = [];
        foreach ($allStats as $stat) {
            $key = ($stat['season_label'] ?? '') . '|' . ($stat['competition_label'] ?? '') . '|' . ($stat['team_name'] ?? '') . '|' . ($stat['is_career_total'] ? '1' : '0');
            $uniqueStats[$key] = $stat;
        }

        return array_values($uniqueStats);
    }

    protected function extractMatches(Crawler $crawler): array
    {
        $matches = [];
        $table = $crawler->filter('#tab-pane-two table, [id="tab-pane-two"] table')->first();

        if ($table->count() === 0) {
            return [];
        }

        $headers = [];
        $columnMap = [
            'Datum' => 'match_date',
            'Soutěž' => 'competition_label',
            'Soupeř' => 'opponent_name',
            'B' => 'points',
            '2B' => 'two_points',
            '3B' => 'three_points',
            'TH' => 'free_throws',
            'TH%' => 'free_throws_pct',
            'F-' => 'fouls',
            'Min' => 'minutes',
            'V' => 'valuation',
        ];

        $table->filter('thead th')->each(function (Crawler $th, $i) use (&$headers, $columnMap) {
            $text = trim($th->text());
            $abbr = $th->filter('abbr');
            if ($abbr->count() > 0) {
                $text = $abbr->attr('title') ?: $text;
            }

            $mapped = null;
            foreach ($columnMap as $key => $val) {
                if ($text === $key || mb_stripos($text, $key) !== false) {
                    $mapped = $val;
                    break;
                }
            }
            $headers[$i] = $mapped ?: $text;
        });

        $table->filter('tbody tr')->each(function (Crawler $tr) use (&$matches, $headers) {
            $match = [];

            $tr->filter('td')->each(function (Crawler $td, $i) use (&$match, $headers) {
                $header = $headers[$i] ?? null;
                if (!$header) return;

                $val = trim($td->text());

                if ($header === 'match_date') {
                    // "26. 11. 2025" -> "2025-11-26"
                    if (preg_match('/(\d{1,2})\.\s*(\d{1,2})\.\s*(\d{4})/', $val, $m)) {
                        $match['match_date'] = sprintf('%04d-%02d-%02d', $m[3], $m[2], $m[1]);
                    }
                } elseif ($header === 'opponent_name') {
                    $match['opponent_name'] = $val;
                    $link = $td->filter('a');
                    if ($link->count() > 0) {
                        $href = $link->attr('href');
                        if (preg_match('/zapas\/(\d+)/', $href, $m)) {
                            $match['external_match_id'] = $m[1];
                        }
                    }
                } elseif (in_array($header, ['two_points', 'three_points', 'free_throws'])) {
                    if (str_contains($val, '/')) {
                        $parts = explode('/', $val);
                        $match[$header . '_made'] = $this->extractInt($parts[0]);
                        $match[$header . '_attempts'] = $this->extractInt($parts[1]);
                    } else {
                        $match[$header . '_made'] = $this->extractInt($val);
                    }
                } elseif ($header === 'free_throws_pct') {
                    $match['free_throws_pct'] = $this->extractFloat($val);
                } elseif ($header === 'competition_label') {
                    $match['competition_label'] = $val;
                } else {
                    // points, fouls, minutes, valuation
                    if (in_array($header, ['points', 'fouls', 'minutes', 'valuation'])) {
                        $match[$header] = $this->extractInt($val);
                    }
                }
            });

            if (!empty($match['match_date'])) {
                $matches[] = $match;
            }
        });

        return $matches;
    }

    protected function parseTable(Crawler $table): array
    {
        $rows = [];
        $headers = [];

        // Mapování zkratek/názvů na sloupce v DB (od nejspecifičtějších po nejobecnější)
        // Klíče jsou ty, které očekáváme v textu nebo v title u abbr
        $columnMap = [
            'Sezona' => 'season_full',
            'Tým' => 'team_name',
            'Zápasy' => 'games_played',
            'Minuty' => 'minutes_avg',
            'TH %' => 'free_throws_pct',
            'TH%' => 'free_throws_pct',
            '% úspěšnosti TH' => 'free_throws_pct',
            '2B' => 'two_points',
            '3B' => 'three_points',
            'TH' => 'free_throws',
            'Doskoky útočné' => 'rebounds_offensive_avg',
            'Doskoky obranné' => 'rebounds_defensive_avg',
            'Doskoky celkem' => 'rebounds_total_avg',
            'Asistence' => 'assists_avg',
            'Zisky' => 'steals_avg',
            'Ztráty' => 'turnovers_avg',
            'Bloky' => 'blocks_avg',
            'Chyby celkem' => 'fouls_avg',
            'F-' => 'fouls_avg',
            'F+' => 'fouls_received_avg',
            'Užitečnost' => 'valuation_avg',
            'V' => 'valuation_avg',
            'Plus/minus' => 'plus_minus_avg',
            '+/-' => 'plus_minus_avg',
            'Body' => 'points_avg',
            'B' => 'points_avg',
        ];

        // Zjistíme hlavičky
        $table->filter('thead th')->each(function (Crawler $th, $i) use (&$headers, $columnMap) {
            $text = trim($th->text());
            $abbr = $th->filter('abbr');
            $abbrTitle = null;
            if ($abbr->count() > 0) {
                $abbrTitle = trim($abbr->attr('title') ?: '');
            }

            // Mapování na naše klíče
            $mapped = null;

            // 1. Zkusíme match na title u abbr
            if ($abbrTitle) {
                foreach ($columnMap as $key => $val) {
                    if (mb_stripos($abbrTitle, $key) !== false) {
                        $mapped = $val;
                        break;
                    }
                }
            }

            // 2. Pokud neuspěje, zkusíme match na text
            if (!$mapped) {
                foreach ($columnMap as $key => $val) {
                    if ($text === $key || mb_stripos($text, $key) !== false) {
                        $mapped = $val;
                        break;
                    }
                }
            }

            $headers[$i] = $mapped ?: $text;
        });

        // Ověříme, jestli je to tabulka statistik (musí mít aspoň sezónu nebo tým)
        $hasRequiredHeaders = in_array('season_full', $headers) || in_array('team_name', $headers);
        if (!$hasRequiredHeaders) {
            return [];
        }

        // Parsování řádků
        $table->filter('tbody tr')->each(function (Crawler $tr) use (&$rows, $headers) {
            $row = [
                'is_career_total' => false,
            ];

            $tr->filter('td')->each(function (Crawler $td, $i) use (&$row, $headers) {
                $header = $headers[$i] ?? null;
                if (!$header) return;

                $val = trim($td->text());

                if ($header === 'season_full') {
                    if (mb_stripos($val, 'Celkem') !== false || mb_stripos($val, 'Total') !== false || mb_stripos($val, 'Průměr') !== false) {
                        $row['is_career_total'] = true;
                    }
                    // "2010/11 muži - Základní fáze" -> rozdělit
                    if (preg_match('/^(\d{4}\/\d{2})\s*(.*)$/', $val, $m)) {
                        $row['season_label'] = $m[1];
                        $row['competition_label'] = trim($m[2]);
                    } else {
                        $row['season_label'] = $val;
                    }
                } elseif ($header === 'team_name') {
                    if (mb_stripos($val, 'Celkem') !== false || mb_stripos($val, 'Total') !== false || mb_stripos($val, 'Průměr') !== false) {
                        $row['is_career_total'] = true;
                    }
                    $row['team_name'] = $val;
                } elseif ($header === 'games_played') {
                    $extracted = $this->extractInt($val);
                    $row['games_played'] = $extracted ?? 0; // Default na 0, pokud je to např. pomlčka v řádku s průměry
                } elseif (in_array($header, ['two_points', 'three_points', 'free_throws'])) {
                    // Může být "2.1/3.1" nebo jen "12.5"
                    if (str_contains($val, '/')) {
                        $parts = explode('/', $val);
                        $row[$header . '_made_avg'] = $this->extractFloat($parts[0]);
                        $row[$header . '_attempts_avg'] = $this->extractFloat($parts[1]);
                    } else {
                        $row[$header . '_made_avg'] = $this->extractFloat($val);
                    }
                } else {
                    // Ostatní numerické hodnoty (včetně points_avg, free_throws_pct atd.)
                    if (str_ends_with($header, '_avg') || str_ends_with($header, '_pct')) {
                        $row[$header] = $this->extractFloat($val);
                    }
                }
            });

            if (!empty($row['team_name']) || !empty($row['season_label'])) {
                $rows[] = $row;
            }
        });

        return $rows;
    }

    protected function extractRecords(Crawler $crawler): array
    {
        $records = [];
        // Tabulka rekordů je obvykle v záložce Křížové (#tab-pane-three) v tabulce rekordů
        $recordsTable = null;
        $crawler->filter('#tab-pane-three table')->each(function (Crawler $table) use (&$recordsTable) {
            $header = trim($table->filter('thead th')->first()->text());
            if (mb_stripos($header, 'Kategorie') !== false || mb_stripos($header, 'Rekord') !== false) {
                $recordsTable = $table;
            }
        });

        if (!$recordsTable || $recordsTable->count() === 0) {
            return [];
        }

        $recordsTable->filter('tbody tr')->each(function (Crawler $tr) use (&$records) {
            $cells = $tr->filter('td');
            if ($cells->count() >= 5) {
                $records[] = [
                    'label' => trim($cells->eq(0)->text()),
                    'value' => trim($cells->eq(1)->text()),
                    'opponent' => trim($cells->eq(2)->text()),
                    'date' => trim($cells->eq(3)->text()),
                    'season' => trim($cells->eq(4)->text()),
                ];
            }
        });

        return $records;
    }
}
