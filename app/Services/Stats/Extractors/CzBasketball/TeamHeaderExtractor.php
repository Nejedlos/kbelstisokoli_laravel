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

        // Zkusíme najít soutěž (často v menším textu pod h1 nebo v okolí, nebo pomocí labelu "Soutěž")
        $competition = null;
        $competitionUrl = null;

        $compLabel = $crawler->filterXPath("//*[normalize-space(.)='Soutěž']");
        if ($compLabel->count() > 0) {
            $compLabel->each(function (Crawler $labelNode) use (&$competition, &$competitionUrl) {
                if ($competition && $competitionUrl) return;

                // Zkusíme najít hodnotu vedle (pokud je to v tabulce nebo seznamu)
                $compValue = $labelNode->filterXPath("following::*[1]");
                if ($compValue->count() > 0) {
                    $valText = trim($compValue->text());
                    if (strlen($valText) > 2) {
                        $competition = $valText;
                        $compLink = $compValue->filter('a')->first();
                        if ($compLink->count() > 0) {
                            $competitionUrl = $compLink->attr('href');
                        }
                    }
                }

                // Zkusíme najít hodnotu v sibling divu (pokud je to v mřížce divů)
                if (!$competition || strlen($competition) < 3 || !$competitionUrl) {
                    $parent = $labelNode->closest('div');
                    if ($parent->count() > 0) {
                        $sibling = $parent->filterXPath("following-sibling::div[1]");
                        if ($sibling->count() > 0) {
                            $valText = trim($sibling->text());
                            if (strlen($valText) > 2) {
                                $competition = $valText;
                                $compLink = $sibling->filter('a')->first();
                                if ($compLink->count() > 0) {
                                    $competitionUrl = $compLink->attr('href');
                                }
                            }
                        }
                    }
                }
            });
        }

        if (!$competition) {
            $crawler->filter('h2, .competition-label, .league-name')->each(function (Crawler $node) use (&$competition, &$competitionUrl) {
                if (!$competition && strlen(trim($node->text())) > 3) {
                    $competition = trim($node->text());
                    $link = $node->filter('a')->first();
                    if ($link->count() > 0) {
                        $competitionUrl = $link->attr('href');
                    }
                }
            });
        }

        // Pokud stále nemáme URL soutěže, zkusíme najít v tabulce historie sezón (pokud známe rok)
        $year = $config['external_season_year'] ?? null;
        if (!$competitionUrl && $year) {
            $crawler->filter('table')->each(function (Crawler $table) use ($year, &$competition, &$competitionUrl) {
                if ($competitionUrl) return;
                $table->filter('tr')->each(function (Crawler $tr) use ($year, &$competition, &$competitionUrl) {
                     if ($competitionUrl) return;
                     if (str_contains($tr->text(), (string)$year)) {
                         $link = $tr->filter('a[href*="/soutez/"]')->first();
                         if ($link->count() > 0) {
                             $competitionUrl = $link->attr('href');
                             if (!$competition || $competition === 'Základní informace') {
                                 $competition = trim($link->text());
                             }
                         }
                     }
                });
            });
        }

        // Pokud stále nemáme URL soutěže, zkusíme najít jakýkoliv odkaz /soutez/, který má v textu název soutěže
        if ($competition && $competition !== 'Základní informace' && !$competitionUrl) {
            $crawler->filter('a[href*="/soutez/"]')->each(function (Crawler $node) use ($competition, &$competitionUrl) {
                if (!$competitionUrl && str_contains(mb_strtolower(trim($node->text())), mb_strtolower($competition))) {
                    $competitionUrl = $node->attr('href');
                }
            });
        }

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
                'competition_url' => $competitionUrl,
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
