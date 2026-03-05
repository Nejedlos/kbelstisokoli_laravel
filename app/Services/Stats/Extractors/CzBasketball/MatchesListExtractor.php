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

        // Hledáme tabulku se zápasy - hledáme tu, která má v hlavičce "domácí / hosté" a "datum a čas"
        $table = $crawler->filter('table')->reduce(function (Crawler $node) {
            $thead = $node->filter('thead');
            if ($thead->count() === 0) {
                return false;
            }
            $headerText = mb_strtolower($thead->text());

            // Kontrolujeme, zda obsahuje aspoň "hosté" a "datum" a "skore" (to je v obou variantách)
            return (str_contains($headerText, 'hosté') || str_contains($headerText, 'domácí'))
                && (str_contains($headerText, 'datum'))
                && (str_contains($headerText, 'skore') || str_contains($headerText, 'skóre'));
        });

        // Pokud jich je víc, chceme tu, která má "kolo" (to je ta z tabu #3 nebo ze stránky zápasů)
        if ($table->count() > 1) {
            $tableWithKolo = $table->reduce(function (Crawler $node) {
                return str_contains(mb_strtolower($node->filter('thead')->text()), 'kolo');
            });
            if ($tableWithKolo->count() > 0) {
                $table = $tableWithKolo;
            }
        }

        $table = $table->first();

        if ($table->count() === 0) {
            // Fallback na table.table-striped, která obsahuje /zapas/
            $table = $crawler->filter('table.table-striped')->reduce(function (Crawler $node) {
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
            if ($cells->count() < 3) {
                return;
            }

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
                        // Zkusíme bez času - rozdělíme podle mezer a vezmeme první 3 části (např. 6., 3., 2026)
                        $parts = array_filter(explode(' ', $cleanDateStr));
                        $dateOnly = implode(' ', array_slice($parts, 0, 3));
                        $scheduledAt = Carbon::createFromFormat('j. n. Y', $dateOnly);
                    } catch (\Exception $e2) {
                        $warnings[] = "Could not parse date: $dateStr";
                    }
                }
            }

            // Týmy (třetí buňka)
            $teamNodes = $cells->eq(2)->filter('.text-nowrap');
            $homeTeamId = null;
            $awayTeamId = null;

            if ($teamNodes->count() >= 2) {
                $homeTeam = trim($teamNodes->eq(0)->text());
                $awayTeam = trim($teamNodes->eq(1)->text());

                // Zkusíme najít external_id týmu (v odkazu)
                $homeLink = $teamNodes->eq(0)->filter('a[href*="/tym/"]')->first();
                if ($homeLink->count() > 0 && preg_match('/\/tym\/(\d+)/', $homeLink->attr('href'), $m)) {
                    $homeTeamId = $m[1];
                }
                $awayLink = $teamNodes->eq(1)->filter('a[href*="/tym/"]')->first();
                if ($awayLink->count() > 0 && preg_match('/\/tym\/(\d+)/', $awayLink->attr('href'), $m)) {
                    $awayTeamId = $m[1];
                }
            } else {
                // Fallback na text rozdělený novým řádkem nebo něčím
                $teams = explode("\n", trim($cells->eq(2)->text()));
                $homeTeam = trim($teams[0] ?? 'Unknown');
                $awayTeam = trim($teams[1] ?? 'Unknown');

                // Zkusíme aspoň odkaz kdekoli v buňce (pokud jsou tam dva, tak první=home, druhý=away)
                $links = $cells->eq(2)->filter('a[href*="/tym/"]');
                if ($links->count() >= 1 && preg_match('/\/tym\/(\d+)/', $links->eq(0)->attr('href'), $m)) {
                    $homeTeamId = $m[1];
                }
                if ($links->count() >= 2 && preg_match('/\/tym\/(\d+)/', $links->eq(1)->attr('href'), $m)) {
                    $awayTeamId = $m[1];
                }
            }

            // Skóre (čtvrtá buňka)
            $scoreCell = $cells->eq(3);
            $scoreDivs = $scoreCell->filter('div');

            if ($scoreDivs->count() >= 2) {
                $score = trim($scoreDivs->eq(0)->text()).':'.trim($scoreDivs->eq(1)->text());
            } else {
                $score = trim($scoreCell->text());
                // Pokud obsahuje mezeru nebo pomlčku a neobsahuje dvojtečku, zkusíme ji nahradit
                if (! str_contains($score, ':') && preg_match('/(\d+)\s*[\s\-]\s*(\d+)/', $score, $scoreMatches)) {
                    $score = $scoreMatches[1].':'.$scoreMatches[2];
                }
            }

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
                    'home_team_external_id' => $homeTeamId,
                    'away_team_external_id' => $awayTeamId,
                ],
                metadata: [
                    'external_match_id' => $matchId,
                    'home_team_external_id' => $homeTeamId,
                    'away_team_external_id' => $awayTeamId,
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
