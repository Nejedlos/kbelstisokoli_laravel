<?php

namespace App\Services\Stats\Extractors\CzBasketball;

use App\Services\Stats\Contracts\StatExtractorInterface;
use App\Services\Stats\DTO\NormalizedRowDTO;
use App\Services\Stats\DTO\NormalizedTableDTO;
use Symfony\Component\DomCrawler\Crawler;
use Carbon\Carbon;

class CompetitionScheduleExtractor implements StatExtractorInterface
{
    public function extract(string $content, array $config = []): array
    {
        $crawler = new Crawler($content);
        $rows = [];

        // Hledáme tabulku s rozpisem
        $table = $crawler->filter('table')->reduce(function (Crawler $node) {
            $text = mb_strtolower($node->text());
            return (str_contains($text, 'domácí') && str_contains($text, 'hosté')) ||
                   (str_contains($text, 'datum') && str_contains($text, 'skore'));
        })->first();

        if ($table->count() === 0) {
            return [
                'data' => new NormalizedTableDTO('Competition Schedule', [], []),
                'fragment_html' => '',
            ];
        }

        // Zjistíme indexy sloupců z hlavičky
        $headers = [];
        $table->filter('thead th')->each(function (Crawler $th, $i) use (&$headers) {
            $text = mb_strtolower(trim($th->text()));
            if (str_contains($text, 'domácí') || str_contains($text, 'hosté')) $headers['teams'] = $i;
            if (str_contains($text, 'datum')) $headers['date'] = $i;
            if (str_contains($text, 'skore') || str_contains($text, 'skóre')) $headers['score'] = $i;
        });

        $table->filter('tbody tr')->each(function (Crawler $tr) use (&$rows, $headers) {
            $cells = $tr->filter('td');
            if ($cells->count() < 3) return;

            $matchExtId = null;
            $matchLink = $tr->filter('a[href*="/zapas/"]')->first();
            if ($matchLink->count() > 0) {
                $href = $matchLink->attr('href');
                if (preg_match('/\/zapas\/(\d+)/', $href, $matches)) {
                    $matchExtId = $matches[1];
                }
            }

            // Extrakce dat (často v buňce "datum a čas", může mít br)
            $dateText = isset($headers['date']) ? trim($cells->eq($headers['date'])->text()) : '';
            // Nahradíme více mezer a nové řádky jednou mezerou pro parsování
            $cleanDateText = preg_replace('/\s+/', ' ', $dateText);

            $scheduledAt = null;
            if (preg_match('/(\d{1,2})\.\s*(\d{1,2})\.\s*(\d{4})(\s+(\d{1,2}):(\d{2}))?/', $cleanDateText, $m)) {
                $day = str_pad($m[1], 2, '0', STR_PAD_LEFT);
                $month = str_pad($m[2], 2, '0', STR_PAD_LEFT);
                $year = $m[3];
                $hour = isset($m[5]) ? str_pad($m[5], 2, '0', STR_PAD_LEFT) : '00';
                $min = isset($m[6]) ? str_pad($m[6], 2, '0', STR_PAD_LEFT) : '00';
                try {
                    $scheduledAt = Carbon::createFromFormat('d.m.Y H:i', "$day.$month.$year $hour:$min")->toDateTimeString();
                } catch (\Exception $e) {}
            }

            // Týmy (pokud jsou v jedné buňce, zkusíme je rozdělit přes divy nebo nové řádky)
            $homeTeam = 'Unknown';
            $awayTeam = 'Unknown';
            if (isset($headers['teams'])) {
                $teamCell = $cells->eq($headers['teams']);
                $divs = $teamCell->filter('div');
                if ($divs->count() >= 2) {
                    $homeTeam = trim($divs->eq(0)->text());
                    $awayTeam = trim($divs->eq(1)->text());
                } else {
                    $teams = explode("\n", trim($teamCell->text()));
                    $homeTeam = trim($teams[0] ?? 'Unknown');
                    $awayTeam = trim($teams[1] ?? 'Unknown');
                }
            }

            $scoreText = isset($headers['score']) ? trim($cells->eq($headers['score'])->text()) : '';

            $scoreHome = null;
            $scoreAway = null;

            // Zkusíme najít skóre v různých formátech (číslo : číslo nebo dvě čísla oddělená mezerou/novým řádkem)
            if (preg_match('/(\d+)\s*[:\-\s]\s*(\d+)/', $scoreText, $sm)) {
                $scoreHome = (int) $sm[1];
                $scoreAway = (int) $sm[2];
            } elseif (preg_match_all('/\d+/', $scoreText, $mScores)) {
                if (count($mScores[0]) >= 2) {
                    $scoreHome = (int) $mScores[0][0];
                    $scoreAway = (int) $mScores[0][1];
                }
            }

            if ($matchExtId) {
                $score = ($scoreHome !== null && $scoreAway !== null) ? "$scoreHome:$scoreAway" : null;

                $rows[] = new NormalizedRowDTO(
                    values: [
                        'external_match_id' => $matchExtId,
                        'scheduled_at' => $scheduledAt,
                        'home_team' => $homeTeam,
                        'away_team' => $awayTeam,
                        'score' => $score,
                        'score_home' => $scoreHome,
                        'score_away' => $scoreAway,
                        'status' => ($scoreHome !== null) ? 'finished' : 'scheduled',
                    ],
                    metadata: [
                        'external_id' => $matchExtId,
                        'original_date' => $dateText,
                    ]
                );
            }
        });

        return [
            'data' => new NormalizedTableDTO('Competition Schedule', [], $rows),
            'fragment_html' => $table->outerHtml(),
        ];
    }
}
