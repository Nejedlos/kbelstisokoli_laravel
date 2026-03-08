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

        // Zkusíme najít další detaily (Trenér, Hala, atd.)
        $coach = null;
        $assistants = [];
        $manager = null;
        $venue = null;
        $website = null;

        $crawler->filter('.contact-list, .team-info, .team-details, .table-condensed')->each(function (Crawler $node) use (&$coach, &$assistants, &$manager, &$venue, &$website) {
            $text = $node->text();

            if (preg_match('/Trenér:\s*([^<>\n]+)/u', $text, $m)) {
                $coach = trim($m[1]);
            }
            if (preg_match('/Asistent[^\s]*:\s*([^<>\n]+)/u', $text, $m)) {
                $assistants[] = trim($m[1]);
            }
            if (preg_match('/Vedoucí[^\s]*:\s*([^<>\n]+)/u', $text, $m)) {
                $manager = trim($m[1]);
            }
            if (preg_match('/Hala:\s*([^<>\n]+)/u', $text, $m)) {
                $venue = trim($m[1]);
            }
            if (preg_match('/Web[^\s]*:\s*([^<>\n\s]+)/u', $text, $m)) {
                $website = trim($m[1]);
            }
        });

        $dto = new NormalizedTableDTO(
            name: 'Team Header',
            columns: [],
            rows: [],
            metadata: [
                'team_name' => $teamName,
                'competition' => $competition,
                'coach' => $coach,
                'assistants' => $assistants,
                'manager' => $manager,
                'venue' => $venue,
                'website' => $website,
                'source' => 'czbasketball',
            ]
        );

        return [
            'data' => $dto,
            'fragment_html' => $h1->count() > 0 ? $h1->outerHtml() : '',
        ];
    }
}
