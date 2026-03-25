<?php

namespace App\Services;

use App\Models\SeoMetadata;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;

class SeoService
{
    protected BrandingService $brandingService;

    public function __construct(BrandingService $brandingService)
    {
        $this->brandingService = $brandingService;
    }

    /**
     * Získá SEO metadata pro daný model nebo globální výchozí hodnoty.
     */
    public function getMetadata(?Model $model = null): array
    {
        $settings = $this->brandingService->getSettings();
        $seo = $model && method_exists($model, 'seo') ? $model->seo : null;

        $siteName = $settings['club_name'] ?? 'Kbelští sokoli';
        $titleSuffix = $settings['seo_title_suffix'] ?? " | {$siteName}";

        // Základní metadata
        $title = $this->resolveTitle($seo, $model, $siteName, $titleSuffix);
        $description = $this->resolveDescription($seo, $model, $settings);
        $canonical = $seo->canonical_url ?? Request::url();

        // Robots
        $index = $seo ? $seo->robots_index : (filter_var($settings['seo_robots_index'] ?? true, FILTER_VALIDATE_BOOLEAN));
        $follow = $seo ? $seo->robots_follow : (filter_var($settings['seo_robots_follow'] ?? true, FILTER_VALIDATE_BOOLEAN));

        // Pokud je model draft, vynutíme noindex
        if ($model && isset($model->status) && $model->status !== 'published') {
            $index = false;
        }

        // Pokud jsme v admin nebo member sekci, vynutíme noindex
        if (Request::is('admin*') || Request::is('member*')) {
            $index = false;
        }

        $robots = ($index ? 'index' : 'noindex').','.($follow ? 'follow' : 'nofollow');

        // OpenGraph & Twitter
        $ogTitle = $seo->og_title ?? $seo->title ?? ($model->title ?? $siteName);
        $ogDescription = $seo->og_description ?? $seo->description ?? $description;
        $ogImage = $this->resolveOgImage($seo, $model, $settings);
        $ogImageWidth = 1200;
        $ogImageHeight = 630;

        // Pokud máme lokální soubor, zkusíme zjistit rozměry
        if ($ogImage && str_starts_with($ogImage, url('/'))) {
            $path = public_path(parse_url($ogImage, PHP_URL_PATH));
            if (file_exists($path)) {
                $size = @getimagesize($path);
                if ($size) {
                    $ogImageWidth = $size[0];
                    $ogImageHeight = $size[1];
                }
            }
        }

        return [
            'title' => $title,
            'description' => $description,
            'keywords' => $this->resolveKeywords($seo, $settings),
            'canonical' => $canonical,
            'robots' => $robots,
            'og_title' => $ogTitle,
            'og_description' => $ogDescription,
            'og_image' => $ogImage,
            'og_image_width' => $ogImageWidth,
            'og_image_height' => $ogImageHeight,
            'og_type' => $this->resolveOgType($model),
            'og_locale' => $this->resolveOgLocale(),
            'twitter_card' => $seo->twitter_card ?? 'summary_large_image',
            'twitter_image_alt' => $siteName,
            'twitter_site' => $settings['socials']['twitter'] ?? null,
            'site_name' => $siteName,
            'structured_data' => $this->generateStructuredData($model, $seo, $settings),
        ];
    }

    protected function resolveTitle(?SeoMetadata $seo, ?Model $model, string $siteName, string $titleSuffix): string
    {
        $title = $seo->title ?? $model->title ?? null;

        if (! $title) {
            // Speciální případy pro stránky bez modelu v DB (fallbacky)
            $path = Request::path();
            if ($path === '/' || $path === 'cs' || $path === 'en' || $path === '') {
                $title = 'Kbelští sokoli – Basketbal Praha 9 (Letňany, Kbely)';
            } elseif (Str::contains($path, 'novinky') || Str::contains($path, 'news')) {
                $title = __('nav.news');
            } elseif (Str::contains($path, 'tymy') || Str::contains($path, 'teams')) {
                $title = __('nav.teams');
            } elseif (Str::contains($path, 'galerie') || Str::contains($path, 'gallery')) {
                $title = __('nav.gallery');
            } elseif (Str::contains($path, 'zapasy') || Str::contains($path, 'matches')) {
                $title = __('nav.matches');
            } elseif (Str::contains($path, 'treninky') || Str::contains($path, 'trainings')) {
                $title = __('nav.trainings');
            } elseif (Str::contains($path, 'kontakt') || Str::contains($path, 'contact')) {
                $title = __('nav.contact');
            } elseif (Str::contains($path, 'vysledky-hledani') || Str::contains($path, 'search')) {
                $title = __('nav.search_results') !== 'nav.search_results' ? __('nav.search_results') : 'Výsledky hledání';
            } elseif (Str::contains($path, 'mapa-webu') || Str::contains($path, 'sitemap')) {
                $title = app()->getLocale() === 'cs' ? 'Mapa webu' : 'Sitemap';
            } else {
                $title = $siteName;
            }
        }

        if ($title === 'Kbelští sokoli – Basketbal Praha 9 (Letňany, Kbely)') {
            return $title;
        }

        if ($title === $siteName) {
            return $title.' – Basketbal Praha 9';
        }

        // Pokud titulek již obsahuje název klubu, nepřidáváme suffix
        if (Str::contains($title, $siteName) || Str::contains($title, 'Sokol Kbely')) {
            return $title;
        }

        return $title.' | '.$siteName.' – Basketbal Praha 9';
    }

    protected function resolveDescription(?SeoMetadata $seo, ?Model $model, array $settings): string
    {
        if ($seo && $seo->description) {
            return $seo->description;
        }

        if ($model && isset($model->excerpt) && $model->excerpt) {
            return Str::limit(strip_tags($model->excerpt), 160);
        }

        if ($model && isset($model->description) && $model->description) {
            return Str::limit(strip_tags($model->description), 160);
        }

        if ($model && isset($model->content)) {
            $content = is_array($model->content) ? json_encode($model->content) : $model->content;

            return Str::limit(strip_tags($content), 160);
        }

        return $settings['seo_description'] ?? 'Basketbalový klub Sokol Kbely - tréninky, zápasy a nábor nových členů v Praze 9.';
    }

    protected function resolveKeywords(?SeoMetadata $seo, array $settings): string
    {
        $keywords = [];

        if ($seo && $seo->keywords) {
            $keywords[] = $seo->keywords;
        }

        if (isset($settings['seo_keywords']) && $settings['seo_keywords']) {
            $keywords[] = $settings['seo_keywords'];
        }

        if (empty($keywords)) {
            // Defaultní klíčová slova optimalizovaná pro funnel, pokud nic není nastaveno
            return 'basketbal Kbely, basketbalový klub Praha, nábor dětí basketbal, Sokol Kbely, basketbalová akademie';
        }

        // Spojíme unikátní klíčová slova
        $allKeywords = implode(', ', $keywords);
        $parts = array_map('trim', explode(',', $allKeywords));

        return implode(', ', array_unique(array_filter($parts)));
    }

    protected function resolveOgImage(?SeoMetadata $seo, ?Model $model, array $settings): ?string
    {
        if ($seo && $seo->og_image) {
            return web_asset($seo->og_image);
        }

        if ($model && isset($model->featured_image) && $model->featured_image) {
            return web_asset($model->featured_image);
        }

        if (isset($settings['seo_og_image_path']) && $settings['seo_og_image_path']) {
            return web_asset($settings['seo_og_image_path']);
        }

        // Hardcoded fallback na kvalitní týmový obrázek v assetech
        $fallbacks = [
            'assets/img/home/home-hero.jpg',
            'assets/img/home/kbely-basket-community.jpg',
        ];

        foreach ($fallbacks as $fallback) {
            if (file_exists(public_path($fallback))) {
                return asset($fallback);
            }
        }

        return $settings['logo_path'] ? web_asset($settings['logo_path']) : null;
    }

    protected function resolveOgType(?Model $model): string
    {
        if ($model instanceof \App\Models\Post) {
            return 'article';
        }

        return 'website';
    }

    protected function generateStructuredData(?Model $model, ?SeoMetadata $seo, array $settings): array
    {
        $data = [];

        $siteName = $settings['club_name'] ?? 'Kbelští sokoli';
        $logo = $settings['logo_path'] ? web_asset($settings['logo_path']) : null;
        $url = url('/');

        // Organization
        $org = [
            '@context' => 'https://schema.org',
            '@type' => 'SportsOrganization',
            'name' => $siteName,
            'url' => $url,
            'logo' => $logo,
        ];

        // LocalBusiness (Kbely/Letňany)
        $local = [
            '@context' => 'https://schema.org',
            '@type' => 'LocalBusiness',
            'name' => $siteName,
            'image' => $logo,
            'url' => $url,
            'telephone' => $settings['contact']['phone'] ?? null,
            'email' => $settings['contact']['email'] ?? null,
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => $settings['contact']['address'] ?? 'Praha 9',
                'addressLocality' => 'Praha',
                'postalCode' => '19700',
                'addressCountry' => 'CZ',
            ],
            'geo' => [
                '@type' => 'GeoCoordinates',
                'latitude' => '50.1315',
                'longitude' => '14.5492',
            ],
            'priceRange' => '$$',
        ];

        $sameAs = array_filter([
            $settings['socials']['facebook'] ?? null,
            $settings['socials']['instagram'] ?? null,
            $settings['socials']['youtube'] ?? null,
            'https://www.linkedin.com/company/kbelstisokoli', // Příklad, pokud by byl
        ]);
        if (! empty($sameAs)) {
            $org['sameAs'] = $sameAs;
            $local['sameAs'] = $sameAs;
        }

        $data[] = $this->cleanSchema($org);
        $data[] = $this->cleanSchema($local);

        // Article if post
        if ($model instanceof \App\Models\Post) {
            $article = [
                '@context' => 'https://schema.org',
                '@type' => 'NewsArticle',
                'headline' => $model->title,
                'image' => $model->featured_image ? [web_asset($model->featured_image)] : null,
                'datePublished' => $model->publish_at?->toIso8601String() ?? $model->created_at->toIso8601String(),
                'dateModified' => $model->updated_at->toIso8601String(),
                'author' => [
                    '@type' => 'Organization',
                    'name' => $siteName,
                ],
            ];
            $data[] = $this->cleanSchema($article);
        }

        // Custom override
        if ($seo && $seo->structured_data_override) {
            foreach ((array) $seo->structured_data_override as $schema) {
                $data[] = $this->cleanSchema($schema);
            }
        }

        // Finální očištění prázdných záznamů
        return array_values(array_filter($data, fn ($item) => ! empty($item)));
    }

    protected function resolveOgLocale(): string
    {
        $locale = app()->getLocale();

        return match ($locale) {
            'cs' => 'cs_CZ',
            'en' => 'en_US',
            default => 'cs_CZ',
        };
    }

    /**
     * Rekurzivní očištění JSON-LD od null/""/prázdných polí.
     */
    protected function cleanSchema($value)
    {
        if (is_array($value)) {
            $clean = [];
            foreach ($value as $k => $v) {
                $v = $this->cleanSchema($v);
                if ($v === null) {
                    continue;
                }
                if (is_array($v) && empty($v)) {
                    continue;
                }
                if ($v === '') {
                    continue;
                }
                $clean[$k] = $v;
            }

            return $clean;
        }

        return $value;
    }
}
