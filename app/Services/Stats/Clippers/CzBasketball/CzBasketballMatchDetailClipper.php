<?php

namespace App\Services\Stats\Clippers\CzBasketball;

use App\Services\Stats\Contracts\ClipperInterface;
use App\Services\Stats\DTO\ClipDTO;
use Symfony\Component\DomCrawler\Crawler;

class CzBasketballMatchDetailClipper implements ClipperInterface
{
    /**
     * Vyřízne hlavičku zápasu a boxscore tabulky.
     */
    public function clip(string $html, ?string $baseUrl = null): array
    {
        $crawler = new Crawler($html);
        $clips = [];

        // 1. MATCH HEADER CLIP
        $clips[] = $this->extractMatchHeader($crawler);

        // 2. BOXSCORE CLIPS
        $boxscoreClips = $this->extractBoxscoreTables($crawler);
        $clips = array_merge($clips, $boxscoreClips);

        return array_filter($clips);
    }

    protected function extractMatchHeader(Crawler $crawler): ?ClipDTO
    {
        // Najdeme blok se skóre a týmy (obvykle horní část stránky)
        // Hledáme h1 nebo specifický kontejner pro zápas
        $header = $crawler->filter('.match-detail-header')->first();
        if ($header->count() === 0) {
            // Fallback na první h1 a jeho okolí
            $header = $crawler->filter('h1')->closest('.row') ?? $crawler->filter('h1')->closest('div');
        }

        if (!$header) {
            return null;
        }

        return new ClipDTO(
            id: 'match_header',
            htmlFragment: $header->outerHtml(),
            textHint: 'Match summary header (teams, score, date)',
            evidence: [
                'text_summary' => trim($header->text()),
            ]
        );
    }

    protected function extractBoxscoreTables(Crawler $crawler): array
    {
        $clips = [];

        // Heuristika: tabulka, kde je mnoho hráčských řádků (/hrac/) a obsahuje statistické hlavičky (Body, 2B, 3B, TH)
        $tables = $crawler->filter('table')->reduce(function (Crawler $node) {
            $text = $node->text();

            // Obsahuje aspoň 2 hráče?
            $playerLinks = $node->filter('a[href*="/hrac/"]');
            if ($playerLinks->count() < 2) return false;

            // Obsahuje body nebo jiné basketbalové zkratky?
            return str_contains($text, 'Body') || str_contains($text, '2B') || str_contains($text, '3B') || str_contains($text, 'TH');
        });

        $tables->each(function (Crawler $table, $i) use (&$clips) {
            $html = $table->outerHtml();

            // Zkusíme najít název týmu nad tabulkou
            $teamName = 'Unknown Team';
            $prev = $table->previousAll()->filter('h1, h2, h3, h4, h5, .team-name')->first();
            if ($prev->count() > 0) {
                $teamName = trim($prev->text());
            }

            $links = [];
            $table->filter('a[href*="/hrac/"]')->each(function (Crawler $a) use (&$links) {
                $href = $a->attr('href');
                $links[] = [
                    'href' => $href,
                    'text' => trim($a->text()),
                    'id' => preg_match('/\/hrac\/(\d+)/', $href, $m) ? $m[1] : null
                ];
            });

            $clips[] = new ClipDTO(
                id: ($i === 0 ? 'boxscore_home' : ($i === 1 ? 'boxscore_away' : "boxscore_table_" . ($i + 1))),
                htmlFragment: $html,
                textHint: "Boxscore table for {$teamName}",
                links: $links,
                evidence: [
                    'team_label' => $teamName,
                    'row_count' => $table->filter('tr')->count(),
                ]
            );
        });

        return $clips;
    }
}
