<?php

namespace App\Services;

use App\Models\SeoMetadata;
use App\Models\Setting;
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

        $robots = ($index ? 'index' : 'noindex') . ',' . ($follow ? 'follow' : 'nofollow');

        // OpenGraph & Twitter
        $ogTitle = $seo->og_title ?? $seo->title ?? ($model->title ?? $siteName);
        $ogDescription = $seo->og_description ?? $seo->description ?? $description;
        $ogImage = $this->resolveOgImage($seo, $model, $settings);

        return [
            'title' => $title,
            'description' => $description,
            'keywords' => $this->resolveKeywords($seo, $settings),
            'canonical' => $canonical,
            'robots' => $robots,
            'og_title' => $ogTitle,
            'og_description' => $ogDescription,
            'og_image' => $ogImage,
            'og_type' => $this->resolveOgType($model),
            'og_locale' => $this->resolveOgLocale(),
            'twitter_card' => $seo->twitter_card ?? 'summary_large_image',
            'twitter_image_alt' => $siteName,
            'site_name' => $siteName,
            'structured_data' => $this->generateStructuredData($model, $seo, $settings),
        ];
    }

    protected function resolveTitle(?SeoMetadata $seo, ?Model $model, string $siteName, string $titleSuffix): string
    {
        $title = $seo->title ?? $model->title ?? $siteName;

        if ($title === $siteName) {
            return $title;
        }

        // Pokud titulek již obsahuje název klubu, nepřidáváme suffix
        if (Str::contains($title, $siteName) || Str::contains($title, 'Sokol Kbely')) {
            return $title;
        }

        return $title . $titleSuffix;
    }

    protected function resolveDescription(?SeoMetadata $seo, ?Model $model, array $settings): string
    {
        if ($seo && $seo->description) {
            return $seo->description;
        }

        if ($model && isset($model->excerpt) && $model->excerpt) {
            return Str::limit(strip_tags($model->excerpt), 160);
        }

        if ($model && isset($model->content)) {
            $content = is_array($model->content) ? json_encode($model->content) : $model->content;
            return Str::limit(strip_tags($content), 160);
        }

        return $settings['seo_description'] ?? '';
    }

    protected function resolveKeywords(?SeoMetadata $seo, array $settings): string
    {
        if ($seo && $seo->keywords) {
            return $seo->keywords;
        }

        return $settings['seo_keywords'] ?? '';
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

        // Organization
        $org = [
            '@context' => 'https://schema.org',
            '@type' => 'SportsOrganization',
            'name' => $settings['club_name'] ?? 'Kbelští sokoli',
            'url' => url('/'),
            'logo' => $settings['logo_path'] ? web_asset($settings['logo_path']) : null,
        ];

        // Address (jen pokud existují smysluplná data)
        $address = [
            '@type' => 'PostalAddress',
            'streetAddress' => $settings['contact']['address'] ?? null,
        ];
        $address = $this->cleanSchema($address);
        if (!empty($address)) {
            $org['address'] = $address;
        }

        // ContactPoint (jen pokud existují data)
        $contact = [
            '@type' => 'ContactPoint',
            'telephone' => $settings['contact']['phone'] ?? null,
            'contactType' => 'customer service',
            'email' => $settings['contact']['email'] ?? null,
        ];
        $contact = $this->cleanSchema($contact);
        if (!empty($contact)) {
            $org['contactPoint'] = $contact;
        }

        $sameAs = array_filter([
            $settings['socials']['facebook'] ?? null,
            $settings['socials']['instagram'] ?? null,
            $settings['socials']['youtube'] ?? null,
        ]);
        if (!empty($sameAs)) {
            $org['sameAs'] = $sameAs;
        }

        $data[] = $this->cleanSchema($org);

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
                    'name' => $settings['club_name'] ?? 'Kbelští sokoli',
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
        return array_values(array_filter($data, fn ($item) => !empty($item)));
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
