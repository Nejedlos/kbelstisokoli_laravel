<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class BrandingService
{
    /**
     * Cache v rámci requestu.
     */
    protected ?array $settings = null;

    /**
     * Získá globální nastavení brandingu.
     */
    public function getSettings(): array
    {
        if ($this->settings !== null) {
            return $this->settings;
        }

        $cfg = config('branding', []);
        $dbSettings = $this->getDbSettings();

        $activeTheme = $dbSettings['theme_preset'] ?? $cfg['default_theme'];
        $themeConfig = $cfg['themes'][$activeTheme] ?? $cfg['themes'][$cfg['default_theme']];

        $this->settings = [
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
            'header_variant' => $dbSettings['header_variant'] ?? 'light',
            'footer_variant' => $dbSettings['footer_variant'] ?? 'full',
            'button_radius' => $dbSettings['button_radius'] ?? 'md',
            'seo' => [
                'title_suffix' => $dbSettings['seo_title_suffix'] ?? $cfg['seo_title_suffix'] ?? null,
                'description' => $dbSettings['seo_description'] ?? $cfg['seo_description'] ?? null,
                'og_image_path' => $dbSettings['seo_og_image_path'] ?? $cfg['seo_og_image_path'] ?? null,
                'robots_index' => filter_var($dbSettings['seo_robots_index'] ?? true, FILTER_VALIDATE_BOOLEAN),
                'robots_follow' => filter_var($dbSettings['seo_robots_follow'] ?? true, FILTER_VALIDATE_BOOLEAN),
            ],
            'main_club_url' => $dbSettings['main_club_url'] ?? $cfg['main_club_url'] ?? 'https://www.basketkbely.cz/',
            'recruitment_url' => $dbSettings['recruitment_url'] ?? $cfg['recruitment_url'] ?? 'https://www.basketkbely.cz/nabor',
            'venue' => [
                'name' => $dbSettings['venue_name'] ?? null,
                'street' => $dbSettings['venue_street'] ?? null,
                'city' => $dbSettings['venue_city'] ?? null,
                'gps' => $dbSettings['venue_gps'] ?? null,
                'map_url' => $dbSettings['venue_map_url'] ?? null,
            ],
            'match_day' => $dbSettings['match_day'] ?? null,
            'public_contact' => [
                'person' => $dbSettings['contact_person'] ?? null,
                'role' => $dbSettings['contact_role'] ?? null,
                'street' => $dbSettings['contact_street'] ?? null,
                'city' => $dbSettings['contact_city'] ?? null,
                'phone' => $dbSettings['contact_phone'] ?? null, // Use the one already in dbSettings
                'fax' => $dbSettings['contact_fax'] ?? null,
                'email' => $dbSettings['contact_email'] ?? null, // Use the one already in dbSettings
            ],
            'maintenance_mode' => filter_var($dbSettings['maintenance_mode'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'maintenance_title' => $dbSettings['maintenance_title'] ?? __('Trenér právě kreslí vítěznou taktiku pro náš nový web.'),
            'maintenance_text' => $dbSettings['maintenance_text'] ?? __('Vzali jsme si oddechový čas, abychom do nového webu dostali všechny ty smeče a trojky, které si zasloužíte. Dejte nám chvilku na střídačce, brzy se vrátíme do hry v plné sestavě!'),
        ];

        return $this->settings;
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
            '--color-brand-navy-rgb' => $this->hexToRgb($colors['navy']),
            '--color-brand-blue' => $colors['blue'],
            '--color-brand-blue-rgb' => $this->hexToRgb($colors['blue']),
            '--color-brand-red' => $colors['red'],
            '--color-brand-red-rgb' => $this->hexToRgb($colors['red']),
            '--color-brand-white' => $colors['white'],
            '--color-brand-white-rgb' => $this->hexToRgb($colors['white']),
            '--color-ui-bg' => $colors['bg'],
            '--color-ui-surface' => $colors['surface'],
            '--color-ui-surface-alt' => $colors['surface_alt'],
            '--color-ui-border' => $colors['border'],
            '--color-ui-text' => $colors['text'],
            '--color-ui-text-muted' => $colors['text_muted'],
            '--color-primary' => $colors['red'], // Výchozí primární je červená
            '--color-primary-rgb' => $this->hexToRgb($colors['red']),
        ];

        $css = ":root {\n";
        foreach ($vars as $key => $value) {
            $css .= "    {$key}: {$value};\n";
        }
        $css .= "}";

        return $css;
    }

    /**
     * Převede HEX barvu na RGB formát (pro použití s opacitou v CSS).
     */
    protected function hexToRgb(string $hex): string
    {
        $hex = str_replace('#', '', $hex);
        if (strlen($hex) === 3) {
            $r = hexdec(substr($hex, 0, 1) . substr($hex, 0, 1));
            $g = hexdec(substr($hex, 1, 1) . substr($hex, 1, 1));
            $b = hexdec(substr($hex, 2, 1) . substr($hex, 2, 1));
        } else {
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));
        }
        return "{$r}, {$g}, {$b}";
    }

    /**
     * Načte všechna nastavení z DB a nacachuje je.
     */
    protected function getDbSettings(): array
    {
        try {
            // Pokud jsme v konzoli a běží příkaz, který by neměl sahat do DB (např. package:discover)
            // nebo pokud soubor s SQLite databází neexistuje, vrátíme prázdné pole.
            if (app()->runningInConsole()) {
                $dbConnection = config('database.default');
                $dbConfig = config("database.connections.{$dbConnection}");

                if (($dbConfig['driver'] ?? '') === 'sqlite') {
                    $database = $dbConfig['database'] ?? '';
                    // V CI prostředí nemusí absolutní cesta k DB existovat při buildu/lintu
                    if ($database !== ':memory:' && !empty($database) && !file_exists($database)) {
                        return [];
                    }
                }
            }

            return Cache::remember('global_branding_settings_' . app()->getLocale(), 3600, function () {
                if (!Schema::hasTable('settings')) {
                    return [];
                }

                $settings = Setting::all();
                $mapped = [];
                foreach ($settings as $setting) {
                    $mapped[$setting->key] = $setting->value;
                }
                return $mapped;
            });
        } catch (\Throwable $e) {
            // Bezpečný fallback v případě jakékoliv chyby (např. chybějící tabulka cache nebo settings)
            return [];
        }
    }

    /**
     * Vymaže cache nastavení pro všechny podporované jazyky.
     */
    public function clearCache(): void
    {
        Cache::forget('global_branding_settings_cs');
        Cache::forget('global_branding_settings_en');
    }

    /**
     * Nahradí zástupné symboly (hashe) v textu skutečnými hodnotami z brandingu.
     */
    public function replacePlaceholders(?string $text): string
    {
        if (!$text) {
            return '';
        }

        $settings = $this->getSettings();

        $placeholders = [
            '###TEAM_NAME###' => $settings['club_name'],
            '###TEAM_SHORT###' => $settings['club_short_name'],
            '###CLUB_NAME###' => $settings['club_name'],
        ];

        return str_replace(array_keys($placeholders), array_values($placeholders), $text);
    }

    /**
     * Rekurzivně nahradí zástupné symboly v celém poli (např. v datech bloku).
     */
    public function replaceInArray(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $data[$key] = $this->replacePlaceholders($value);
            } elseif (is_array($value)) {
                $data[$key] = $this->replaceInArray($value);
            }
        }

        return $data;
    }
}
