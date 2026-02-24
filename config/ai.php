<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Globální zapnutí AI funkcí
    |--------------------------------------------------------------------------
    */
    'enabled' => env('AI_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Používat nastavení z databáze
    |--------------------------------------------------------------------------
    | Pokud je true, systém se pokusí načíst nastavení z tabulky ai_settings.
    | Pokud záznam neexistuje nebo je tato volba false, použije se config/env.
    */
    'use_database_settings' => env('AI_USE_DATABASE_SETTINGS', false),

    /*
    |--------------------------------------------------------------------------
    | OpenAI Konfigurace (Fallback)
    |--------------------------------------------------------------------------
    */
    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
        'base_url' => env('OPENAI_BASE_URL', 'https://api.openai.com/v1'),
        'organization' => env('OPENAI_ORGANIZATION'),
        'project' => env('OPENAI_PROJECT'),
        'timeout' => (int) env('OPENAI_TIMEOUT', 90),
        'max_retries' => (int) env('OPENAI_MAX_RETRIES', 3),

        'models' => [
            'default' => env('OPENAI_DEFAULT_MODEL', 'gpt-4o-mini'),
            'analyze' => env('OPENAI_ANALYZE_MODEL', 'gpt-4o'),
            'fast' => env('OPENAI_FAST_MODEL', 'gpt-4o-mini'),
            'embeddings' => env('OPENAI_EMBEDDINGS_MODEL', 'text-embedding-3-small'),
        ],

        'cache_ttl' => (int) env('OPENAI_CACHE_TTL', 3600),
    ],

    /*
    |--------------------------------------------------------------------------
    | Výchozí parametry generování
    |--------------------------------------------------------------------------
    */
    'defaults' => [
        'temperature' => 0.7,
        'top_p' => 1.0,
        'max_tokens' => 2000,
    ],
];
