<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class AiTextEnhancer
{
    public function __construct(
        protected AiSettingsService $settingsService
    ) {}

    /**
     * Navrhne/metodicky vylepší metadata pro Photo Pool.
     * Pokud je AI vypnuté nebo selže, vrátí vstup s drobnou normalizací.
     */
    public function suggestPhotoPoolMetadata(string $title, ?string $date, string $description, string $locale = 'cs'): array
    {
        $title = trim($title);
        $description = trim($description);
        $date = $date ? trim($date) : null;

        $settings = $this->settingsService->getSettings();
        $enabled = (bool)($settings['enabled'] ?? false);
        $apiKey = $settings['openai_api_key'] ?? null;
        $baseUrl = rtrim($settings['openai_base_url'] ?? 'https://api.openai.com', '/');
        $model = $settings['fast_model'] ?? ($settings['default_chat_model'] ?? 'gpt-4o-mini');

        if (!$enabled || !$apiKey) {
            return $this->fallback($title, $date, $description);
        }

        try {
            $client = new Client([
                'base_uri' => $baseUrl,
                'timeout' => (int)($settings['openai_timeout_seconds'] ?? 20),
            ]);

            $prompt = $this->buildPrompt($title, $date, $description, $locale);

            $response = $client->post('/v1/chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => $model,
                    'temperature' => (float)($settings['temperature'] ?? 0.3),
                    'messages' => $prompt,
                    'response_format' => ['type' => 'json_schema', 'json_schema' => [
                        'name' => 'photo_pool_metadata',
                        'schema' => [
                            'type' => 'object',
                            'required' => ['title', 'description'],
                            'properties' => [
                                'title' => ['type' => 'string'],
                                'date' => ['type' => 'string'],
                                'description' => ['type' => 'string'],
                            ],
                        ],
                    ]],
                ],
            ]);

            $data = json_decode((string) $response->getBody(), true);
            $content = Arr::get($data, 'choices.0.message.content');
            $parsed = json_decode($content, true);

            if (!is_array($parsed)) {
                return $this->fallback($title, $date, $description);
            }

            return [
                'title' => $parsed['title'] ? Str::limit(trim((string)$parsed['title']), 200) : $title,
                'date' => $parsed['date'] ? trim((string)$parsed['date']) : ($date ?? ''),
                'description' => $parsed['description'] ? Str::limit(trim((string)$parsed['description']), 2000) : $description,
            ];
        } catch (\Throwable $e) {
            // Bezpečný fallback
            return $this->fallback($title, $date, $description);
        }
    }

    protected function buildPrompt(string $title, ?string $date, string $description, string $locale): array
    {
        $system = $locale === 'cs'
            ? 'Jsi editor klubového webu basketbalového týmu. Vylepši popisy akcí, buď stručný, informativní a bez teček na konci nadpisu.'
            : 'You are an editor of a basketball club website. Improve event descriptions, be concise and informative. No trailing periods in titles.';

        $user = [
            'role' => 'user',
            'content' => [
                [
                    'type' => 'text',
                    'text' => json_encode([
                        'title' => $title,
                        'date' => $date,
                        'description' => $description,
                        'locale' => $locale,
                        'requirements' => [
                            'return_json' => true,
                            'title_max_len' => 200,
                            'description_max_len' => 2000,
                            'keep_language' => true,
                        ],
                    ], JSON_UNESCAPED_UNICODE),
                ],
            ],
        ];

        return [
            ['role' => 'system', 'content' => $system],
            $user,
        ];
    }

    protected function fallback(string $title, ?string $date, string $description): array
    {
        return [
            'title' => Str::title(Str::of($title)->squish()),
            'date' => $date ?? '',
            'description' => Str::ucfirst(Str::of($description)->squish()),
        ];
    }
}
