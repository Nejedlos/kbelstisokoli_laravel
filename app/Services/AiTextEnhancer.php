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
     * Navrhne bilingvní metadata pro Photo Pool (CS i EN).
     * Pokud je AI vypnuté nebo selže, vrátí vstup s drobnou normalizací.
     */
    public function suggestPhotoPoolMetadataBilingual(string $title, ?string $date, string $description): array
    {
        $title = trim($title);
        $description = trim($description);
        $date = $date ? trim($date) : null;

        $settings = $this->settingsService->getSettings();
        $enabled = (bool) ($settings['enabled'] ?? false);
        $apiKey = $settings['openai_api_key'] ?? null;
        $baseUrl = rtrim($settings['openai_base_url'] ?? 'https://api.openai.com', '/');
        $model = $settings['fast_model'] ?? ($settings['default_chat_model'] ?? 'gpt-4o-mini');

        if (! $enabled || ! $apiKey) {
            return $this->fallbackBilingual($title, $date, $description);
        }

        try {
            $client = new Client([
                'base_uri' => $baseUrl,
                'timeout' => (int) ($settings['openai_timeout_seconds'] ?? 20),
            ]);

            $prompt = $this->buildBilingualPrompt($title, $date, $description);

            $response = $client->post('/v1/chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer '.$apiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => $model,
                    'temperature' => 0.3,
                    'messages' => $prompt,
                    'response_format' => ['type' => 'json_object'],
                ],
            ]);

            $data = json_decode((string) $response->getBody(), true);
            $content = Arr::get($data, 'choices.0.message.content');
            $parsed = json_decode($content, true);

            if (! is_array($parsed) || ! isset($parsed['cs'], $parsed['en'])) {
                return $this->fallbackBilingual($title, $date, $description);
            }

            return [
                'cs' => [
                    'title' => $this->ensureString($parsed['cs']['title'] ?? ($parsed['cs'] ?? $title), 200, $title),
                    'description' => $this->ensureString($parsed['cs']['description'] ?? ($parsed['cs'] ?? $description), 2000, $description),
                ],
                'en' => [
                    'title' => $this->ensureString($parsed['en']['title'] ?? ($parsed['en'] ?? $title), 200, $title),
                    'description' => $this->ensureString($parsed['en']['description'] ?? ($parsed['en'] ?? $description), 2000, $description),
                ],
                'date' => $this->ensureString($parsed['date'] ?? $date, 10, ($date ?? '')),
                'slug' => $this->ensureString($parsed['slug'] ?? Str::slug($parsed['cs']['title'] ?? $title), 200),
            ];
        } catch (\Throwable $e) {
            return $this->fallbackBilingual($title, $date, $description);
        }
    }

    protected function buildBilingualPrompt(string $title, ?string $date, string $description): array
    {
        $system = 'Jsi editor klubového webu basketbalového týmu "Kbelští sokoli". Tvým úkolem je na základě vstupu navrhnout bilingvní (česká a anglická) metadata pro fotogalerii (pool fotografií).
        Výstup musí být POUZE validní JSON.
        Pole "date" musí být ve formátu YYYY-MM-DD. Pokud uživatel zadá jen měsíc a rok, doplň první den v měsíci.
        Pole "slug" musí být URL přátelský identifikátor vygenerovaný z českého názvu.';

        $user = [
            'role' => 'user',
            'content' => json_encode([
                'input' => [
                    'title' => $title,
                    'date' => $date,
                    'description' => $description,
                ],
                'requirements' => [
                    'languages' => ['cs', 'en'],
                    'format' => 'json',
                    'structure' => [
                        'cs' => ['title' => '...', 'description' => '...'],
                        'en' => ['title' => '...', 'description' => '...'],
                        'date' => 'YYYY-MM-DD',
                        'slug' => 'url-slug-from-cs-title',
                    ],
                ],
            ], JSON_UNESCAPED_UNICODE),
        ];

        return [
            ['role' => 'system', 'content' => $system],
            $user,
        ];
    }

    protected function fallbackBilingual(string $title, ?string $date, string $description): array
    {
        return [
            'cs' => [
                'title' => Str::title(Str::of($title)->squish()),
                'description' => Str::ucfirst(Str::of($description)->squish()),
            ],
            'en' => [
                'title' => Str::title(Str::of($title)->squish()),
                'description' => Str::ucfirst(Str::of($description)->squish()),
            ],
            'date' => $date ?? '',
            'slug' => Str::slug($title),
        ];
    }

    /**
     * Zajišťuje, že hodnota z AI je skutečně řetězec a omezuje jeho délku.
     * Předchází chybám, kdy AI vrátí v JSONu vnořený objekt místo řetězce.
     */
    protected function ensureString(mixed $value, int $limit = 2000, string $default = ''): string
    {
        if ($value === null) {
            return $default;
        }

        if (is_array($value)) {
            // LLM občas vrátí {"text": "..."} nebo {"title": "..."} místo "..."
            // Zkusíme najít první stringovou hodnotu nebo použijeme známé klíče
            $value = $value['text'] ?? $value['value'] ?? $value['content'] ?? $value['title'] ?? $value['description'] ?? (is_string(reset($value)) ? reset($value) : $default);
        }

        // Pokud je to stále pole (např. vnořené), vynutíme default nebo prázdný string
        if (is_array($value)) {
            return $default;
        }

        return Str::limit(trim((string) $value), $limit, '');
    }

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
        $enabled = (bool) ($settings['enabled'] ?? false);
        $apiKey = $settings['openai_api_key'] ?? null;
        $baseUrl = rtrim($settings['openai_base_url'] ?? 'https://api.openai.com', '/');
        $model = $settings['fast_model'] ?? ($settings['default_chat_model'] ?? 'gpt-4o-mini');

        if (! $enabled || ! $apiKey) {
            return $this->fallback($title, $date, $description);
        }

        try {
            $client = new Client([
                'base_uri' => $baseUrl,
                'timeout' => (int) ($settings['openai_timeout_seconds'] ?? 20),
            ]);

            $prompt = $this->buildPrompt($title, $date, $description, $locale);

            $response = $client->post('/v1/chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer '.$apiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => $model,
                    'temperature' => (float) ($settings['temperature'] ?? 0.3),
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

            if (! is_array($parsed)) {
                return $this->fallback($title, $date, $description);
            }

            return [
                'title' => $this->ensureString($parsed['title'] ?? $title, 200, $title),
                'date' => $this->ensureString($parsed['date'] ?? $date, 10, ($date ?? '')),
                'description' => $this->ensureString($parsed['description'] ?? $description, 2000, $description),
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
