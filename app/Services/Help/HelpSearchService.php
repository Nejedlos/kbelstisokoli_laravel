<?php

namespace App\Services\Help;

use App\Models\HelpArticle;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class HelpSearchService
{
    protected array $roles = [];

    /**
     * Nastaví role pro filtrování vyhledávání.
     *
     * @param array|string $roles
     * @return $this
     */
    public function forAudience(array|string $roles): self
    {
        $this->roles = (array) $roles;
        return $this;
    }

    /**
     * Zjistí, zda má uživatel roli administrátora.
     */
    protected function isAdmin(): bool
    {
        return in_array('admin', $this->roles);
    }

    /**
     * Vrátí role pro filtrování (pro admina prázdné, aby viděl vše).
     */
    protected function getFilteringRoles(): array
    {
        return $this->isAdmin() ? [] : $this->roles;
    }

    /**
     * Vyhledá články podle zadaného dotazu s relevance rankingem.
     *
     * @param string $query
     * @param int $limit
     * @return \Illuminate\Support\Collection
     */
    public function search(string $query, int $limit = 20): \Illuminate\Support\Collection
    {
        $query = trim($query);

        if (empty($query)) {
            return collect();
        }

        $locale = app()->getLocale();
        $filteringRoles = $this->getFilteringRoles();

        $rolesHash = md5(json_encode($this->roles));
        $cacheKey = "help_search_{$rolesHash}_{$locale}_" . md5($query) . "_{$limit}";

        return \Illuminate\Support\Facades\Cache::remember($cacheKey, now()->addHour(), function () use ($query, $limit, $locale, $filteringRoles) {
            // Jednoduchý relevance ranking pomocí SQL (funguje v SQLite i MySQL)
            // Váhy: Title (10), Keywords (8), Purpose (5), Content (3)
            return \DB::table('help_articles')
                ->select(['id', 'slug', 'title', 'excerpt', 'metadata', 'audience_roles', 'is_featured', 'content', 'search_keywords'])
                ->selectRaw("
                    (CASE WHEN JSON_EXTRACT(title, '$.\"{$locale}\"') LIKE ? THEN 10 ELSE 0 END +
                     CASE WHEN JSON_EXTRACT(search_keywords, '$.\"{$locale}\"') LIKE ? THEN 8 ELSE 0 END +
                     CASE WHEN JSON_EXTRACT(metadata, '$.\"{$locale}\".purpose') LIKE ? THEN 5 ELSE 0 END +
                     CASE WHEN JSON_EXTRACT(content, '$.\"{$locale}\"') LIKE ? THEN 3 ELSE 0 END)
                    AS relevance
                ", [
                    "%" . mb_strtolower($query) . "%",
                    "%" . mb_strtolower($query) . "%",
                    "%" . mb_strtolower($query) . "%",
                    "%" . mb_strtolower($query) . "%",
                ])
                ->where('is_published', true)
                ->where(function ($q) {
                    $q->whereNull('published_at')->orWhere('published_at', '<=', now());
                })
                ->where(function ($q) use ($query, $locale) {
                    $term = "%" . mb_strtolower($query) . "%";
                    $q->whereRaw("JSON_EXTRACT(title, '$.\"{$locale}\"') LIKE ?", [$term])
                        ->orWhereRaw("JSON_EXTRACT(search_keywords, '$.\"{$locale}\"') LIKE ?", [$term])
                        ->orWhereRaw("JSON_EXTRACT(metadata, '$.\"{$locale}\".purpose') LIKE ?", [$term])
                        ->orWhereRaw("JSON_EXTRACT(content, '$.\"{$locale}\"') LIKE ?", [$term]);
                })
                ->when(!empty($filteringRoles), function ($q) use ($filteringRoles) {
                    $q->where(function ($inner) use ($filteringRoles) {
                        $inner->whereNull('audience_roles');
                        foreach ($filteringRoles as $role) {
                            // SQLite v testech má problém s whereJsonContains v subquery
                            if (config('database.default') === 'sqlite') {
                                $inner->orWhere('audience_roles', 'LIKE', '%"' . $role . '"%');
                            } else {
                                $inner->orWhereJsonContains('audience_roles', $role);
                            }
                        }
                    });
                })
                ->orderByDesc('relevance')
                ->orderByRaw("JSON_EXTRACT(title, '$.\"{$locale}\"') ASC")
                ->limit($limit)
                ->get()
                ->map(function ($article) use ($query, $locale) {
                    $title = json_decode($article->title, true) ?: [];
                    $article->title_str = $title[$locale] ?? ($title['cs'] ?? ($title['en'] ?? 'Untitled'));

                    $article->search_excerpt = $this->generateExcerpt($article, $query, $locale);
                    $article->audience_roles = json_decode($article->audience_roles, true) ?: [];
                    $article->is_featured = (bool) $article->is_featured;

                    return $article;
                });
        });
    }

    /**
     * Vygeneruje úryvek textu pro výsledek vyhledávání.
     */
    protected function generateExcerpt(object $article, string $query, string $locale): string
    {
        $contentArr = json_decode($article->content, true) ?: [];
        $content = strip_tags($contentArr[$locale] ?? ($contentArr['cs'] ?? ($contentArr['en'] ?? '')));

        $meta = json_decode($article->metadata, true) ?: [];
        $purpose = $meta[$locale]['purpose'] ?? ($meta['cs']['purpose'] ?? '');

        if (empty($query)) {
            return $purpose ?: mb_substr($content, 0, 160) . '...';
        }

        // Pokud je shoda v purpose, preferujeme ho
        if (mb_stripos($purpose, $query) !== false) {
            return $this->highlight($purpose, $query);
        }

        // Hledáme v obsahu
        $pos = mb_stripos($content, $query);
        if ($pos !== false) {
            $start = max(0, $pos - 80);
            $excerpt = mb_substr($content, $start, 160);
            $prefix = $start > 0 ? '...' : '';
            $suffix = (mb_strlen($content) > ($start + 160)) ? '...' : '';

            return $prefix . $this->highlight($excerpt, $query) . $suffix;
        }

        // Fallback na purpose nebo začátek obsahu
        return $purpose ?: mb_substr($content, 0, 160) . '...';
    }

    /**
     * Zvýrazní hledaný výraz v textu.
     */
    protected function highlight(string $text, string $query): string
    {
        if (empty($query)) return $text;

        return preg_replace(
            '/(' . preg_quote($query, '/') . ')/i',
            '<mark class="bg-primary-100 text-primary-900 px-1 rounded-sm">$1</mark>',
            $text
        );
    }
}
