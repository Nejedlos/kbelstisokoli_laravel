<?php

return [
    // Základní brand identita (může být později spravována přes admin UI)
    'club_name' => env('APP_BRAND_NAME', 'Kbelští sokoli C & E'),
    'club_short_name' => env('APP_BRAND_SHORT', 'Sokoli'),
    'slogan' => env('APP_BRAND_SLOGAN', null),

    // Soubory / loga (cesty ve storage/public) – zatím prázdné
    'logo_path' => env('APP_BRAND_LOGO', null),
    'alt_logo_path' => env('APP_BRAND_LOGO_ALT', null),

    // Barvy motivu – preferujte Tailwind theme tokens; zde pouze fallbacky
    'primary_color' => env('APP_BRAND_PRIMARY', null),

    // Kontakty (volitelné)
    'contact' => [
        'email' => env('APP_CONTACT_EMAIL', null),
        'phone' => env('APP_CONTACT_PHONE', null),
        'address' => env('APP_CONTACT_ADDRESS', null),
    ],

    // Sociální sítě (volitelné)
    'socials' => [
        'facebook' => env('APP_SOCIAL_FACEBOOK', null),
        'instagram' => env('APP_SOCIAL_INSTAGRAM', null),
        'youtube' => env('APP_SOCIAL_YOUTUBE', null),
    ],

    // CTA (volitelně)
    'default_cta' => [
        'enabled' => env('APP_CTA_ENABLED', false),
        'label' => env('APP_CTA_LABEL', null),
        'url' => env('APP_CTA_URL', null),
    ],

    // Copyright text (fallback)
    'footer_text' => env('APP_BRAND_COPYRIGHT', null),

    // Předdefinované presety témat (Themes)
    'themes' => [
        'club-default' => [
            'label' => 'Club Default (Sokol)',
            'colors' => [
                'navy' => '#0B1F3A',
                'blue' => '#2563EB',
                'red' => '#E11D48',
                'white' => '#FFFFFF',
                'bg' => '#F8FAFC',
                'surface' => '#FFFFFF',
                'surface_alt' => '#EEF2F7',
                'border' => '#DCE3EC',
                'text' => '#0F172A',
                'text_muted' => '#64748B',
            ],
        ],
        'dark-arena' => [
            'label' => 'Dark Arena',
            'colors' => [
                'navy' => '#020617',
                'blue' => '#3B82F6',
                'red' => '#F43F5E',
                'white' => '#FFFFFF',
                'bg' => '#0F172A',
                'surface' => '#1E293B',
                'surface_alt' => '#334155',
                'border' => '#475569',
                'text' => '#F8FAFC',
                'text_muted' => '#94A3B8',
            ],
        ],
        'light-clean' => [
            'label' => 'Light Clean',
            'colors' => [
                'navy' => '#1E293B',
                'blue' => '#60A5FA',
                'red' => '#FB7185',
                'white' => '#FFFFFF',
                'bg' => '#FFFFFF',
                'surface' => '#F8FAFC',
                'surface_alt' => '#F1F5F9',
                'border' => '#E2E8F0',
                'text' => '#334155',
                'text_muted' => '#64748B',
            ],
        ],
    ],

    // Výchozí preset
    'default_theme' => 'club-default',
];
