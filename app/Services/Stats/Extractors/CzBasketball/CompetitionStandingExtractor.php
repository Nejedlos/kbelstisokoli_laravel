<?php

namespace App\Services\Stats\Extractors\CzBasketball;

use App\Services\Stats\Contracts\StatExtractorInterface;
use App\Services\Stats\DTO\NormalizedRowDTO;
use App\Services\Stats\DTO\NormalizedTableDTO;
use Symfony\Component\DomCrawler\Crawler;

class CompetitionStandingExtractor implements StatExtractorInterface
{
    public function extract(string $content, array $config = []): array
    {
        $crawler = new Crawler($content);
        $rows = [];

        // Hledáme tabulku s pořadím
        $table = $crawler->filter('table')->reduce(function (Crawler $node) {
            $text = mb_strtolower($node->text());
            return str_contains($text, 'pořadí') && str_contains($text, 'tým');
        })->first();

        if ($table->count() === 0) {
            return [
                'data' => new NormalizedTableDTO('Competition Standing', [], []),
                'fragment_html' => '',
            ];
        }

        // Zjistíme indexy sloupců z hlavičky
        $headers = [];
        $table->filter('thead th')->each(function (Crawler $th, $i) use (&$headers) {
            $text = mb_strtolower(trim($th->text()));
            if (str_contains($text, 'pořadí')) $headers['rank'] = $i;
            if (str_contains($text, 'tým')) $headers['team'] = $i;
            if (trim($text) === 'z') $headers['gp'] = $i;
            if (trim($text) === 'v') $headers['w'] = $i;
            if (trim($text) === 'p') $headers['l'] = $i;
            if (str_contains($text, 'vstřeleno') || str_contains($text, 'skóre')) $headers['score_plus'] = $i;
            if (str_contains($text, 'obdrženo')) $headers['score_minus'] = $i;
            if (str_contains($text, 'body') || trim($text) === 'b') $headers['points'] = $i;
        });

        $table->filter('tbody tr')->each(function (Crawler $tr) use (&$rows, $headers) {
            $cells = $tr->filter('td');
            if ($cells->count() < 4) return;

            $rank = isset($headers['rank']) ? trim($cells->eq($headers['rank'])->text()) : null;
            $teamName = isset($headers['team']) ? trim($cells->eq($headers['team'])->text()) : 'Unknown';
            $gp = isset($headers['gp']) ? trim($cells->eq($headers['gp'])->text()) : 0;
            $w = isset($headers['w']) ? trim($cells->eq($headers['w'])->text()) : 0;
            $l = isset($headers['l']) ? trim($cells->eq($headers['l'])->text()) : 0;

            $scorePlus = isset($headers['score_plus']) ? str_replace('.', '', trim($cells->eq($headers['score_plus'])->text())) : '';
            $scoreMinus = isset($headers['score_minus']) ? str_replace('.', '', trim($cells->eq($headers['score_minus'])->text())) : '';

            $score = $scorePlus;
            if ($scoreMinus) {
                $score .= ':' . $scoreMinus;
            }

            $points = isset($headers['points']) ? trim($cells->eq($headers['points'])->text()) : null;

            $rows[] = new NormalizedRowDTO(
                values: [
                    'rank' => (int) $rank,
                    'team_name' => $teamName,
                    'gp' => (int) str_replace('.', '', $gp),
                    'w' => (int) str_replace('.', '', $w),
                    'l' => (int) str_replace('.', '', $l),
                    'score' => $score,
                    'points' => $points ? (int) str_replace('.', '', $points) : null,
                ]
            );
        });

        return [
            'data' => new NormalizedTableDTO('Competition Standing', [], $rows),
            'fragment_html' => $table->outerHtml(),
        ];
    }
}
