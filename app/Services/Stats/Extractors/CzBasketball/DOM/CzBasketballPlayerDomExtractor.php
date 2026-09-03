<?php

namespace App\Services\Stats\Extractors\CzBasketball\DOM;

use Symfony\Component\DomCrawler\Crawler;

class CzBasketballPlayerDomExtractor
{
    public function extract(string $html): array
    {
        $crawler = new Crawler($html);

        return [
            'career_table' => $this->extractCareer($crawler),
            'per_game_list' => $this->extractPerGame($crawler),
            'opponent_summary' => $this->extractOpponentSummary($crawler),
            'current_club' => $this->extractCurrentClub($crawler),
        ];
    }

    protected function extractCareer(Crawler $crawler): array
    {
        // 4.1 CAREER TABLE: “Sezona” AND “Tým” AND “Zápasy” AND “B” AND “TH %”
        $xpath = "//table[.//tr[1]//*[contains(normalize-space(.), 'Sezona')]
                  and .//tr[1]//*[contains(normalize-space(.), 'Tým')]
                  and .//tr[1]//*[contains(normalize-space(.), 'Zápasy')]
                  and .//tr[1]//*[contains(normalize-space(.), 'B')]
                  and .//tr[1]//*[contains(normalize-space(.), 'TH %')]
                ]";

        $table = $crawler->filterXPath($xpath);
        if ($table->count() === 0) {
            return [];
        }

        $headers = $this->getTableHeaders($table);
        $rows = [];

        $table->filter('tbody tr')->each(function (Crawler $tr) use (&$rows, $headers) {
            $rowData = [];
            $cells = $tr->filter('td');
            if ($cells->count() === 0) {
                return;
            }

            foreach ($headers as $index => $label) {
                $cell = $cells->eq($index);
                if ($cell->count() === 0) {
                    continue;
                }
                $val = trim($cell->text());

                if (str_contains($label, 'Sezona')) {
                    $rowData['season_label'] = $val;
                }
                if (str_contains($label, 'Tým')) {
                    $rowData['team_name'] = $val;
                }
                if (str_contains($label, 'Zápasy')) {
                    $rowData['gp'] = $val;
                }
                if ($label === 'B') {
                    $rowData['pts_pg'] = $val;
                }
                if ($label === '2B') {
                    $rowData['fg2_pg'] = $val;
                }
                if ($label === '3B') {
                    $rowData['fg3_pg'] = $val;
                }
                if ($label === 'TH') {
                    $rowData['ft_pg_raw'] = $val;
                    if (str_contains($val, '/')) {
                        $parts = explode('/', $val);
                        $rowData['ft_made_pg'] = trim($parts[0]);
                        $rowData['ft_att_pg'] = trim($parts[1]);
                    }
                }
                if (str_contains($label, 'TH %')) {
                    $rowData['ft_pct'] = $val;
                }
                if (str_contains($label, 'F-')) {
                    $rowData['fouls_pg'] = $val;
                }
            }
            $rows[] = $rowData;
        });

        return $rows;
    }

    protected function extractPerGame(Crawler $crawler): array
    {
        // 4.2 PER-GAME LIST: “Datum” AND “Fáze sezóny” AND “Soupeř” AND “B” AND “TH %”
        $xpath = "//table[.//tr[1]//*[contains(normalize-space(.), 'Datum')]
                  and .//tr[1]//*[contains(normalize-space(.), 'Fáze sezóny')]
                  and .//tr[1]//*[contains(normalize-space(.), 'Soupeř')]
                  and .//tr[1]//*[contains(normalize-space(.), 'B')]
                  and .//tr[1]//*[contains(normalize-space(.), 'TH %')]
                ]";

        $table = $crawler->filterXPath($xpath);
        if ($table->count() === 0) {
            return [];
        }

        $headers = $this->getTableHeaders($table);
        $rows = [];

        $table->filter('tbody tr')->each(function (Crawler $tr) use (&$rows, $headers) {
            $rowData = [];
            $cells = $tr->filter('td');
            if ($cells->count() === 0) {
                return;
            }

            foreach ($headers as $index => $label) {
                $cell = $cells->eq($index);
                if ($cell->count() === 0) {
                    continue;
                }
                $val = trim($cell->text());

                if (str_contains($label, 'Datum')) {
                    $rowData['date'] = $val;
                }
                if (str_contains($label, 'Fáze sezóny')) {
                    $rowData['phase'] = $val;
                }
                if (str_contains($label, 'Soupeř')) {
                    $rowData['opponent_name'] = $val;
                }
                if ($label === 'B') {
                    $rowData['pts'] = $val;
                }
                if ($label === '2B') {
                    $rowData['fg2_pts'] = $val;
                }
                if ($label === '3B') {
                    $rowData['fg3_pts'] = $val;
                }
                if ($label === 'TH') {
                    $rowData['ft_raw'] = $val;
                    if (str_contains($val, '/')) {
                        $parts = explode('/', $val);
                        $rowData['ft_made'] = trim($parts[0]);
                        $rowData['ft_att'] = trim($parts[1]);
                    }
                }
                if (str_contains($label, 'TH %')) {
                    $rowData['ft_pct'] = $val;
                }
            }
            $rows[] = $rowData;
        });

        return $rows;
    }

    protected function extractOpponentSummary(Crawler $crawler): array
    {
        // 4.3 OPPONENT SUMMARY TABLE: “Soupeř” AND “Z” AND “B”
        $xpath = "//table[.//tr[1]//*[contains(normalize-space(.), 'Soupeř')]
                  and .//tr[1]//*[normalize-space(.)='Z']
                  and .//tr[1]//*[normalize-space(.)='B']
                ]";

        $table = $crawler->filterXPath($xpath);
        if ($table->count() === 0) {
            return [];
        }

        $headers = $this->getTableHeaders($table);
        $rows = [];

        $table->filter('tbody tr')->each(function (Crawler $tr) use (&$rows, $headers) {
            $rowData = [];
            $cells = $tr->filter('td');
            if ($cells->count() === 0) {
                return;
            }

            foreach ($headers as $index => $label) {
                $cell = $cells->eq($index);
                if ($cell->count() === 0) {
                    continue;
                }
                $val = trim($cell->text());

                if (str_contains($label, 'Soupeř')) {
                    $rowData['opponent_name'] = $val;
                }
                if ($label === 'Z') {
                    $rowData['gp'] = $val;
                }
                if ($label === 'B') {
                    $rowData['pts_pg'] = $val;
                }
                if ($label === '2B') {
                    $rowData['fg2_pg'] = $val;
                }
                if ($label === '3B') {
                    $rowData['fg3_pg'] = $val;
                }
                if ($label === 'TH') {
                    $rowData['ft_pg_raw'] = $val;
                    if (str_contains($val, '/')) {
                        $parts = explode('/', $val);
                        $rowData['ft_made_pg'] = trim($parts[0]);
                        $rowData['ft_att_pg'] = trim($parts[1]);
                    }
                }
                if (str_contains($label, 'TH %')) {
                    $rowData['ft_pct'] = $val;
                }
                if (str_contains($label, 'F-')) {
                    $rowData['fouls_pg'] = $val;
                }
            }
            $rows[] = $rowData;
        });

        return $rows;
    }

    protected function extractCurrentClub(Crawler $crawler): array
    {
        $club = '';
        $label = $crawler->filterXPath("//span[contains(normalize-space(.), 'Aktuální klub')]");
        if ($label->count() > 0) {
            // Pokud je to span s textem "Aktuální klub:", zkusíme vzít dalšího sourozence
            $next = $label->nextAll()->filter('span')->first();
            if ($next->count() > 0) {
                $club = trim($next->text());
                if (! empty($club)) {
                    return ['club_name' => $club];
                }
            }
        }

        // Pokud stále nic, zkusíme najít jakýkoli element, který začíná "Aktuální klub:"
        $crawler->filter('span, div, p')->each(function (Crawler $node) use (&$club) {
            $t = trim($node->text());
            // Použijeme regex pro přesnější shodu
            if (preg_match('/Aktuální klub:\s*(.+)/u', $t, $m) && empty($club)) {
                $candidate = trim($m[1]);
                if (! empty($candidate) && strlen($candidate) < 100) {
                    $club = $candidate;
                }
            }
        });

        return ['club_name' => $club];
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
}
