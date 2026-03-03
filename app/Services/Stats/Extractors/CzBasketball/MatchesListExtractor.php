<?php

namespace App\Services\Stats\Extractors\CzBasketball;

use App\Services\Stats\Contracts\StatExtractorInterface;
use App\Services\Stats\DTO\NormalizedRowDTO;
use App\Services\Stats\DTO\NormalizedTableDTO;
use Carbon\Carbon;
use Symfony\Component\DomCrawler\Crawler;

class MatchesListExtractor implements StatExtractorInterface
{
    /**
     * Extrahuje seznam zápasů z HTML stránky.
     */
    public function extract(string $content, array $config = []): array
    {
        $crawler = new Crawler($content);
        $warnings = [];

        // Hledáme tabulku se zápasy - obvykle table.table-striped
        $table = $crawler->filter('table.table-striped')->first();

        if ($table->count() === 0) {
            // Fallback na jakoukoli tabulku obsahující /zapas/
            $table = $crawler->filter('table')->reduce(function (Crawler $node) {
                return str_contains($node->html(), '/zapas/');
            })->first();
        }

        if ($table->count() === 0) {
            return [
                'data' => new NormalizedTableDTO('Zápasy', [], [], ['warnings' => ['Table not found']]),
                'fragment_html' => '',
            ];
        }

        $fragmentHtml = $table->outerHtml();
        $rows = [];

        $table->filter('tbody tr')->each(function (Crawler $tr) use (&$rows, &$warnings) {
            $cells = $tr->filter('td');
            if ($cells->count() < 3) return;

            // Najdeme odkaz na detail zápasu
            $matchLink = $tr->filter('a[href*="/zapas/"]')->first();
            $matchId = null;
            if ($matchLink->count() > 0) {
                if (preg_match('/\/zapas\/(\d+)/', $matchLink->attr('href'), $matches)) {
                    $matchId = $matches[1];
                }
            }

            // Datum a čas (druhá buňka)
            $dateCell = $cells->eq(1);
            $dateStr = trim($dateCell->text());

            // Vyčistíme datum (může obsahovat den v týdnu a br)
            // Např. "6. 3. 2026 Pá 19:15"
            $scheduledAt = null;
            if ($dateStr) {
                // Odstraníme dny v týdnu
                $cleanDateStr = preg_replace('/(Po|Út|St|Čt|Pá|So|Ne)\s*/', '', $dateStr);
                // Nahradíme více mezer jednou
                $cleanDateStr = preg_replace('/\s+/', ' ', trim($cleanDateStr));

                try {
                    // Formát: 6. 3. 2026 19:15
                    $scheduledAt = Carbon::createFromFormat('j. n. Y H:i', $cleanDateStr);
                } catch (\Exception $e) {
                    try {
                        // Zkusíme bez času
                        $scheduledAt = Carbon::createFromFormat('j. n. Y', explode(' ', $cleanDateStr)[0] . ' ' . explode(' ', $cleanDateStr)[1] . ' ' . explode(' ', $cleanDateStr)[2]);
                    } catch (\Exception $e2) {
                        $warnings[] = "Could not parse date: $dateStr";
                    }
                }
            }

            // Týmy (třetí buňka)
            $teamNodes = $cells->eq(2)->filter('.text-nowrap');
            if ($teamNodes->count() >= 2) {
                $homeTeam = trim($teamNodes->eq(0)->text());
                $awayTeam = trim($teamNodes->eq(1)->text());
            } else {
                // Fallback na text rozdělený novým řádkem nebo něčím
                $teams = explode("\n", trim($cells->eq(2)->text()));
                $homeTeam = trim($teams[0] ?? 'Unknown');
                $awayTeam = trim($teams[1] ?? 'Unknown');
            }

            // Skóre (čtvrtá buňka)
            $score = trim($cells->eq(3)->text());
            $status = 'planned';
            if ($score && preg_match('/\d+\s*:\s*\d+/', $score)) {
                $status = 'completed';
            }

            $rows[] = new NormalizedRowDTO(
                values: [
                    'scheduled_at' => $scheduledAt?->toDateTimeString(),
                    'home_team' => $homeTeam,
                    'away_team' => $awayTeam,
                    'score' => $score,
                    'status' => $status,
                    'external_match_id' => $matchId,
                ],
                metadata: [
                    'external_match_id' => $matchId,
                    'source' => 'czbasketball',
                ]
            );
        });

        $dto = new NormalizedTableDTO(
            name: 'Zápasy',
            columns: [
                'scheduled_at' => 'Datum a čas',
                'home_team' => 'Domácí',
                'away_team' => 'Hosté',
                'score' => 'Skóre',
                'status' => 'Stav',
            ],
            rows: $rows,
            metadata: [
                'warnings' => $warnings,
            ]
        );

        return [
            'data' => $dto,
            'fragment_html' => $fragmentHtml,
        ];
    }
}
