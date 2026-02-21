<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class BrandingService
{
    /**
     * Získá globální nastavení brandingu.
     */
    public function getSettings(): array
    {
        $cfg = config('branding', []);
        $dbSettings = $this->getDbSettings();

        $activeTheme = $dbSettings['theme_preset'] ?? $cfg['default_theme'];
        $themeConfig = $cfg['themes'][$activeTheme] ?? $cfg['themes'][$cfg['default_theme']];

        return [
            'club_name' => $dbSettings['club_name'] ?? $cfg['club_name'] ?? 'Kbelští sokoli',
            'club_short_name' => $dbSettings['club_short_name'] ?? $cfg['club_short_name'] ?? 'Sokoli',
            'slogan' => $dbSettings['slogan'] ?? $cfg['slogan'] ?? null,
            'logo_path' => $dbSettings['logo_path'] ?? $cfg['logo_path'] ?? null,
            'alt_logo_path' => $dbSettings['alt_logo_path'] ?? $cfg['alt_logo_path'] ?? null,
            'theme_preset' => $activeTheme,
            'colors' => $themeConfig['colors'],
            'contact' => [
                'email' => $dbSettings['contact_email'] ?? $cfg['contact']['email'] ?? null,
                'phone' => $dbSettings['contact_phone'] ?? $cfg['contact']['phone'] ?? null,
                'address' => $dbSettings['contact_address'] ?? $cfg['contact']['address'] ?? null,
            ],
            'socials' => [
                'facebook' => $dbSettings['social_facebook'] ?? $cfg['socials']['facebook'] ?? null,
                'instagram' => $dbSettings['social_instagram'] ?? $cfg['socials']['instagram'] ?? null,
                'youtube' => $dbSettings['social_youtube'] ?? $cfg['socials']['youtube'] ?? null,
            ],
            'default_cta' => [
                'enabled' => filter_var($dbSettings['cta_enabled'] ?? $cfg['default_cta']['enabled'], FILTER_VALIDATE_BOOLEAN),
                'label' => $dbSettings['cta_label'] ?? $cfg['default_cta']['label'] ?? null,
                'url' => $dbSettings['cta_url'] ?? $cfg['default_cta']['url'] ?? null,
            ],
            'footer_text' => $dbSettings['footer_text'] ?? $cfg['footer_text'] ?? null,
        ];
    }

    /**
     * Vygeneruje CSS proměnné pro aktivní téma.
     */
    public function getCssVariables(): string
    {
        $settings = $this->getSettings();
        $colors = $settings['colors'];

        $vars = [
            '--color-brand-navy' => $colors['navy'],
            '--color-brand-blue' => $colors['blue'],
            '--color-brand-red' => $colors['red'],
            '--color-brand-white' => $colors['white'],
            '--color-ui-bg' => $colors['bg'],
            '--color-ui-surface' => $colors['surface'],
            '--color-ui-surface-alt' => $colors['surface_alt'],
            '--color-ui-border' => $colors['border'],
            '--color-ui-text' => $colors['text'],
            '--color-ui-text-muted' => $colors['text_muted'],
            '--color-primary' => $colors['red'], // Výchozí primární je červená
        ];

        $css = ":root {\n";
        foreach ($vars as $key => $value) {
            $css .= "    {$key}: {$value};\n";
        }
        $css .= "}";

        return $css;
    }

    /**
     * Načte všechna nastavení z DB a nacachuje je.
     */
    protected function getDbSettings(): array
    {
        return Cache::remember('global_branding_settings', 3600, function () {
            return Setting::pluck('value', 'key')->toArray();
        });
    }

    /**
     * Vymaže cache nastavení.
     */
    public function clearCache(): void
    {
        Cache::forget('global_branding_settings');
    }
}
