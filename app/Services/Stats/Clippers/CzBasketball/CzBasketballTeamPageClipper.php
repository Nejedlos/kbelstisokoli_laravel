<?php

namespace App\Services\Stats\Clippers\CzBasketball;

use App\Services\Stats\Contracts\ClipperInterface;
use App\Services\Stats\DTO\ClipDTO;
use Symfony\Component\DomCrawler\Crawler;

class CzBasketballTeamPageClipper implements ClipperInterface
{
    /**
     * Vyřízne relevantní fragmenty z týmové stránky cz.basketball.
     */
    public function clip(string $html, ?string $baseUrl = null): array
    {
        $crawler = new Crawler($html);
        $clips = [];

        // 1. TEAM HEADER CLIP
        $clips[] = $this->extractTeamHeader($crawler);

        // 2. ROSTER TABLE CLIP
        $clips[] = $this->extractRosterTable($crawler);

        // 3. TEAM MATCHES CLIPS (na team page)
        $clips[] = $this->extractMatchesPreview($crawler);

        // 4. TEAM STATS / OTHER TABLES
        $otherTables = $this->extractOtherTables($crawler);
        $clips = array_merge($clips, $otherTables);

        return array_filter($clips);
    }

    protected function extractTeamHeader(Crawler $crawler): ?ClipDTO
    {
        // Najdeme hlavní h1 (název týmu) a jeho okolí
        $h1 = $crawler->filter('h1')->first();
        if ($h1->count() === 0) {
            return null;
        }

        // Zkusíme najít kontejner, který obsahuje základní info (často nadřazený div s určitou třídou)
        $headerContainer = $h1->closest('.row') ?? $h1->closest('div');

        if (!$headerContainer) {
            return null;
        }

        $html = $headerContainer->outerHtml();
        // Omezíme velikost, pokud by to bylo moc velké, ale team header bývá malý

        return new ClipDTO(
            id: 'team_header',
            htmlFragment: $html,
            textHint: 'Team name and season header',
            evidence: [
                'team_name_found' => trim($h1->text()),
            ]
        );
    }

    protected function extractRosterTable(Crawler $crawler): ?ClipDTO
    {
        // Heuristika: <table> kde >= 3 řádky obsahují <a href*="/hrac/">
        $rosterTable = $crawler->filter('table')->reduce(function (Crawler $node) {
            $playerLinks = $node->filter('a[href*="/hrac/"]');
            return $playerLinks->count() >= 3;
        })->first();

        if ($rosterTable->count() === 0) {
            // Alternativní heuristika podle textu v hlavičce
            $rosterTable = $crawler->filter('table')->reduce(function (Crawler $node) {
                $text = $node->text();
                return str_contains($text, 'Hráč') || str_contains($text, 'Ročník') || str_contains($text, 'Pozice');
            })->first();
        }

        if ($rosterTable->count() === 0) {
            return null;
        }

        // Zkusíme vzít i nadpis nad tabulkou
        $fragmentHtml = $rosterTable->outerHtml();
        $prev = $rosterTable->previousAll()->first();
        if ($prev && in_array($prev->nodeName(), ['h2', 'h3', 'h4', 'caption'])) {
            $fragmentHtml = $prev->outerHtml() . $fragmentHtml;
        }

        $links = [];
        $rosterTable->filter('a[href*="/hrac/"]')->each(function (Crawler $a) use (&$links) {
            $href = $a->attr('href');
            $links[] = [
                'href' => $href,
                'text' => trim($a->text()),
                'id' => preg_match('/\/hrac\/(\d+)/', $href, $m) ? $m[1] : null
            ];
        });

        return new ClipDTO(
            id: 'roster_table',
            htmlFragment: $fragmentHtml,
            textHint: 'Roster table with player links',
            links: $links,
            evidence: [
                'row_count' => $rosterTable->filter('tr')->count(),
                'player_link_count' => count($links),
            ]
        );
    }

    protected function extractMatchesPreview(Crawler $crawler): ?ClipDTO
    {
        // Heuristika: <table> kde >= 2 řádky obsahují <a href*="/zapas/">
        $matchesTable = $crawler->filter('table')->reduce(function (Crawler $node) {
            $matchLinks = $node->filter('a[href*="/zapas/"]');
            return $matchLinks->count() >= 2;
        })->first();

        if ($matchesTable->count() === 0) {
            return null;
        }

        $fragmentHtml = $matchesTable->outerHtml();
        $prev = $matchesTable->previousAll()->first();
        if ($prev && in_array($prev->nodeName(), ['h2', 'h3', 'h4'])) {
            $fragmentHtml = $prev->outerHtml() . $fragmentHtml;
        }

        $links = [];
        $matchesTable->filter('a[href*="/zapas/"]')->each(function (Crawler $a) use (&$links) {
            $href = $a->attr('href');
            $links[] = [
                'href' => $href,
                'text' => trim($a->text()),
                'id' => preg_match('/\/zapas\/(\d+)/', $href, $m) ? $m[1] : null
            ];
        });

        return new ClipDTO(
            id: 'matches_preview',
            htmlFragment: $fragmentHtml,
            textHint: 'Recent and upcoming matches table',
            links: $links,
            evidence: [
                'row_count' => $matchesTable->filter('tr')->count(),
                'match_link_count' => count($links),
            ]
        );
    }

    protected function extractOtherTables(Crawler $crawler): array
    {
        $clips = [];
        $n = 1;

        $crawler->filter('table')->each(function (Crawler $table) use (&$clips, &$n) {
            $html = $table->outerHtml();
            $hash = hash('sha256', $html);

            // Pokud už tabulku máme v jiném klipu (podle hashe), přeskočíme
            foreach ($clips as $c) {
                if ($c->hash === $hash) return;
            }

            // Heuristika pro statistiky
            $text = $table->text();
            if (str_contains($text, 'Body') || str_contains($text, 'Průměr') || str_contains($text, 'Top')) {
                $clips[] = new ClipDTO(
                    id: "team_stats_table_{$n}",
                    htmlFragment: $html,
                    textHint: 'Additional team or player stats table',
                    evidence: [
                        'row_count' => $table->filter('tr')->count(),
                    ],
                    hash: $hash
                );
                $n++;
            }
        });

        return $clips;
    }
}
