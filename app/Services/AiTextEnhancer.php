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
                'timeout' => (int) ($settings['openai_timeout_seconds'] ?? 30),
            ]);

            $prompt = $this->buildBilingualPrompt($title, $date, $description);

            $response = $client->post('/v1/chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer '.$apiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => $model,
                    'temperature' => 0.5,
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
                    'description' => $this->ensureString($parsed['cs']['description'] ?? ($parsed['cs'] ?? $description), 3000, $description),
                    'seo' => [
                        'title' => $this->ensureString($parsed['cs']['seo']['title'] ?? '', 100),
                        'description' => $this->ensureString($parsed['cs']['seo']['description'] ?? '', 255),
                        'keywords' => $this->ensureString($parsed['cs']['seo']['keywords'] ?? '', 255),
                        'og_title' => $this->ensureString($parsed['cs']['seo']['og_title'] ?? '', 100),
                        'og_description' => $this->ensureString($parsed['cs']['seo']['og_description'] ?? '', 255),
                    ],
                ],
                'en' => [
                    'title' => $this->ensureString($parsed['en']['title'] ?? ($parsed['en'] ?? $title), 200, $title),
                    'description' => $this->ensureString($parsed['en']['description'] ?? ($parsed['en'] ?? $description), 3000, $description),
                    'seo' => [
                        'title' => $this->ensureString($parsed['en']['seo']['title'] ?? '', 100),
                        'description' => $this->ensureString($parsed['en']['seo']['description'] ?? '', 255),
                        'keywords' => $this->ensureString($parsed['en']['seo']['keywords'] ?? '', 255),
                        'og_title' => $this->ensureString($parsed['en']['seo']['og_title'] ?? '', 100),
                        'og_description' => $this->ensureString($parsed['en']['seo']['og_description'] ?? '', 255),
                    ],
                ],
                'date' => $this->ensureString($parsed['date'] ?? $date, 10, ($date ?? '')),
                'slug' => $this->ensureString($parsed['slug'] ?? Str::slug($parsed['cs']['title'] ?? $title), 200),
            ];
        } catch (\Throwable $e) {
            \Log::error('AI Suggestion Error: '.$e->getMessage());

            return $this->fallbackBilingual($title, $date, $description);
        }
    }

    protected function buildBilingualPrompt(string $title, ?string $date, string $description): array
    {
        $now = date('Y-m-d');
        $system = "Jsi profesionální redaktor, copywriter a social media manager basketbalového klubu \"Kbelští sokoli\".
        Tvým úkolem je na základě surového vstupu (název, datum, popis) navrhnout atraktivní bilingvní metadata (CS a EN) pro fotogalerii.

        DŮLEŽITÝ KONTEXT ČASU:
        - Dnešní datum je: {$now}
        - Pokud je datum akce ({$date}) v MINULOSTI, piš popis jako reportáž nebo vzpomínku na proběhlou akci (např. 'Ohlédnutí za...', 'Byli jsme u toho...').
        - Pokud je datum akce v BUDOUCNOSTI, piš popis jako pozvánku (např. 'Přijďte nás podpořit...', 'Těšíme se na vás...').

        POŽADAVKY:
        1. NÁZEV (title): Musí být poutavý, jasný a reprezentativní.
        2. POPIS (description): Musí být gramaticky správný, čtivý a v duchu klubu (komunitní, sportovní, pozitivní). Vylepši a rozšiř původní popis tak, aby zněl profesionálně na webu.
        3. PŘEKLAD: Vytvoř věrný, ale přirozený překlad do angličtiny (EN).
        4. FORMÁT: Výstup musí být POUZE validní JSON ve specifikované struktuře.
        5. DATUM (date): Musí být ve formátu YYYY-MM-DD. Pokud uživatel zadá jen měsíc a rok, doplň první den v měsíci.
        6. SLUG (slug): URL přátelský identifikátor vygenerovaný z českého názvu.
        7. SEO (seo): Pro každý jazyk navrhni SEO titulek (do 60 znaků), SEO popis (do 160 znaků), klíčová slova (oddělená čárkou), OG titulek a OG popis.";

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
                        'cs' => [
                            'title' => 'string',
                            'description' => 'string',
                            'seo' => [
                                'title' => 'string (max 60 chars)',
                                'description' => 'string (max 160 chars)',
                                'keywords' => 'string',
                                'og_title' => 'string',
                                'og_description' => 'string',
                            ],
                        ],
                        'en' => [
                            'title' => 'string',
                            'description' => 'string',
                            'seo' => [
                                'title' => 'string (max 60 chars)',
                                'description' => 'string (max 160 chars)',
                                'keywords' => 'string',
                                'og_title' => 'string',
                                'og_description' => 'string',
                            ],
                        ],
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
                'seo' => [
                    'title' => '',
                    'description' => '',
                    'keywords' => '',
                    'og_title' => '',
                    'og_description' => '',
                ],
            ],
            'en' => [
                'title' => Str::title(Str::of($title)->squish()),
                'description' => Str::ucfirst(Str::of($description)->squish()),
                'seo' => [
                    'title' => '',
                    'description' => '',
                    'keywords' => '',
                    'og_title' => '',
                    'og_description' => '',
                ],
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

    /**
     * Navrhne bilingvní popis pro klubovou akci (CS i EN) v basketbalovém stylu.
     */
    public function suggestClubEventDescriptionBilingual(
        string $title,
        string $type,
        string $description,
        ?string $location = null,
        ?string $startsAt = null,
        ?string $endsAt = null
    ): array
    {
        $title = trim($title);
        $description = trim($description);
        $type = trim($type);
        $location = $location ? trim($location) : null;

        $settings = $this->settingsService->getSettings();
        $enabled = (bool) ($settings['enabled'] ?? false);
        $apiKey = $settings['openai_api_key'] ?? null;
        $baseUrl = rtrim($settings['openai_base_url'] ?? 'https://api.openai.com', '/');
        $model = $settings['fast_model'] ?? ($settings['default_chat_model'] ?? 'gpt-4o-mini');

        if (! $enabled || ! $apiKey) {
            return $this->fallbackClubEventBilingual($description);
        }

        try {
            $client = new Client([
                'base_uri' => $baseUrl,
                'timeout' => (int) ($settings['openai_timeout_seconds'] ?? 30),
            ]);

            $prompt = $this->buildClubEventBilingualPrompt($title, $type, $description, $location, $startsAt, $endsAt);

            $response = $client->post('/v1/chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer '.$apiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => $model,
                    'temperature' => 0.7, // Mírně vyšší pro kreativitu
                    'messages' => $prompt,
                    'response_format' => ['type' => 'json_object'],
                ],
            ]);

            $data = json_decode((string) $response->getBody(), true);
            $content = Arr::get($data, 'choices.0.message.content');
            $parsed = json_decode($content, true);

            if (! is_array($parsed) || ! isset($parsed['cs'], $parsed['en'])) {
                return $this->fallbackClubEventBilingual($description);
            }

            return [
                'cs' => $this->ensureString($parsed['cs'] ?? $description, 5000, $description),
                'en' => $this->ensureString($parsed['en'] ?? $description, 5000, $description),
            ];
        } catch (\Throwable $e) {
            \Log::error('AI Club Event Suggestion Error: '.$e->getMessage());

            return $this->fallbackClubEventBilingual($description);
        }
    }

    protected function buildClubEventBilingualPrompt(
        string $title,
        string $type,
        string $description,
        ?string $location = null,
        ?string $startsAt = null,
        ?string $endsAt = null
    ): array
    {
        $system = "Jsi zkušený basketbalový copywriter pro klub 'Kbelští sokoli'.
Tvým úkolem je vytvořit atraktivní a dynamický popis pro klubovou akci.

POŽADAVKY:
1. Styl musí být sportovní, energický a motivující.
2. Používej basketbalovou terminologii (smeč, trojka, doskok, týmový duch, palubovka atd.).
3. Vytvoř text v češtině (klíč 'cs') a v angličtině (klíč 'en').
4. Formátuj text pomocí HTML tagů (např. <p>, <strong>, <ul>), pokud je to vhodné pro delší popis.
5. Výstupem musí být POUZE validní JSON objekt s klíči 'cs' a 'en'.
6. KRITICKÉ: Nikdy nepoužívej zástupné symboly jako '[vložte datum]', '[vložte místo]', '[doplňte]' atd. Pokud máš k dispozici datum a místo v parametrech, použij je přirozeně v textu. Pokud ne, piš text tak, aby tyto konkrétní detaily nevyžadoval (nebo je zmiň obecně).";

        $user = [
            'role' => 'user',
            'content' => json_encode([
                'action' => [
                    'title' => $title,
                    'type' => $type,
                    'location' => $location,
                    'starts_at' => $startsAt,
                    'ends_at' => $endsAt,
                    'current_description' => $description,
                ],
                'instruction' => 'Vytvoř atraktivní popis akce na základě těchto parametrů. Pokud je už něco v popisu, rozpracuj to. Pokud je popis prázdný, vymysli ho na základě názvu a typu akce. V textu nepoužívej žádné hranaté závorky ani výzvy k doplnění údajů.',
            ], JSON_UNESCAPED_UNICODE),
        ];

        return [
            ['role' => 'system', 'content' => $system],
            $user,
        ];
    }

    protected function fallbackClubEventBilingual(string $description): array
    {
        return [
            'cs' => $description,
            'en' => $description,
        ];
    }
}
