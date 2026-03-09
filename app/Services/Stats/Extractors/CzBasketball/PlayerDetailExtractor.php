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
        // Obvykle první img v profilové části s třídou rounded a img-fluid
        $photoImg = $crawler->filter('img.rounded.img-fluid')->first();
        $photoUrl = null;
        if ($photoImg->count() > 0) {
            $photoUrl = $photoImg->attr('src');
            // Pokud je to relativní URL, přidáme doménu
            if ($photoUrl && !str_starts_with($photoUrl, 'http')) {
                $photoUrl = 'https://cz.basketball' . $photoUrl;
            }
        }

        // Osobní údaje z divů s font-weight-bold mb-1
        $details = [];
        $crawler->filter('.font-weight-bold.mb-1')->each(function (Crawler $node) use (&$details) {
            $label = trim($node->text());
            $valueNode = $node->nextAll()->first();
            if ($valueNode->count() > 0) {
                $value = trim($valueNode->text());
                $details[$label] = $value;
            }
        });

        // Mapování českých labelů na klíče
        $data = [
            'name' => $name,
            'photo_url' => $photoUrl,
            'birth_year' => $this->extractBirthYear($details['Ročník narození'] ?? $details['Datum narození'] ?? null),
            'height' => $this->extractInt($details['Výška'] ?? null),
            'position' => $details['Pozice'] ?? null,
            'current_club' => $details['Aktuální klub'] ?? null,
            'career_history' => $this->extractCareer($crawler),
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
        if (!$text) return null;
        if (preg_match('/(\d+)/', $text, $matches)) {
            return (int) $matches[1];
        }
        return null;
    }

    protected function extractCareer(Crawler $crawler): array
    {
        $history = [];
        // Hledáme tabulku kariéry (v tabu statistiky)
        $table = $crawler->filterXPath("//h3[contains(., 'Kariéra')]")->nextAll()->filter('table')->first();

        if ($table->count() === 0) {
            // Zkusíme najít jakoukoli tabulku s historií, pokud h3 není přítomno
            $table = $crawler->filter('table')->reduce(function (Crawler $node) {
                return str_contains($node->text(), 'Sezona') && str_contains($node->text(), 'Tým');
            })->first();
        }

        if ($table->count() > 0) {
            $table->filter('tbody tr')->each(function (Crawler $tr) use (&$history) {
                $cells = $tr->filter('td');
                if ($cells->count() >= 2) {
                    $history[] = [
                        'season' => trim($cells->eq(0)->text()),
                        'team' => trim($cells->eq(1)->text()),
                        'games' => $this->extractInt($cells->eq(2)->text() ?? null),
                        'points' => $this->extractInt($cells->eq(4)->text() ?? null), // Obvykle B (Body)
                    ];
                }
            });
        }

        return $history;
    }
}
