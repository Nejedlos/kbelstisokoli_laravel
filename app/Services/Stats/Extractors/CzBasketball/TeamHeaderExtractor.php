<?php

namespace App\Services\Stats\Extractors\CzBasketball;

use App\Services\Stats\Contracts\StatExtractorInterface;
use App\Services\Stats\DTO\NormalizedTableDTO;
use Symfony\Component\DomCrawler\Crawler;

class TeamHeaderExtractor implements StatExtractorInterface
{
    public function extract(string $content, array $config = []): array
    {
        $crawler = new Crawler($content);

        $h1 = $crawler->filter('h1')->first();
        $teamName = $h1->count() > 0 ? trim($h1->text()) : 'Unknown Team';

        // Zkusíme najít soutěž (často v menším textu pod h1 nebo v okolí)
        $competition = null;
        $crawler->filter('h2, .competition-label, .league-name')->each(function (Crawler $node) use (&$competition) {
            if (!$competition && strlen(trim($node->text())) > 3) {
                $competition = trim($node->text());
            }
        });

        $dto = new NormalizedTableDTO(
            name: 'Team Header',
            columns: [],
            rows: [],
            metadata: [
                'team_name' => $teamName,
                'competition' => $competition,
                'source' => 'czbasketball',
            ]
        );

        return [
            'data' => $dto,
            'fragment_html' => $h1->count() > 0 ? $h1->outerHtml() : '',
        ];
    }
}
