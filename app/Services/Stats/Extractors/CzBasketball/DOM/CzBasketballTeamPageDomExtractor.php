<?php

namespace App\Services\Stats\Extractors\CzBasketball\DOM;

use App\Services\Stats\DTO\CzBasketball\CzBasketballTeamPageDTO;
use Symfony\Component\DomCrawler\Crawler;

class CzBasketballTeamPageDomExtractor
{
    /**
     * @param string $html
     * @param string|int|null $teamExternalId
     * @param int|null $yYear
     * @return CzBasketballTeamPageDTO
     */
    public function extract(string $html, $teamExternalId = null, ?int $yYear = null): CzBasketballTeamPageDTO
    {
        $crawler = new Crawler($html);
        $warnings = [];

        // 1.1 TEAM HEADER
        $teamHeader = $this->extractHeader($crawler);

        // Season mismatch check (1.4)
        if ($yYear) {
            $seasonText = $yYear . '/' . substr($yYear + 1, 2, 2);
            if (!str_contains($html, $seasonText)) {
                $warnings[] = "Season mismatch: expected {$seasonText} not found in page content.";
            }
        }

        // 1.2 DETEKCE TABULKY HRÁČŮ (ROSTER/STATS)
        $rosterTable = $this->extractRoster($crawler, $warnings);

        // 1.3 DETEKCE TABULKY ZÁPASŮ
        $matchesTable = $this->extractMatches($crawler, $warnings);

        // 1.5 VÝSTUP LINKS
        $links = $this->collectLinks($rosterTable, $matchesTable);

        // 6. VALIDACE
        if (count($rosterTable) < 3) {
            $warnings[] = "Roster table has fewer than 3 rows.";
        }
        $hasPlayerLink = false;
        foreach ($rosterTable as $row) {
            if (!empty($row['player_external_id'])) {
                $hasPlayerLink = true;
                break;
            }
        }
        if (!$hasPlayerLink) {
            $warnings[] = "Roster table contains no player links.";
        }

        if (count($matchesTable) === 0) {
            $warnings[] = "No matches found in matches table.";
        }

        return new CzBasketballTeamPageDTO(
            team_header: $teamHeader,
            roster_table: $rosterTable,
            matches_table: $matchesTable,
            links: $links,
            warnings: $warnings
        );
    }

    protected function extractHeader(Crawler $crawler): array
    {
        $teamName = "";
        $h1 = $crawler->filter('h1')->first();
        if ($h1->count() > 0) {
            $teamName = trim($h1->text());
        }

        $clubName = "";
        $clubLabel = $crawler->filterXPath("//*[normalize-space(.)='Klub']");
        if ($clubLabel->count() > 0) {
            $clubValue = $clubLabel->filterXPath("following::*[1]");
            if ($clubValue->count() > 0) {
                $clubName = trim($clubValue->text());
            }
        }

        $category = "";
        $catLabel = $crawler->filterXPath("//*[normalize-space(.)='Kategorie']");
        if ($catLabel->count() > 0) {
            $catValue = $catLabel->filterXPath("following::*[1]");
            if ($catValue->count() > 0) {
                $category = trim($catValue->text());
            }
        }

        $competition = "";
        $compLabel = $crawler->filterXPath("//*[normalize-space(.)='Soutěž']");
        if ($compLabel->count() > 0) {
            $compValue = $compLabel->filterXPath("following::*[1]");
            if ($compValue->count() > 0) {
                $competition = trim($compValue->text());
            }
        }

        return [
            'team_name' => $teamName,
            'club_name' => $clubName,
            'category' => $category,
            'competition' => $competition,
        ];
    }

    protected function extractRoster(Crawler $crawler, array &$warnings): array
    {
        // XPath from prompt
        $xpath = "//table[.//tr[1]//*[self::th or self::td][contains(normalize-space(.),'Hráč')]
                  and .//tr[1]//*[contains(normalize-space(.),'Rok narození')]
                  and .//tr[1]//*[normalize-space(.)='Z' or contains(normalize-space(.),' Z ')]
                  and .//tr[1]//*[contains(normalize-space(.),'Min')]
                  and .//tr[1]//*[normalize-space(.)='B' or contains(normalize-space(.),' B ')]
                  and .//tr[1]//*[contains(normalize-space(.),'TH %')]
                ]";

        $tables = $crawler->filterXPath($xpath);

        // Final check: count player links
        $targetTable = null;
        foreach ($tables as $node) {
            $t = new Crawler($node);
            if ($t->filter('a[href*="/hrac/"]')->count() >= 3) {
                $targetTable = $t;
                break;
            }
        }

        if (!$targetTable) {
            return [];
        }

        $headers = $this->getTableHeaders($targetTable);
        $rows = [];

        $targetTable->filter('tbody tr')->each(function (Crawler $tr) use (&$rows, $headers) {
            $rowData = [];
            $cells = $tr->filter('td');

            if ($cells->count() === 0) return;

            $playerLink = $tr->filter('a[href*="/hrac/"]')->first();
            if ($playerLink->count() > 0) {
                $href = $playerLink->attr('href');
                if (preg_match('/\/hrac\/(\d+)/', $href, $matches)) {
                    $rowData['player_external_id'] = $matches[1];
                }
                $rowData['player_name'] = trim($playerLink->text());
            }

            foreach ($headers as $index => $label) {
                $cell = $cells->eq($index);
                if ($cell->count() === 0) continue;
                $val = trim($cell->text());

                if (str_contains($label, 'Pozice')) $rowData['position'] = $val;
                if (str_contains($label, 'Rok narození')) $rowData['birth_year'] = $val;
                if (str_contains($label, 'Výška')) $rowData['height'] = $val;
                if (str_contains($label, 'Věk')) $rowData['age'] = $val;
                if ($label === 'Z' || $label === ' Z ') $rowData['gp'] = $val;
                if (str_contains($label, 'Min')) $rowData['minutes'] = $val;
                if ($label === 'B' || $label === ' B ') $rowData['points'] = $val;
                if ($label === '2B') $rowData['fg2'] = $val;
                if ($label === '3B') $rowData['fg3'] = $val;
                if ($label === 'TH %') $rowData['ft_pct'] = $val;
                if ($label === 'F-') $rowData['fouls'] = $val;

                if ($label === 'TH') {
                    if (str_contains($val, '/')) {
                        $parts = explode('/', $val);
                        $rowData['ft_made_pg'] = trim($parts[0]);
                        $rowData['ft_att_pg'] = trim($parts[1]);
                    } else {
                        $rowData['ft_made_pg'] = '0';
                        $rowData['ft_att_pg'] = '0';
                    }
                }
            }
            $rows[] = $rowData;
        });

        return $rows;
    }

    protected function extractMatches(Crawler $crawler, array &$warnings): array
    {
        $xpath = "//table[
            .//tr[1]//*[contains(normalize-space(.),'Číslo utkání')]
            and .//tr[1]//*[contains(normalize-space(.),'Domácí/hosté')]
            and .//tr[1]//*[contains(normalize-space(.),'Datum')]
            and .//tr[1]//*[contains(normalize-space(.),'Soupeř')]
            and .//tr[1]//*[contains(normalize-space(.),'Skóre')]
            and .//tr[1]//*[contains(normalize-space(.),'TH %')]
        ]";

        $tables = $crawler->filterXPath($xpath);
        $targetTable = null;
        foreach ($tables as $node) {
            $t = new Crawler($node);
            if ($t->filter('a[href*="/zapas/"]')->count() >= 1) {
                $targetTable = $t;
                break;
            }
        }

        if (!$targetTable) {
            return [];
        }

        $headers = $this->getTableHeaders($targetTable);
        $rows = [];

        $targetTable->filter('tbody tr')->each(function (Crawler $tr) use (&$rows, $headers) {
            $rowData = [];
            $cells = $tr->filter('td');
            if ($cells->count() === 0) return;

            $matchLink = $tr->filter('a[href*="/zapas/"]')->first();
            if ($matchLink->count() > 0) {
                $href = $matchLink->attr('href');
                if (preg_match('/\/zapas\/(\d+)/', $href, $matches)) {
                    $rowData['match_external_id'] = $matches[1];
                }
            }

            foreach ($headers as $index => $label) {
                $cell = $cells->eq($index);
                if ($cell->count() === 0) continue;
                $val = trim($cell->text());

                if (str_contains($label, 'Číslo utkání')) {
                    $rowData['match_number'] = $val;
                }
                if (str_contains($label, 'Soutěž')) $rowData['competition'] = $val;
                if (str_contains($label, 'Domácí/hosté')) $rowData['home_away'] = $val;
                if (str_contains($label, 'Datum')) $rowData['date'] = $val;
                if (str_contains($label, 'Soupeř')) {
                    $rowData['opponent_name'] = $val;
                    $oppLink = $cell->filter('a[href*="/tym/"]')->first();
                    if ($oppLink->count() > 0) {
                        if (preg_match('/\/tym\/(\d+)/', $oppLink->attr('href'), $m)) {
                            $rowData['opponent_external_id'] = $m[1];
                        }
                    }
                }
                if (str_contains($label, 'Skóre')) {
                    $rowData['score_raw'] = $val;
                    if (str_contains($val, ':')) {
                        $parts = explode(':', $val);
                        $s1 = trim($parts[0]);
                        $s2 = trim($parts[1]);
                        if (is_numeric($s1) && is_numeric($s2)) {
                            $rowData['score_home'] = $s1;
                            $rowData['score_away'] = $s2;
                            $rowData['status'] = 'completed';
                        } else {
                            $rowData['status'] = 'planned';
                        }
                    } else {
                        $rowData['status'] = 'planned';
                    }
                }
                if ($label === '2B') $rowData['fg2_made_team'] = $val;
                if ($label === '3B') $rowData['fg3_made_team'] = $val;
                if ($label === 'TH') {
                    $rowData['ft_raw'] = $val;
                    if (str_contains($val, '/')) {
                        $parts = explode('/', $val);
                        $rowData['ft_made'] = trim($parts[0]);
                        $rowData['ft_att'] = trim($parts[1]);
                    }
                }
                if (str_contains($label, 'TH %')) $rowData['ft_pct'] = $val;
            }
            $rows[] = $rowData;
        });

        return $rows;
    }

    protected function getTableHeaders(Crawler $table): array
    {
        $headers = [];
        $headerRow = $table->filter('tr')->first();
        if ($headerRow->count() > 0) {
            $headerRow->filter('th, td')->each(function (Crawler $node) use (&$headers) {
                $headers[] = trim($node->text());
            });
        }
        return $headers;
    }

    protected function collectLinks(array $roster, array $matches): array
    {
        $playerIds = [];
        foreach ($roster as $row) {
            if (!empty($row['player_external_id'])) {
                $playerIds[] = $row['player_external_id'];
            }
        }

        $matchIds = [];
        foreach ($matches as $row) {
            if (!empty($row['match_external_id'])) {
                $matchIds[] = $row['match_external_id'];
            }
        }

        $opponentTeamIds = [];
        foreach ($matches as $row) {
            if (!empty($row['opponent_external_id'])) {
                $opponentTeamIds[] = $row['opponent_external_id'];
            }
        }

        return [
            'player_external_ids' => array_values(array_unique($playerIds)),
            'match_external_ids' => array_values(array_unique($matchIds)),
            'opponent_team_external_ids' => array_values(array_unique($opponentTeamIds)),
        ];
    }
}
