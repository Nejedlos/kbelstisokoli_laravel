<?php

namespace App\Services;

use App\Models\BasketballMatch;
use App\Models\ClubEvent;
use App\Models\Post;
use App\Models\PostCategory;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AiNewsService
{
    public function __construct(
        protected AiSettingsService $aiSettings
    ) {}

    /**
     * Vygeneruje týdenní novinku na základě dat z klubu.
     */
    public function generateWeeklyNews(): ?Post
    {
        $settings = $this->aiSettings->getSettings();

        if (! ($settings['enabled'] ?? true)) {
            Log::warning('AI News Generation: AI is disabled in settings.');
            return null;
        }

        $data = $this->gatherClubData();

        if ($this->isDataEmpty($data)) {
            Log::info('AI News Generation: No significant data found for the last/next week.');
            return null;
        }

        $aiResponse = $this->callOpenAi($data, $settings);

        if (! $aiResponse) {
            return null;
        }

        return $this->createPost($aiResponse);
    }

    /**
     * Shromáždí data o zápasech a událostech.
     */
    protected function gatherClubData(): array
    {
        $lastWeek = now()->subDays(7);
        $nextWeek = now()->addDays(7);

        $recentMatches = BasketballMatch::where('scheduled_at', '>=', $lastWeek)
            ->where('scheduled_at', '<=', now())
            ->with(['team', 'opponent'])
            ->get()
            ->map(function ($match) {
                return [
                    'team' => $match->official_team_name,
                    'opponent' => $match->official_opponent_name,
                    'score' => $match->has_score ? "{$match->score_home}:{$match->score_away}" : 'beze skóre',
                    'result' => $match->has_score ? ($match->is_win ? 'výhra' : ($match->is_loss ? 'prohra' : 'remíza')) : 'neodehráno',
                    'date' => $match->scheduled_at->format('d.m.Y H:i'),
                    'is_home' => $match->is_home,
                ];
            });

        $upcomingMatches = BasketballMatch::where('scheduled_at', '>', now())
            ->where('scheduled_at', '<=', $nextWeek)
            ->with(['team', 'opponent'])
            ->get()
            ->map(function ($match) {
                return [
                    'team' => $match->official_team_name,
                    'opponent' => $match->official_opponent_name,
                    'date' => $match->scheduled_at->format('d.m.Y H:i'),
                    'location' => $match->location,
                    'is_home' => $match->is_home,
                ];
            });

        $events = ClubEvent::where('starts_at', '>=', $lastWeek)
            ->where('starts_at', '<=', $nextWeek)
            ->get()
            ->map(function ($event) {
                return [
                    'title' => $event->title,
                    'type' => $event->event_type,
                    'date' => $event->starts_at->format('d.m.Y H:i'),
                    'location' => $event->location,
                ];
            });

        return [
            'recent_matches' => $recentMatches->toArray(),
            'upcoming_matches' => $upcomingMatches->toArray(),
            'events' => $events->toArray(),
            'current_date' => now()->format('d.m.Y'),
        ];
    }

    /**
     * Zjistí, zda jsou nasbíraná data prázdná.
     */
    protected function isDataEmpty(array $data): bool
    {
        return empty($data['recent_matches']) && empty($data['upcoming_matches']) && empty($data['events']);
    }

    /**
     * Zavolá OpenAI API pro vygenerování článku.
     */
    protected function callOpenAi(array $data, array $settings): ?array
    {
        $prompt = $this->buildPrompt($data);

        $payload = [
            'model' => $settings['analyze_model'] ?? 'gpt-4o',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'Jsi zkušený sportovní redaktor basketbalového klubu "Kbelští sokoli". Tvým úkolem je na základě strohých dat napsat poutavou týdenní aktualitu (krátký novinový článek). Piš profesionálně, ale s vášní pro klub. Článek musí mít 2-3 odstavce (nebo více, pokud je dost témat, ale nevymýšlej si fakta). Výstup vrať ve formátu JSON s klíči "title_cs", "content_cs", "excerpt_cs", "title_en", "content_en", "excerpt_en".',
                ],
                [
                    'role' => 'user',
                    'content' => $prompt,
                ],
            ],
            'response_format' => ['type' => 'json_object'],
            'temperature' => 0.7,
        ];

        try {
            $response = Http::timeout((int) ($settings['openai_timeout_seconds'] ?? 90))
                ->withToken($settings['openai_api_key'])
                ->baseUrl($settings['openai_base_url'] ?? 'https://api.openai.com/v1')
                ->post('/chat/completions', $payload)
                ->throw()
                ->json();

            $content = json_decode($response['choices'][0]['message']['content'], true);

            // Logování úspěchu
            $this->aiSettings->logRequest([
                'context' => 'weekly_news_generation',
                'model' => $payload['model'],
                'status' => 'success',
                'latency_ms' => 0, // Nemáme přesné měření tady, ale můžeme doplnit pokud chceme
                'token_usage' => $response['usage'] ?? null,
            ]);

            return $content;

        } catch (\Throwable $e) {
            Log::error('AI News Generation Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Sestaví prompt pro OpenAI.
     */
    protected function buildPrompt(array $data): string
    {
        $prompt = "Zde jsou data z klubu Kbelští sokoli za uplynulý týden a výhled na příští týden (dnešní datum: {$data['current_date']}):\n\n";

        if (!empty($data['recent_matches'])) {
            $prompt .= "ODEHRANÉ ZÁPASY:\n";
            foreach ($data['recent_matches'] as $m) {
                $prompt .= "- {$m['date']}: {$m['team']} vs {$m['opponent']} -> {$m['score']} ({$m['result']})\n";
            }
            $prompt .= "\n";
        }

        if (!empty($data['upcoming_matches'])) {
            $prompt .= "NADCHÁZEJÍCÍ ZÁPASY:\n";
            foreach ($data['upcoming_matches'] as $m) {
                $prompt .= "- {$m['date']}: {$m['team']} vs {$m['opponent']} (Místo: {$m['location']})\n";
            }
            $prompt .= "\n";
        }

        if (!empty($data['events'])) {
            $prompt .= "DALŠÍ UDÁLOSTI:\n";
            foreach ($data['events'] as $e) {
                $prompt .= "- {$e['date']}: {$e['title']} ({$e['type']}, Místo: {$e['location']})\n";
            }
            $prompt .= "\n";
        }

        $prompt .= "Napiš článek, který shrne tyto události. Zaměř se na úspěchy, pozvi fanoušky na nadcházející zápasy a informuj o akcích v klubu. Piš v češtině i angličtině.";

        return $prompt;
    }

    /**
     * Vytvoří model Post.
     */
    protected function createPost(array $aiData): Post
    {
        $category = PostCategory::where('slug', 'aktuality')->first()
            ?? PostCategory::where('id', 1)->first(); // Fallback na ID 1 (Obecné)

        $post = new Post();
        $post->category_id = $category?->id;
        $post->setTranslation('title', 'cs', $aiData['title_cs'] ?? 'Týdenní přehled sokolů');
        $post->setTranslation('title', 'en', $aiData['title_en'] ?? 'Weekly Falcons Overview');
        $post->setTranslation('excerpt', 'cs', $aiData['excerpt_cs'] ?? '');
        $post->setTranslation('excerpt', 'en', $aiData['excerpt_en'] ?? '');
        $post->setTranslation('content', 'cs', $aiData['content_cs'] ?? '');
        $post->setTranslation('content', 'en', $aiData['content_en'] ?? '');
        $post->slug = Str::slug($aiData['title_cs'] ?? 'tydenni-prehled-' . now()->format('Y-W'));
        $post->status = 'published';
        $post->is_visible = true;
        $post->publish_at = now();
        $post->save();

        return $post;
    }
}
