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
            'canonical' => $canonical,
            'robots' => $robots,
            'og_title' => $ogTitle,
            'og_description' => $ogDescription,
            'og_image' => $ogImage,
            'og_type' => $this->resolveOgType($model),
            'twitter_card' => $seo->twitter_card ?? 'summary_large_image',
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

    protected function resolveOgImage(?SeoMetadata $seo, ?Model $model, array $settings): ?string
    {
        if ($seo && $seo->og_image) {
            return asset('storage/' . $seo->og_image);
        }

        if ($model && isset($model->featured_image) && $model->featured_image) {
            return asset('storage/' . $model->featured_image);
        }

        if (isset($settings['seo_og_image_path']) && $settings['seo_og_image_path']) {
            return asset('storage/' . $settings['seo_og_image_path']);
        }

        return $settings['logo_path'] ? asset('storage/' . $settings['logo_path']) : null;
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
        $data[] = [
            '@context' => 'https://schema.org',
            '@type' => 'SportsOrganization',
            'name' => $settings['club_name'] ?? 'Kbelští sokoli',
            'url' => url('/'),
            'logo' => $settings['logo_path'] ? asset('storage/' . $settings['logo_path']) : null,
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => $settings['contact']['address'] ?? '',
            ],
            'contactPoint' => [
                '@type' => 'ContactPoint',
                'telephone' => $settings['contact']['phone'] ?? '',
                'contactType' => 'customer service',
                'email' => $settings['contact']['email'] ?? '',
            ],
            'sameAs' => array_filter([
                $settings['socials']['facebook'] ?? null,
                $settings['socials']['instagram'] ?? null,
                $settings['socials']['youtube'] ?? null,
            ]),
        ];

        // Article if post
        if ($model instanceof \App\Models\Post) {
            $data[] = [
                '@context' => 'https://schema.org',
                '@type' => 'NewsArticle',
                'headline' => $model->title,
                'image' => $model->featured_image ? [asset('storage/' . $model->featured_image)] : [],
                'datePublished' => $model->publish_at?->toIso8601String() ?? $model->created_at->toIso8601String(),
                'dateModified' => $model->updated_at->toIso8601String(),
                'author' => [
                    '@type' => 'Organization',
                    'name' => $settings['club_name'] ?? 'Kbelští sokoli',
                ],
            ];
        }

        // Custom override
        if ($seo && $seo->structured_data_override) {
            $data = array_merge($data, $seo->structured_data_override);
        }

        return $data;
    }
}
