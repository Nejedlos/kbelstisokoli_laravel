<?php

namespace App\Services\Help;

use App\Models\HelpArticle;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class HelpSearchService
{
    protected array $roles = [];
    protected ?string $section = null;

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
     * Nastaví sekci pro filtrování vyhledávání.
     *
     * @param string|null $section
     * @return $this
     */
    public function forSection(?string $section): self
    {
        $this->section = $section;
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
        $section = $this->section ?: 'all';

        $rolesHash = md5(json_encode($this->roles));
        $cacheKey = "help_search_{$section}_{$rolesHash}_{$locale}_" . md5($query) . "_{$limit}";

        return \Illuminate\Support\Facades\Cache::remember($cacheKey, now()->addHour(), function () use ($query, $limit, $locale, $filteringRoles) {
            // Relevance ranking pomocí SQL (nyní využíváme JSON vyhledávání)
            // Váhy: Title (10), Keywords (8), Purpose (5), Content (3)
            return \DB::table('help_articles')
                ->select(['id', 'slug', 'title', 'excerpt', 'metadata', 'audience_roles', 'is_featured', 'content', 'search_keywords'])
                ->selectRaw("
                    (CASE WHEN title->>'$." . $locale . "' LIKE ? THEN 10 ELSE 0 END +
                     CASE WHEN search_keywords->>'$." . $locale . "' LIKE ? THEN 8 ELSE 0 END +
                     CASE WHEN metadata->>'$." . $locale . ".purpose' LIKE ? THEN 5 ELSE 0 END +
                     CASE WHEN content->>'$." . $locale . "' LIKE ? THEN 3 ELSE 0 END)
                    AS relevance
                ", [
                    "%" . $query . "%",
                    "%" . $query . "%",
                    "%" . $query . "%",
                    "%" . $query . "%",
                ])
                ->where('is_published', true)
                ->where(function ($q) {
                    $q->whereNull('published_at')->orWhere('published_at', '<=', now());
                })
                ->where(function ($q) use ($query, $locale) {
                    $term = "%" . $query . "%";
                    $q->where("title->{$locale}", 'LIKE', $term)
                        ->orWhere("search_keywords->{$locale}", 'LIKE', $term)
                        ->orWhere('metadata', 'LIKE', $term)
                        ->orWhere("content->{$locale}", 'LIKE', $term);
                })
                ->where(function ($q) {
                    $this->applySectionFilter($q);
                })
                ->orderByDesc('relevance')
                ->limit($limit)
                ->get()
                ->map(function ($article) use ($query, $locale) {
                    $titleRaw = $article->title;
                    $title = is_array($titleRaw) ? $titleRaw : (json_decode((string)$titleRaw, true) ?: []);
                    $article->title_str = $title[$locale] ?? ($title['cs'] ?? ($title['en'] ?? 'Untitled'));

                    $article->search_excerpt = $this->generateExcerpt($article, $query, $locale);

                    $rolesRaw = $article->audience_roles;
                    $article->audience_roles = is_array($rolesRaw) ? $rolesRaw : (json_decode((string)$rolesRaw, true) ?: []);
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
        $contentRaw = $article->content;
        $contentArr = is_array($contentRaw) ? $contentRaw : (json_decode((string)$contentRaw, true) ?: []);
        $content = strip_tags($contentArr[$locale] ?? ($contentArr['cs'] ?? ($contentArr['en'] ?? '')));

        $metaRaw = $article->metadata;
        $meta = is_array($metaRaw) ? $metaRaw : (json_decode((string)$metaRaw, true) ?: []);
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
     * Zvýrazní hledaný výraz v textu a zajistí bezpečné escapování.
     */
    protected function highlight(string $text, string $query): string
    {
        if (empty($query)) return e($text);

        $escapedText = e($text);
        $escapedQuery = e($query);

        return preg_replace(
            '/(' . preg_quote($escapedQuery, '/') . ')/i',
            '<mark class="bg-primary-100 text-primary-900 px-1 rounded-sm">$1</mark>',
            $escapedText
        );
    }

    /**
     * Aplikuje filtrování podle sekce a rolí na dotaz.
     */
    protected function applySectionFilter($query): void
    {
        $filteringRoles = $this->getFilteringRoles();

        if ($this->section === 'admin') {
            $query->where(function ($q) {
                $q->where('metadata', 'LIKE', '%"section":"admin"%')
                    ->orWhere('metadata', 'LIKE', '%"section":"both"%');
            });
        } elseif ($this->section === 'member') {
            $query->where(function ($q) {
                $q->where('metadata', 'LIKE', '%"section":"member"%')
                    ->orWhere('metadata', 'LIKE', '%"section":"both"%')
                    ->orWhere(function($sq) {
                        $sq->whereNull('metadata')
                           ->orWhere('metadata', 'NOT LIKE', '%"section":%');
                    });
            });
        }

        if (!empty($filteringRoles)) {
            $query->where(function ($inner) use ($filteringRoles) {
                $inner->whereNull('audience_roles');
                foreach ($filteringRoles as $role) {
                    $inner->orWhere('audience_roles', 'LIKE', '%"' . $role . '"%');
                }
            });
        }
    }
}
