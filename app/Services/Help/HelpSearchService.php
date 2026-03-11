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
     * @return Collection<int, HelpArticle>
     */
    public function search(string $query, int $limit = 20): Collection
    {
        $query = trim($query);

        if (empty($query)) {
            return new Collection();
        }

        $locale = app()->getLocale();
        $filteringRoles = $this->getFilteringRoles();

        // Jednoduchý relevance ranking pomocí SQL (funguje v SQLite i MySQL)
        // Váhy: Title (10), Keywords (8), Purpose (5), Content (3)
        return HelpArticle::query()
            ->published()
            ->forAudience($filteringRoles)
            ->select('*')
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
            ->where(function (Builder $q) use ($query, $locale) {
                $term = "%" . mb_strtolower($query) . "%";
                $q->whereRaw("JSON_EXTRACT(title, '$.\"{$locale}\"') LIKE ?", [$term])
                    ->orWhereRaw("JSON_EXTRACT(search_keywords, '$.\"{$locale}\"') LIKE ?", [$term])
                    ->orWhereRaw("JSON_EXTRACT(metadata, '$.\"{$locale}\".purpose') LIKE ?", [$term])
                    ->orWhereRaw("JSON_EXTRACT(content, '$.\"{$locale}\"') LIKE ?", [$term]);
            })
            ->with('category')
            ->orderByDesc('relevance')
            ->orderByRaw("JSON_EXTRACT(title, '$.\"{$locale}\"') ASC")
            ->limit($limit)
            ->get()
            ->map(function (HelpArticle $article) use ($query, $locale) {
                $article->search_excerpt = $this->generateExcerpt($article, $query, $locale);
                return $article;
            });
    }

    /**
     * Vygeneruje úryvek textu pro výsledek vyhledávání.
     */
    protected function generateExcerpt(HelpArticle $article, string $query, string $locale): string
    {
        $content = strip_tags($article->getTranslation('content', $locale));
        $purpose = $article->metadata['purpose'] ?? '';

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
