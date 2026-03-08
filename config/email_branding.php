<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Email Branding Configuration
    |--------------------------------------------------------------------------
    |
    | Tato konfigurace definuje vizuální prvky odchozích e-mailů.
    | Čerpá z obecného nastavení brandingu (config/branding.php).
    |
    */

    'brand_name' => config('branding.club_name', 'TJ Sokol Kbely C & E'),
    'brand_short_name' => config('branding.club_short_name', 'Sokoli'),
    'brand_url' => config('app.url'),

    // Logo musí být dostupné přes absolutní URL
    'logo_url' => config('app.url') . '/assets/img/brand/email-logo.png',
    'logo_width' => 120,
    'logo_alt' => 'Sokoli',

    // Barvy (odpovídá club-default preset v branding.php)
    'colors' => [
        'primary' => '#E11D48',   // Red
        'secondary' => '#0B1F3A', // Navy
        'text' => '#0F172A',      // Slate 900
        'muted' => '#64748B',     // Slate 500
        'bg_body' => '#F8FAFC',   // Slate 50
        'bg_content' => '#FFFFFF', // White
        'border' => '#E2E8F0',     // Slate 200
    ],

    'footer_note' => 'Tento e-mail byl odeslán automaticky z informačního systému TJ Sokol Kbely C & E.',
    'copyright' => '© 2026 TJ Sokol Kbely Basketball. Všechna práva vyhrazena.',
];
