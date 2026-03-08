<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

/**
 * Seeder pro globální nastavení brandingu a systému.
 * Zajišťuje, aby základní údaje (název klubu, kontakty, bankovní spojení) byly vždy vyplněny.
 */
class BrandingSeeder extends Seeder
{
    /**
     * Spustí seeder.
     */
    public function run(): void
    {
        $currentYear = (int) date('Y');
        $yearRange = ($currentYear > 2026) ? "2026–{$currentYear}" : "2026";
        $clubName = 'TJ Sokol Kbely C & E';

        $settings = [
            // Identita klubu
            'club_name' => $clubName,
            'club_short_name' => 'Sokoli',
            'slogan' => 'Více než jen basketbal.',
            'logo_path' => 'branding/logo_kbelsti_sokoli_velke.png',
            'alt_logo_path' => 'branding/tj_sokol_kbely_basketball_logo_velke.png',

            // Design a UI
            'theme_preset' => 'club-default',
            'header_variant' => 'light',
            'footer_variant' => 'full',
            'button_radius' => 'md',

            // Hlavní kontakty
            'contact_email' => 'spanily@pro-nemo.cz',
            'contact_phone' => '+420 602 285 447',
            'contact_address' => 'Toužimská 700, Praha 9 - Kbely',

            // Kontakty pro veřejnost (v patičce)
            'contact_person' => 'Tomáš Spanilý',
            'contact_role' => 'vedoucí týmu',
            'contact_street' => '',
            'contact_city' => 'Praha 9',
            'contact_fax' => '',

            // Administrační kontakt (např. pro technické záležitosti)
            'admin_contact_email' => 'nejedlymi@gmail.com',
            'admin_contact_name' => 'Michal Nejedlý',
            'admin_contact_phone' => '+420 777220966',

            // Globální odkazy
            'main_club_url' => 'https://www.basketkbely.cz/',
            'recruitment_url' => 'https://www.basketkbely.cz/zacnihrat',

            // Sociální sítě
            'social_facebook' => '',
            'social_instagram' => '',
            'social_youtube' => '',

            // Ekonomické údaje (Kritické pro platby)
            'bank_account' => '6022854477/6363',
            'bank_name' => 'Partners banka a.s.',

            // Hlavní hala a hrací termíny
            'venue_name' => 'RumcajsArena',
            'venue_street' => 'Třinecká 650',
            'venue_city' => 'Letňany',
            'venue_gps' => "50°8'2.97\"N, 14°30'37.31\"E",
            'venue_map_url' => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2557.423791906335!2d14.508026677153266!3d50.134503371533754!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x470bed3fb2d307c7%3A0x80b9ba0fd7daac96!2zVMWZaW5lY2vDoSA2NTAsIDE5OSAwMCBMZXTFiGFueQ!5e0!3m2!1scs!2scz!4v1772030905282!5m2!1scs!2scz',
            'match_day' => 'Středa 19:15 nebo 20:15',

            // SEO a Právní informace
            'footer_text' => "© {$yearRange} {$clubName}. Všechna práva vyhrazena.",
            'seo_description' => 'Oficiální web basketbalového klubu Kbelští sokoli. Informace o týmech, trénincích, zápasech a náborech pro děti i dospělé v Praze 9.',
            'seo_title_suffix' => ' | Kbelští sokoli',
            'seo_robots_index' => true,
            'seo_robots_follow' => true,

            // Stav webu
            'maintenance_mode' => false,

            // Výkonnostní nastavení
            'perf_scenario' => 'aggressive',
            'perf_full_page_cache' => false,
            'perf_fragment_cache' => true,
            'perf_html_minification' => true,
            'perf_livewire_navigate' => true,
            'perf_lazy_load_images' => true,

            // Týmový branding (Kbelští sokoli C & E)
            'team_logo_enabled_header' => true,
            'team_logo_enabled_hero' => true,
            'team_logo_enabled_hero_watermark' => false,
            'team_logo_enabled_team_cards' => true,
            'team_logo_enabled_recruitment_cards' => true,
            'team_logo_enabled_footer' => true,
            'team_logo_enabled_match_cards' => true,
            'team_logo_enabled_match_detail' => true,
            'team_logo_enabled_page_headers' => true,

            'team_logo_size_header_desktop' => 48,
            'team_logo_size_header_mobile' => 38,
            'team_logo_size_hero' => 88,
            'team_logo_size_team_card' => 44,
            'team_logo_size_recruitment_card' => 44,
            'team_logo_size_footer' => 56,
            'team_logo_size_match_card' => 36,
            'team_logo_size_match_detail' => 56,
            'team_logo_size_page_header' => 48,

            'team_logo_hero_opacity' => 1.0,
            'team_logo_watermark_opacity' => 0.08,
            'team_logo_border_radius' => 'none',
            'team_logo_shadow_enabled' => false,

            // Nastavení partnerů
            'partners_enabled' => true,
            'partners_homepage_strip_enabled' => true,
            'partners_footer_enabled' => true,
            'partners_match_badge_enabled' => true,
            'partners_contact_enabled' => true,
            'partners_recruitment_enabled' => true,
            'partner_logo_width_desktop' => 180,
            'partner_logo_width_mobile' => 140,
            'partner_logo_max_height' => 80,
            'partner_section_style' => 'logo_with_label',
        ];

        foreach ($settings as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        $this->command->info('Branding nastavení bylo úspěšně seedováno.');
    }
}
