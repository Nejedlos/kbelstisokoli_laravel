<?php

namespace App\Services\Stats\Clippers\CzBasketball;

use App\Services\Stats\Contracts\ClipperInterface;
use App\Services\Stats\DTO\ClipDTO;
use Symfony\Component\DomCrawler\Crawler;

class CzBasketballTeamPageClipper implements ClipperInterface
{
    /**
     * Vyřízne relevantní fragmenty z týmové stránky cz.basketball a vytvoří CNH.
     */
    public function clip(string $html, ?string $baseUrl = 'https://cz.basketball'): array
    {
        $crawler = new Crawler($html);
        $clips = [];

        // 1. TEAM HEADER CLIP
        $header = $this->extractTeamHeader($crawler, $baseUrl);
        if ($header) $clips[] = $header;

        // 2. ROSTER TABLE CLIP
        $roster = $this->extractRosterTable($crawler, $baseUrl);
        if ($roster) $clips[] = $roster;

        // 3. TEAM MATCHES CLIPS
        $matches = $this->extractMatchesTable($crawler, $baseUrl);
        if ($matches) $clips[] = $matches;

        // 4. HISTORY TABLE CLIP
        $history = $this->extractHistoryTable($crawler, $baseUrl);
        if ($history) $clips[] = $history;

        // 5. EXTRA STATS TABLES
        $otherTables = $this->extractExtraStatsTables($crawler, $baseUrl);
        $clips = array_merge($clips, $otherTables);

        return array_values(array_filter($clips));
    }

    protected function extractTeamHeader(Crawler $crawler, ?string $baseUrl): ?ClipDTO
    {
        // Najdeme hlavní h1 (název týmu)
        $h1 = $crawler->filter('h1')->first();
        if ($h1->count() === 0) {
            return null;
        }

        // Hledáme blok s "Klub", "Kategorie", "Soutěž"
        $infoBlocks = $crawler->filter('div, section, p')->reduce(function (Crawler $node) {
            $text = $node->text();
            return str_contains($text, 'Klub') || str_contains($text, 'Kategorie') || str_contains($text, 'Soutěž');
        })->first();

        $headerHtml = '<section id="team-header">';
        $headerHtml .= '<h1>' . $h1->text() . '</h1>';
        if ($infoBlocks->count() > 0) {
            $headerHtml .= $this->sanitizeFragment($infoBlocks, $baseUrl);
        }
        $headerHtml .= '</section>';

        return new ClipDTO(
            id: 'team_header',
            htmlFragment: $headerHtml,
            textHint: 'Team name and competition info',
            evidence: ['team_name' => trim($h1->text())]
        );
    }

    protected function extractRosterTable(Crawler $crawler, ?string $baseUrl): ?ClipDTO
    {
        // ROSTER SIGNATURE: <th>Hráč</th> AND <th>Rok narození</th> AND <th>Min.</th> AND <th>TH %</th>
        // AND >= 3 links /hrac/
        $rosterTable = $crawler->filter('table')->reduce(function (Crawler $node) {
            $html = $node->html();
            $hasHeader = str_contains($html, 'Hráč') && str_contains($html, 'Rok narození') &&
                         str_contains($html, 'Min.') && str_contains($html, 'TH %');
            $playerLinks = $node->filter('a[href*="/hrac/"]')->count();
            return $hasHeader && $playerLinks >= 3;
        })->first();

        if ($rosterTable->count() === 0) {
            return null;
        }

        $links = $this->extractLinks($rosterTable, $baseUrl, 'players');

        return new ClipDTO(
            id: 'roster_table',
            htmlFragment: $this->sanitizeFragment($rosterTable, $baseUrl),
            textHint: 'Seasonal roster table',
            links: $links,
            evidence: [
                'row_count' => $rosterTable->filter('tr')->count(),
                'player_count' => count($links)
            ]
        );
    }

    protected function extractMatchesTable(Crawler $crawler, ?string $baseUrl): ?ClipDTO
    {
        // MATCHES SIGNATURE (Table A): <th>Číslo utkání</th> AND <th>Datum</th> AND <th>Soupeř</th> AND <th>Skóre</th> AND <th>TH %</th>
        // AND >= 1 link /zapas/
        $matchesTable = $crawler->filter('table')->reduce(function (Crawler $node) {
            $html = $node->html();
            $hasHeader = str_contains($html, 'Číslo utkání') && str_contains($html, 'Datum') &&
                         str_contains($html, 'Soupeř') && str_contains($html, 'Skóre') && str_contains($html, 'TH %');
            $matchLinks = $node->filter('a[href*="/zapas/"]')->count();
            return $hasHeader && $matchLinks >= 1;
        })->first();

        if ($matchesTable->count() === 0) {
            return null;
        }

        $links = array_merge(
            $this->extractLinks($matchesTable, $baseUrl, 'matches'),
            $this->extractLinks($matchesTable, $baseUrl, 'opponent_teams')
        );

        return new ClipDTO(
            id: 'matches_table',
            htmlFragment: $this->sanitizeFragment($matchesTable, $baseUrl),
            textHint: 'Primary seasonal matches table',
            links: $links,
            evidence: [
                'row_count' => $matchesTable->filter('tr')->count(),
                'match_count' => $matchesTable->filter('a[href*="/zapas/"]')->count()
            ]
        );
    }

    protected function extractHistoryTable(Crawler $crawler, ?string $baseUrl): ?ClipDTO
    {
        // HISTORIE SIGNATURE: <th>Sezóna</th> AND <th>Soutěž</th> AND <th>Umístění</th> AND <th>Počet bodů</th>
        $historyTable = $crawler->filter('table')->reduce(function (Crawler $node) {
            $html = $node->html();
            return str_contains($html, 'Sezóna') && str_contains($html, 'Soutěž') &&
                   str_contains($html, 'Umístění') && str_contains($html, 'Počet bodů');
        })->first();

        if ($historyTable->count() === 0) {
            return null;
        }

        $links = $this->extractLinks($historyTable, $baseUrl, 'competitions');

        return new ClipDTO(
            id: 'history_table',
            htmlFragment: $this->sanitizeFragment($historyTable, $baseUrl),
            textHint: 'Team history table',
            links: $links,
            evidence: [
                'row_count' => $historyTable->filter('tr')->count()
            ]
        );
    }

    protected function extractExtraStatsTables(Crawler $crawler, ?string $baseUrl): array
    {
        $clips = [];
        $n = 1;

        //statistiky jsou obvykle v tab-pane-two
        $statsPane = $crawler->filter('#tab-pane-two');
        $context = $statsPane->count() > 0 ? $statsPane : $crawler;

        $context->filter('table')->each(function (Crawler $table) use (&$clips, &$n, $baseUrl) {
            // Nesmí to být tabulky, které jsme už vybrali (roster, matches, history)
            $html = $table->html();
            if (str_contains($html, 'Hráč') && str_contains($html, 'Rok narození')) return;
            if (str_contains($html, 'Číslo utkání') && str_contains($html, 'Datum')) return;
            if (str_contains($html, 'Sezóna') && str_contains($html, 'Soutěž')) return;

            // Musí mít aspoň 5 řádků
            if ($table->filter('tr')->count() < 5) return;

            $clips[] = new ClipDTO(
                id: "extra_stats_table_{$n}",
                htmlFragment: $this->sanitizeFragment($table, $baseUrl),
                textHint: 'Additional statistics table',
                links: $this->extractLinks($table, $baseUrl),
                evidence: ['row_count' => $table->filter('tr')->count()]
            );
            $n++;
        });

        return $clips;
    }

    protected function sanitizeFragment(Crawler $node, ?string $baseUrl): string
    {
        $html = $node->outerHtml();

        // Absolutizace URL
        if ($baseUrl) {
            $base = 'https://cz.basketball';
            $html = preg_replace_callback('/href="(\/[^"]+)"/', function($m) use ($base) {
                return 'href="' . $base . $m[1] . '"';
            }, $html);
        }

        // Agresivní sanitizace (odstranění zbytečných atributů)
        $html = preg_replace_callback('/<([a-z1-6]+)\s+([^>]+)>/i', function ($m) {
            $tag = $m[1];
            $attrs = $m[2];
            // Ponecháme jen href, colspan, rowspan
            preg_match_all('/(href|colspan|rowspan)="([^"]+)"/i', $attrs, $matches, PREG_SET_ORDER);
            $newAttrs = '';
            foreach ($matches as $match) {
                $newAttrs .= ' ' . $match[1] . '="' . $match[2] . '"';
            }
            return "<$tag$newAttrs>";
        }, $html);

        // Odstranění komentářů
        $html = preg_replace('/<!--(.|\s)*?-->/', '', $html);

        return trim($html);
    }

    protected function extractLinks(Crawler $node, ?string $baseUrl, ?string $type = null): array
    {
        $links = [];
        $node->filter('a[href]')->each(function (Crawler $a) use (&$links, $baseUrl, $type) {
            $href = $a->attr('href');
            if (str_starts_with($href, '/')) {
                $href = 'https://cz.basketball' . $href;
            }

            $id = null;
            $matchType = false;
            $extra = [];

            if (preg_match('/\/hrac\/(\d+)/', $href, $m)) {
                $id = $m[1];
                if ($type === null || $type === 'players') $matchType = true;
            } elseif (preg_match('/\/zapas\/(\d+)/', $href, $m)) {
                $id = $m[1];
                if ($type === null || $type === 'matches') {
                    $matchType = true;
                    // Pokus o zisk cisla utkani z prvni bunky radku
                    $row = $a->closest('tr');
                    if ($row && $row->count() > 0) {
                        $firstCell = $row->filter('td')->first();
                        if ($firstCell->count() > 0) {
                            $num = trim(preg_replace('/\s+/', ' ', $firstCell->text()));
                            if ($num !== '') {
                                $extra['number'] = $num;
                            }
                        }
                    }
                }
            } elseif (preg_match('/\/tym\/(\d+)/', $href, $m)) {
                $id = $m[1];
                if ($type === null || $type === 'opponent_teams') $matchType = true;
            } elseif (preg_match('/\/soutez\/(\d+)/', $href, $m)) {
                $id = $m[1];
                if ($type === null || $type === 'competitions') {
                    $matchType = true;
                    $extra['label'] = trim($a->text());
                }
            }

            if ($matchType || $type === null) {
                $entry = [
                    'id' => $id,
                    'url' => $href,
                    'name' => trim($a->text()),
                ];
                if (isset($extra['number'])) {
                    $entry['number'] = $extra['number'];
                }
                if (isset($extra['label'])) {
                    $entry['label'] = $extra['label'];
                }
                $links[] = $entry;
            }
        });

        // Unikátní podle URL
        return array_values(collect($links)->unique('url')->toArray());
    }

    /**
     * Sestaví CNH dokument ze seznamu klipů s limitem velikosti.
     */
    public function buildCnh(array $clips): string
    {
        $sections = [
            'team-header' => collect($clips)->firstWhere('id', 'team_header'),
            'tab-roster' => collect($clips)->firstWhere('id', 'roster_table'),
            'tab-matches' => collect($clips)->firstWhere('id', 'matches_table'),
            'tab-history' => collect($clips)->firstWhere('id', 'history_table'),
            'tab-stats' => collect($clips)->filter(fn($c) => str_starts_with($c->id, 'extra_stats_table_'))->values(),
        ];

        $build = function(array $sectionsToInclude) use ($sections) {
            $html = "<html>\n<body>\n";
            foreach ($sectionsToInclude as $sec) {
                if ($sec === 'tab-stats') {
                    if ($sections['tab-stats']->isEmpty()) continue;
                    $html .= "  <section id=\"tab-stats\">\n";
                    foreach ($sections['tab-stats'] as $clip) {
                        $html .= "    {$clip->htmlFragment}\n";
                    }
                    $html .= "  </section>\n";
                    continue;
                }
                $clip = $sections[$sec] ?? null;
                if ($clip) {
                    $html .= "  <section id=\"{$sec}\">\n";
                    $html .= "    {$clip->htmlFragment}\n";
                    $html .= "  </section>\n";
                }
            }
            $html .= "</body>\n</html>";
            return $html;
        };

        // Základ: header+roster+matches
        $html = $build(['team-header', 'tab-roster', 'tab-matches']);

        // Přidáme stats, pokud se vejdeme do 80 KB
        if (strlen($html) < 80000) {
            $withStats = $build(['team-header', 'tab-roster', 'tab-matches', 'tab-stats']);
            if (strlen($withStats) <= 80000) {
                $html = $withStats;
            }
        }

        // Přidáme history, pokud se vejdeme do 80 KB
        if (strlen($html) < 80000) {
            $withHistory = $build(['team-header', 'tab-roster', 'tab-matches', 'tab-stats', 'tab-history']);
            if (strlen($withHistory) <= 80000) {
                $html = $withHistory;
            }
        }

        return $html;
    }

    /**
     * Vytěží JSON se seznamem všech odkazů z klipů.
     */
    public function buildExtractedLinksJson(array $clips): string
    {
        $result = [
            'players' => [],
            'matches' => [],
            'opponent_teams' => [],
            'competitions' => [],
        ];

        foreach ($clips as $clip) {
            foreach ($clip->links as $link) {
                $url = $link['url'];
                if (str_contains($url, '/hrac/')) $result['players'][] = $link;
                elseif (str_contains($url, '/zapas/')) $result['matches'][] = $link;
                elseif (str_contains($url, '/tym/')) $result['opponent_teams'][] = $link;
                elseif (str_contains($url, '/soutez/')) $result['competitions'][] = $link;
            }
        }

        foreach ($result as $key => $val) {
            $result[$key] = array_values(collect($val)->unique('url')->toArray());
        }

        return json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
