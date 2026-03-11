<?php

namespace App\Services\Help;

use App\Models\HelpArticle;
use App\Models\HelpCategory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class HelpQueryService
{
    protected array $roles = [];

    /**
     * Nastaví role pro filtrování obsahu.
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
     * Načte kořenové kategorie pro úvodní stránku nápovědy.
     * Používá Query Builder pro maximální výkon a eliminaci rekurze v Eloquentu.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getHomeCategories(): \Illuminate\Support\Collection
    {
        $filteringRoles = $this->getFilteringRoles();
        $locale = app()->getLocale();

        $rows = \DB::table('help_categories')
            ->select(['id', 'slug', 'name', 'description', 'icon', 'color', 'audience_roles', 'is_featured'])
            ->where('is_active', true)
            ->whereNull('parent_id')
            ->when(!empty($filteringRoles), function ($q) use ($filteringRoles) {
                $q->where(function ($inner) use ($filteringRoles) {
                    $inner->whereNull('audience_roles');
                    foreach ($filteringRoles as $role) {
                        $inner->orWhereJsonContains('audience_roles', $role);
                    }
                });
            })
            ->orderBy('sort_order')
            ->get();

        return $rows->map(function ($item) use ($locale) {
            // Default barva pokud chybí
            if (empty($item->color)) {
                $item->color = 'slate';
            }
            $name = json_decode($item->name, true) ?: [];
            $item->name_str = $name[$locale] ?? ($name['cs'] ?? ($name['en'] ?? 'Untitled'));

            $desc = json_decode($item->description, true) ?: [];
            $item->description_str = $desc[$locale] ?? ($desc['cs'] ?? ($desc['en'] ?? ''));

            $item->articles_count = \DB::table('help_articles')
                ->where('category_id', $item->id)
                ->where('is_published', true)
                ->count();

            return $item;
        });
    }

    /**
     * Načte doporučené (featured) články.
     * Používá Query Builder pro eliminaci paměťové náročnosti Eloquent modelů.
     *
     * @param int $limit
     * @return \Illuminate\Support\Collection
     */
    public function getFeaturedArticles(int $limit = 5): \Illuminate\Support\Collection
    {
        $filteringRoles = $this->getFilteringRoles();
        $locale = app()->getLocale();

        // Ultra-lean výběr jen pro zobrazení karet na homepage
        $rows = \DB::table('help_articles')
            ->select(['id', 'slug', 'title', 'is_featured', 'updated_at', 'audience_roles'])
            ->where('is_published', true)
            ->where(function ($q) {
                $q->whereNull('published_at')->orWhere('published_at', '<=', now());
            })
            ->orderBy('is_featured', 'desc')
            ->orderBy('sort_order')
            ->limit($limit)
            ->get();

        return $rows->map(function ($item) use ($locale) {
            $title = json_decode($item->title, true) ?: [];
            $item->title_str = $title[$locale] ?? ($title['cs'] ?? ($title['en'] ?? 'Untitled'));
            $item->is_featured = (bool) $item->is_featured;
            $item->audience_roles = json_decode($item->audience_roles, true) ?: [];
            return $item;
        });
    }

    /**
     * Načte kategorii podle slugu včetně jejích článků a podkategorií.
     *
     * @param string $slug
     * @return object|null
     */
    public function getCategoryBySlug(string $slug): ?object
    {
        $filteringRoles = $this->getFilteringRoles();
        $locale = app()->getLocale();

        $category = \DB::table('help_categories')
            ->select(['id', 'slug', 'name', 'description', 'icon', 'color', 'parent_id', 'audience_roles'])
            ->where('slug', $slug)
            ->where('is_active', true)
            ->first();

        if (!$category) {
            return null;
        }

        // Default barva
        if (empty($category->color)) {
            $category->color = 'slate';
        }

        // Dekódování polí kategorie
        $category->audience_roles = json_decode($category->audience_roles ?? '[]', true) ?: [];
        $category->name = json_decode($category->name ?? '[]', true) ?: [];
        $category->name_str = $category->name[$locale] ?? ($category->name['cs'] ?? ($category->name['en'] ?? 'Untitled'));

        $category->description = json_decode($category->description ?? '[]', true) ?: [];
        $category->description_str = $category->description[$locale] ?? ($category->description['cs'] ?? ($category->description['en'] ?? ''));

        // Načtení článků kategorie přes Query Builder - ultra lean
        $category->articles = \DB::table('help_articles')
            ->select(['id', 'slug', 'title', 'is_featured', 'audience_roles', 'sort_order'])
            ->where('category_id', $category->id)
            ->where('is_published', true)
            ->where(function ($q) {
                $q->whereNull('published_at')->orWhere('published_at', '<=', now());
            })
            ->orderBy('sort_order')
            ->get()
            ->map(function ($article) use ($locale) {
                $article->audience_roles = json_decode($article->audience_roles ?? '[]', true) ?: [];
                $article->title = json_decode($article->title ?? '[]', true) ?: [];
                $article->title_str = $article->title[$locale] ?? ($article->title['cs'] ?? ($article->title['en'] ?? 'Untitled'));
                return $article;
            });

        return $category;
    }

    /**
     * Načte článek podle slugu se všemi souvisejícími daty.
     *
     * @param string $slug
     * @return object|null
     */
    public function getArticleBySlug(string $slug): ?object
    {
        $filteringRoles = $this->getFilteringRoles();
        $locale = app()->getLocale();

        // Pro detail článku použijeme Eloquent, ale pouze JEDEN model
        // a hned mu předpočítáme řetězce, aby Blade nevolal magii.
        $article = HelpArticle::query()
            ->published()
            ->forAudience($filteringRoles)
            ->where('slug', $slug)
            ->with(['category', 'faqs', 'quickActions'])
            ->first();

        if (!$article) {
            return null;
        }

        // Ruční "zhloupnutí" pro Blade
        $article->title_str = $article->getTranslation('title', $locale, false);
        $article->content_html = $article->getParsedContent();

        // Předpočítáme i relace pro stabilitu
        $article->faqs->each(function ($faq) use ($locale) {
            $faq->question_str = $faq->getTranslation('question', $locale, false);
            $faq->answer_str = $faq->getTranslation('answer', $locale, false);
        });

        $article->quickActions->each(function ($action) use ($locale) {
            $action->label_str = $action->getTranslation('label', $locale, false);
        });

        // Předpočítáme i související
        $article->relatedArticles = $article->relatedArticles()
            ->published()
            ->forAudience($filteringRoles)
            ->get()
            ->map(function ($rel) use ($locale) {
                $rel->title_str = $rel->getTranslation('title', $locale, false);
                return $rel;
            });

        return $article;
    }

    /**
     * Načte všechny aktivní kategorie jako strom.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getCategoryTree(): \Illuminate\Support\Collection
    {
        $filteringRoles = $this->getFilteringRoles();
        $locale = app()->getLocale();

        // Strom uděláme taky přes Query Builder, ať je to neprůstřelné
        $categories = \DB::table('help_categories')
            ->select(['id', 'slug', 'name', 'parent_id', 'icon', 'color'])
            ->where('is_active', true)
            ->when(!empty($filteringRoles), function ($q) use ($filteringRoles) {
                $q->where(function ($inner) use ($filteringRoles) {
                    $inner->whereNull('audience_roles');
                    foreach ($filteringRoles as $role) {
                        $inner->orWhereJsonContains('audience_roles', $role);
                    }
                });
            })
            ->orderBy('sort_order')
            ->get()
            ->map(function ($cat) use ($locale) {
                $name = json_decode($cat->name, true) ?: [];
                $cat->name_str = $name[$locale] ?? ($name['cs'] ?? ($name['en'] ?? 'Untitled'));
                return $cat;
            });

        // Sestavení stromu v paměti (jednoduché 2 úrovně)
        $tree = $categories->whereNull('parent_id')->values();
        foreach ($tree as $root) {
            $root->children = $categories->where('parent_id', $root->id)->values();
            foreach ($root->children as $child) {
                $child->articles = \DB::table('help_articles')
                    ->select(['id', 'slug', 'title'])
                    ->where('category_id', $child->id)
                    ->where('is_published', true)
                    ->orderBy('sort_order')
                    ->get()
                    ->map(function ($art) use ($locale) {
                        $title = json_decode($art->title, true) ?: [];
                        $art->title_str = $title[$locale] ?? ($title['cs'] ?? ($title['en'] ?? 'Untitled'));
                        return $art;
                    });
            }

            // Kořenové články
            $root->articles = \DB::table('help_articles')
                ->select(['id', 'slug', 'title'])
                ->where('category_id', $root->id)
                ->where('is_published', true)
                ->orderBy('sort_order')
                ->get()
                ->map(function ($art) use ($locale) {
                    $title = json_decode($art->title, true) ?: [];
                    $art->title_str = $title[$locale] ?? ($title['cs'] ?? ($title['en'] ?? 'Untitled'));
                    return $art;
                });
        }

        return $tree;
    }
    /**
     * Vrátí předchozí a následující článek ve stejné kategorii.
     *
     * @param object $article
     * @return array{prev: object|null, next: object|null}
     */
    public function getArticleNavigation(object $article): array
    {
        $filteringRoles = $this->getFilteringRoles();
        $locale = app()->getLocale();

        $articles = \DB::table('help_articles')
            ->select(['id', 'slug', 'title'])
            ->where('category_id', $article->category_id)
            ->where('is_published', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $currentIndex = $articles->search(fn ($a) => $a->id === $article->id);

        $prev = $currentIndex > 0 ? $articles->get($currentIndex - 1) : null;
        $next = $currentIndex !== false && $currentIndex < $articles->count() - 1 ? $articles->get($currentIndex + 1) : null;

        if ($prev) {
            $t = json_decode($prev->title, true) ?: [];
            $prev->title_str = $t[$locale] ?? ($t['cs'] ?? ($t['en'] ?? 'Untitled'));
        }
        if ($next) {
            $t = json_decode($next->title, true) ?: [];
            $next->title_str = $t[$locale] ?? ($t['cs'] ?? ($t['en'] ?? 'Untitled'));
        }

        return ['prev' => $prev, 'next' => $next];
    }
}
