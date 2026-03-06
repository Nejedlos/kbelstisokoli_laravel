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
     * Cache pro vygenerované CSS proměnné.
     */
    protected ?string $cachedCssVariables = null;

    /**
     * Cache pro DB nastavení (aby se nevolal Cache::remember víckrát v requestu).
     */
    protected ?array $dbSettings = null;

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

        $activeTheme = $dbSettings['theme_preset'] ?? $cfg['default_theme'] ?? 'club-default';
        $themeConfig = $cfg['themes'][$activeTheme] ?? $cfg['themes'][$cfg['default_theme'] ?? 'club-default'] ?? ['colors' => []];

        $this->settings = [
            'club_name' => $dbSettings['club_name'] ?? $cfg['club_name'] ?? 'Kbelští sokoli',
            'club_short_name' => $dbSettings['club_short_name'] ?? $cfg['club_short_name'] ?? 'Sokoli',
            'slogan' => $dbSettings['slogan'] ?? $cfg['slogan'] ?? null,
            'logo_path' => $dbSettings['logo_path'] ?? $cfg['logo_path'] ?? null,
            'alt_logo_path' => $dbSettings['alt_logo_path'] ?? $cfg['alt_logo_path'] ?? null,
            'theme_preset' => $activeTheme,
            'colors' => $themeConfig['colors'] ?? [],
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
                'enabled' => filter_var($dbSettings['cta_enabled'] ?? $cfg['default_cta']['enabled'] ?? false, FILTER_VALIDATE_BOOLEAN),
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
                'phone' => $dbSettings['contact_phone'] ?? null,
                'fax' => $dbSettings['contact_fax'] ?? null,
                'email' => $dbSettings['contact_email'] ?? null,
            ],
            'maintenance_mode' => filter_var($dbSettings['maintenance_mode'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'maintenance_title' => $dbSettings['maintenance_title'] ?? __('Trenér právě kreslí vítěznou taktiku pro náš nový web.'),
            'maintenance_text' => $dbSettings['maintenance_text'] ?? __('Vzali jsme si oddechový čas, abychom do nového webu dostali všechny ty smeče a trojky, které si zasloužíte. Dejte nám chvilku na střídačce, brzy se vrátíme do hry v plné sestavě!'),
            'admin_contact' => [
                'email' => $dbSettings['admin_contact_email'] ?? null,
                'name' => $dbSettings['admin_contact_name'] ?? null,
                'phone' => $dbSettings['admin_contact_phone'] ?? null,
                'photo_path' => $dbSettings['admin_contact_photo_path'] ?? null,
            ],
            'economy' => [
                'bank_account' => $dbSettings['bank_account'] ?? null,
                'bank_name' => $dbSettings['bank_name'] ?? null,
            ],
            'team_logo' => [
                'enabled_header' => filter_var($dbSettings['team_logo_enabled_header'] ?? true, FILTER_VALIDATE_BOOLEAN),
                'enabled_hero' => filter_var($dbSettings['team_logo_enabled_hero'] ?? true, FILTER_VALIDATE_BOOLEAN),
                'enabled_hero_watermark' => filter_var($dbSettings['team_logo_enabled_hero_watermark'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'enabled_team_cards' => filter_var($dbSettings['team_logo_enabled_team_cards'] ?? true, FILTER_VALIDATE_BOOLEAN),
                'enabled_recruitment_cards' => filter_var($dbSettings['team_logo_enabled_recruitment_cards'] ?? true, FILTER_VALIDATE_BOOLEAN),
                'enabled_footer' => filter_var($dbSettings['team_logo_enabled_footer'] ?? true, FILTER_VALIDATE_BOOLEAN),
                'enabled_match_cards' => filter_var($dbSettings['team_logo_enabled_match_cards'] ?? true, FILTER_VALIDATE_BOOLEAN),
                'enabled_match_detail' => filter_var($dbSettings['team_logo_enabled_match_detail'] ?? true, FILTER_VALIDATE_BOOLEAN),
                'enabled_page_headers' => filter_var($dbSettings['team_logo_enabled_page_headers'] ?? true, FILTER_VALIDATE_BOOLEAN),

                'sizes' => [
                    'header_desktop' => (int) ($dbSettings['team_logo_size_header_desktop'] ?? 40),
                    'header_mobile' => (int) ($dbSettings['team_logo_size_header_mobile'] ?? 32),
                    'hero' => (int) ($dbSettings['team_logo_size_hero'] ?? 88),
                    'team_card' => (int) ($dbSettings['team_logo_size_team_card'] ?? 44),
                    'recruitment_card' => (int) ($dbSettings['team_logo_size_recruitment_card'] ?? 44),
                    'footer' => (int) ($dbSettings['team_logo_size_footer'] ?? 56),
                    'match_card' => (int) ($dbSettings['team_logo_size_match_card'] ?? 36),
                    'match_detail' => (int) ($dbSettings['team_logo_size_match_detail'] ?? 56),
                    'page_header' => (int) ($dbSettings['team_logo_size_page_header'] ?? 40),
                ],
                'hero_opacity' => (float) ($dbSettings['team_logo_hero_opacity'] ?? 1.0),
                'watermark_opacity' => (float) ($dbSettings['team_logo_watermark_opacity'] ?? 0.08),
                'border_radius' => $dbSettings['team_logo_border_radius'] ?? 'none',
                'shadow_enabled' => filter_var($dbSettings['team_logo_shadow_enabled'] ?? false, FILTER_VALIDATE_BOOLEAN),

                'paths' => $cfg['team_logos'] ?? [],
            ],
            'parent_logo' => [
                'paths' => $cfg['parent_logos'] ?? [],
                'sizes' => [
                    'footer' => 28,
                    'card_badge' => 44,
                ],
            ],
            'partners' => [
                'enabled' => filter_var($dbSettings['partners_enabled'] ?? $cfg['partners']['enabled'] ?? true, FILTER_VALIDATE_BOOLEAN),
                'homepage_strip_enabled' => filter_var($dbSettings['partners_homepage_strip_enabled'] ?? $cfg['partners']['homepage_strip_enabled'] ?? true, FILTER_VALIDATE_BOOLEAN),
                'footer_enabled' => filter_var($dbSettings['partners_footer_enabled'] ?? $cfg['partners']['footer_enabled'] ?? true, FILTER_VALIDATE_BOOLEAN),
                'match_badge_enabled' => filter_var($dbSettings['partners_match_badge_enabled'] ?? $cfg['partners']['match_badge_enabled'] ?? true, FILTER_VALIDATE_BOOLEAN),
                'contact_enabled' => filter_var($dbSettings['partners_contact_enabled'] ?? $cfg['partners']['contact_enabled'] ?? true, FILTER_VALIDATE_BOOLEAN),
                'recruitment_enabled' => filter_var($dbSettings['partners_recruitment_enabled'] ?? $cfg['partners']['recruitment_enabled'] ?? true, FILTER_VALIDATE_BOOLEAN),
                'logo_width_desktop' => (int) ($dbSettings['partner_logo_width_desktop'] ?? $cfg['partners']['logo_width_desktop'] ?? 180),
                'logo_width_mobile' => (int) ($dbSettings['partner_logo_width_mobile'] ?? $cfg['partners']['logo_width_mobile'] ?? 140),
                'logo_max_height' => (int) ($dbSettings['partner_logo_max_height'] ?? $cfg['partners']['logo_max_height'] ?? 80),
                'section_style' => $dbSettings['partner_section_style'] ?? $cfg['partners']['section_style'] ?? 'logo_with_label',
            ],
        ];

        return $this->settings;
    }

    /**
     * Vygeneruje CSS proměnné pro aktivní téma.
     */
    public function getCssVariables(): string
    {
        if ($this->cachedCssVariables !== null) {
            return $this->cachedCssVariables;
        }

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
        $css .= '}';

        return $this->cachedCssVariables = $css;
    }

    /**
     * Převede HEX barvu na RGB formát (pro použití s opacitou v CSS).
     */
    protected function hexToRgb(string $hex): string
    {
        $hex = str_replace('#', '', $hex);
        if (strlen($hex) === 3) {
            $r = hexdec(substr($hex, 0, 1).substr($hex, 0, 1));
            $g = hexdec(substr($hex, 1, 1).substr($hex, 1, 1));
            $b = hexdec(substr($hex, 2, 1).substr($hex, 2, 1));
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
        if ($this->dbSettings !== null) {
            return $this->dbSettings;
        }

        try {
            // Rychlá kontrola existence DB v konzoli (např. při package:discover)
            if (app()->runningInConsole()) {
                $dbConnection = config('database.default');
                $dbConfig = config("database.connections.{$dbConnection}");

                if (($dbConfig['driver'] ?? '') === 'sqlite') {
                    $database = $dbConfig['database'] ?? '';
                    if ($database !== ':memory:' && ! empty($database) && ! file_exists($database)) {
                        return $this->dbSettings = [];
                    }
                }
            }

            $locale = app()->getLocale();

            return $this->dbSettings = Cache::remember("global_branding_settings_{$locale}", 3600, function () {
                // Optimalizované zjištění existence tabulky přes cache (Schema::hasTable je drahé v každém requestu)
                $tableExists = Cache::rememberForever('schema_has_settings_table', function () {
                    return Schema::hasTable('settings');
                });

                if (! $tableExists) {
                    return [];
                }

                // Načteme jen klíče, které BrandingService reálně používá
                $settings = Setting::where('key', 'like', 'branding_%')
                    ->orWhere('key', 'like', 'social_%')
                    ->orWhere('key', 'like', 'contact_%')
                    ->orWhere('key', 'like', 'cta_%')
                    ->orWhere('key', 'like', 'seo_%')
                    ->orWhere('key', 'like', 'maintenance_%')
                    ->orWhere('key', 'like', 'venue_%')
                    ->orWhere('key', 'like', 'club_%')
                    ->orWhere('key', 'like', 'team_logo_%')
                    ->orWhere('key', 'like', 'admin_contact_%')
                    ->orWhere('key', 'like', 'partners_%')
                    ->orWhere('key', 'like', 'partner_%')
                    ->orWhereIn('key', [
                        'slogan', 'logo_path', 'alt_logo_path', 'main_club_url', 'recruitment_url',
                        'match_day', 'header_variant', 'footer_variant', 'button_radius', 'footer_text', 'theme_preset',
                        'bank_account', 'bank_name',
                    ])
                    ->get(['key', 'value']);

                $mapped = [];
                foreach ($settings as $setting) {
                    $mapped[$setting->key] = $setting->value;
                }

                return $mapped;
            });
        } catch (\Throwable $e) {
            // Bezpečný fallback v případě jakékoliv chyby
            return $this->dbSettings = [];
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
        if (! $text) {
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
