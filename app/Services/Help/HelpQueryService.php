<?php

namespace App\Services\Help;

use App\Models\HelpArticle;
use App\Models\HelpCategory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class HelpQueryService
{
    protected array $roles = [];
    protected ?string $section = null;

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
     * Nastaví sekci pro filtrování obsahu.
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
     * Vygeneruje unikátní cache klíč na základě rolí, sekce a locale.
     */
    protected function getCacheKey(string $base, ?string $identifier = null): string
    {
        $roles = $this->roles;
        sort($roles);
        $rolesHash = md5(json_encode($roles));
        $locale = app()->getLocale();
        $section = $this->section ?: 'all';

        $key = "help_{$base}_{$section}_{$rolesHash}_{$locale}";
        if ($identifier) {
            $key .= "_{$identifier}";
        }
        return $key;
    }

    /**
     * Zjistí, zda má uživatel roli administrátora nebo superadmina.
     */
    protected function isAdmin(): bool
    {
        return in_array('admin', $this->roles) || in_array('super_admin', $this->roles);
    }

    /**
     * Vrátí role pro filtrování (pro admina prázdné, aby viděl vše).
     */
    protected function getFilteringRoles(): array
    {
        return $this->isAdmin() ? [] : $this->roles;
    }

    /**
     * Aplikuje filtrování podle sekce a rolí na dotaz.
     */
    protected function applySectionFilterToArticles($query): void
    {
        if ($this->section === 'admin') {
            $query->where(function ($q) {
                // FALLBACK pro Webglobe (bez JSON operátorů v SQL)
                $q->where('metadata', 'LIKE', '%"section":"admin"%')
                    ->orWhere('metadata', 'LIKE', '%"section":"both"%');
            });
        } elseif ($this->section === 'member') {
            $query->where(function ($q) {
                // FALLBACK pro Webglobe (bez JSON operátorů v SQL)
                $q->where('metadata', 'LIKE', '%"section":"member"%')
                    ->orWhere('metadata', 'LIKE', '%"section":"both"%')
                    ->orWhereNull('metadata'); // Default je member
            });
        }

        $filteringRoles = $this->getFilteringRoles();
        if (!empty($filteringRoles)) {
            $query->where(function ($q) use ($filteringRoles) {
                $q->whereNull('audience_roles');
                foreach ($filteringRoles as $role) {
                    // FALLBACK: Pro produkční DB bez JSON funkcí (Webglobe) použijeme LIKE.
                    $q->orWhere('audience_roles', 'LIKE', '%"' . (string) $role . '"%');
                }
            });
        }
    }

    protected function applySectionFilterToCategories($query): void
    {
        // Pro kategorie nemáme metadata. Filtrovat podle sekce skrze existenci článků v dané sekci.
        if ($this->section !== null) {
            $section = $this->section;
            $query->whereExists(function ($sub) use ($section) {
                $sub->from('help_articles')
                    ->selectRaw('1')
                    ->whereColumn('help_articles.category_id', 'help_categories.id')
                    ->where('help_articles.is_published', true)
                    ->where(function ($q) {
                        $q->whereNull('help_articles.published_at')->orWhere('help_articles.published_at', '<=', now());
                    })
                    ->where(function ($q) use ($section) {
                        if ($section === 'admin') {
                            // FALLBACK pro Webglobe
                            $q->where('help_articles.metadata', 'LIKE', '%"section":"admin"%')
                              ->orWhere('help_articles.metadata', 'LIKE', '%"section":"both"%');
                        } elseif ($section === 'member') {
                            // FALLBACK pro Webglobe
                            $q->where('help_articles.metadata', 'LIKE', '%"section":"member"%')
                              ->orWhere('help_articles.metadata', 'LIKE', '%"section":"both"%')
                              ->orWhereNull('help_articles.metadata');
                        }
                    });
            });
        }

        // Role-based filtr přímo na kategorii (audience_roles)
        $filteringRoles = $this->getFilteringRoles();
        if (!empty($filteringRoles)) {
            $query->where(function ($q) use ($filteringRoles) {
                $q->whereNull('audience_roles');
                foreach ($filteringRoles as $role) {
                    // FALLBACK pro Webglobe
                    $q->orWhere('audience_roles', 'LIKE', '%"' . (string) $role . '"%');
                }
            });
        }
    }

    /**
     * Načte kořenové kategorie pro úvodní stránku nápovědy.
     * Používá Query Builder pro maximální výkon a eliminaci rekurze v Eloquentu.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getHomeCategories(): \Illuminate\Support\Collection
    {
        $cacheKey = $this->getCacheKey('home_categories');

        return Cache::remember($cacheKey, now()->addHours(24), function () {
            $filteringRoles = $this->getFilteringRoles();
            $locale = app()->getLocale();

            $rows = \DB::table('help_categories')
                ->select(['id', 'slug', 'name', 'description', 'icon', 'color', 'audience_roles', 'is_featured'])
                ->where('is_active', true)
                ->whereNull('parent_id')
                ->where(function ($q) {
                    $this->applySectionFilterToCategories($q);
                })
                ->orderBy('sort_order')
                ->get();

            return $rows->map(function ($item) use ($locale) {
                // Default barva pokud chybí
                if (empty($item->color)) {
                    $item->color = 'slate';
                }
                $name = is_array($item->name) ? $item->name : (json_decode($item->name ?? '[]', true) ?: []);
                $item->name_str = $name[$locale] ?? ($name['cs'] ?? ($name['en'] ?? 'Untitled'));

                $desc = is_array($item->description) ? $item->description : (json_decode($item->description ?? '[]', true) ?: []);
                $item->description_str = $desc[$locale] ?? ($desc['cs'] ?? ($desc['en'] ?? ''));

                // Optimalizace N+1: Pro HomeCategories to necháme v Query Builderu pro výkon,
                // ale v cache to nevadí, že to proběhne jednou.
                $item->articles_count = \DB::table('help_articles')
                    ->where('category_id', $item->id)
                    ->where('is_published', true)
                    ->count();

                return $item;
            });
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
        $cacheKey = $this->getCacheKey('featured_articles', (string) $limit);

        return Cache::remember($cacheKey, now()->addHours(24), function () use ($limit) {
            $filteringRoles = $this->getFilteringRoles();
            $locale = app()->getLocale();

            // Ultra-lean výběr jen pro zobrazení karet na homepage
            $rows = \DB::table('help_articles')
                ->select(['id', 'slug', 'title', 'is_featured', 'updated_at', 'audience_roles'])
                ->where('is_published', true)
                ->where(function ($q) {
                    $q->whereNull('published_at')->orWhere('published_at', '<=', now());
                })
                ->where(function ($q) {
                    $this->applySectionFilterToArticles($q);
                })
                ->orderBy('is_featured', 'desc')
                ->orderBy('sort_order')
                ->limit($limit)
                ->get();

            return $rows->map(function ($item) use ($locale) {
                $titleRaw = $item->title;
                $title = is_array($titleRaw) ? $titleRaw : (json_decode((string)$titleRaw, true) ?: []);
                $item->title_str = $title[$locale] ?? ($title['cs'] ?? ($title['en'] ?? 'Untitled'));
                $item->is_featured = (bool) $item->is_featured;
                $rolesRaw = $item->audience_roles;
                $item->audience_roles = is_array($rolesRaw) ? $rolesRaw : (json_decode((string)$rolesRaw, true) ?: []);
                return $item;
            });
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
        $cacheKey = $this->getCacheKey('category', $slug);

        return Cache::remember($cacheKey, now()->addHours(24), function () use ($slug) {
            $filteringRoles = $this->getFilteringRoles();
            $locale = app()->getLocale();

            $category = \DB::table('help_categories')
                ->select(['id', 'slug', 'name', 'description', 'icon', 'color', 'parent_id', 'audience_roles'])
                ->where('slug', $slug)
                ->where('is_active', true)
                ->where(function ($q) {
                    $this->applySectionFilterToCategories($q);
                })
                ->first();

            if (!$category) {
                return null;
            }

            // Default barva
            if (empty($category->color)) {
                $category->color = 'slate';
            }

            // Dekódování polí kategorie
            $category->audience_roles = is_array($category->audience_roles) ? $category->audience_roles : (json_decode($category->audience_roles ?? '[]', true) ?: []);
            $category->name = is_array($category->name) ? $category->name : (json_decode($category->name ?? '[]', true) ?: []);
            $category->name_str = $category->name[$locale] ?? ($category->name['cs'] ?? ($category->name['en'] ?? 'Untitled'));

            $category->description = is_array($category->description) ? $category->description : (json_decode($category->description ?? '[]', true) ?: []);
            $category->description_str = $category->description[$locale] ?? ($category->description['cs'] ?? ($category->description['en'] ?? ''));

            // Načtení článků kategorie přes Query Builder - ultra lean
            $category->articles = \DB::table('help_articles')
                ->select(['id', 'slug', 'title', 'is_featured', 'audience_roles', 'sort_order', 'metadata'])
                ->where('category_id', $category->id)
                ->where('is_published', true)
                ->where(function ($q) {
                    $q->whereNull('published_at')->orWhere('published_at', '<=', now());
                })
                ->where(function ($q) {
                    $this->applySectionFilterToArticles($q);
                })
                ->orderBy('sort_order')
                ->get()
                ->map(function ($article) use ($locale) {
                    $rolesRaw = $article->audience_roles;
                    $article->audience_roles = is_array($rolesRaw) ? $rolesRaw : (json_decode((string)$rolesRaw, true) ?: []);
                    $titleRaw = $article->title;
                    $article->title = is_array($titleRaw) ? $titleRaw : (json_decode((string)$titleRaw, true) ?: []);
                    $article->title_str = $article->title[$locale] ?? ($article->title['cs'] ?? ($article->title['en'] ?? 'Untitled'));

                    $metaRaw = $article->metadata;
                    $m = is_array($metaRaw) ? $metaRaw : (json_decode((string)$metaRaw ?? '[]', true) ?: []);
                    if (array_key_exists('cs', $m) || array_key_exists('en', $m) || array_key_exists($locale, $m)) {
                        $rawM = $m[$locale] ?? ($m['cs'] ?? ($m['en'] ?? '[]'));
                        $article->metadata = is_string($rawM) ? (json_decode($rawM, true) ?: []) : ($rawM ?: []);
                    } else {
                        $article->metadata = $m;
                    }

                    return $article;
                });

            // Načtení podkategorií
            $category->subcategories = \DB::table('help_categories')
                ->where('parent_id', $category->id)
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get()
                ->map(function ($sub) use ($locale) {
                    $nameRaw = $sub->name;
                    $name = is_array($nameRaw) ? $nameRaw : (json_decode((string)$nameRaw, true) ?: []);
                    $sub->name_str = $name[$locale] ?? ($name['cs'] ?? ($name['en'] ?? 'Untitled'));
                    return $sub;
                });

            return $category;
        });
    }

    /**
     * Načte článek podle slugu se všemi souvisejícími daty.
     *
     * @param string $slug
     * @return object|null
     */
    public function getArticleBySlug(string $slug): ?object
    {
        $cacheKey = $this->getCacheKey('article', $slug);

        return Cache::remember($cacheKey, now()->addHours(24), function () use ($slug) {
            $filteringRoles = $this->getFilteringRoles();
            $locale = app()->getLocale();

            // Pro detail článku použijeme Eloquent, ale pouze JEDEN model
            // a hned mu předpočítáme řetězce, aby Blade nevolal magii.
            $article = HelpArticle::query()
                ->published()
                ->where(function ($q) {
                    $this->applySectionFilterToArticles($q);
                })
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
            $article->relatedArticlesData = $article->relatedArticles()
                ->published()
                ->forAudience($filteringRoles)
                ->get()
                ->map(function ($rel) use ($locale) {
                    return (object) [
                        'slug' => $rel->slug,
                        'title_str' => $rel->getTranslation('title', $locale, false),
                    ];
                });

            return $article;
        });
    }

    /**
     * Načte všechny aktivní kategorie jako strom.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getCategoryTree(): \Illuminate\Support\Collection
    {
        $cacheKey = $this->getCacheKey('category_tree');

        return Cache::remember($cacheKey, now()->addHours(24), function () {
            $filteringRoles = $this->getFilteringRoles();
            $locale = app()->getLocale();

            // Načteme všechny kategorie a články naráz pro eliminaci N+1
            $categories = \DB::table('help_categories')
                ->select(['id', 'slug', 'name', 'parent_id', 'icon', 'color', 'audience_roles'])
                ->where('is_active', true)
                ->where(function ($q) {
                    $this->applySectionFilterToCategories($q);
                })
                ->orderBy('sort_order')
                ->get()
                ->map(function ($cat) use ($locale) {
                    $nameRaw = $cat->name;
                    $name = is_array($nameRaw) ? $nameRaw : (json_decode((string)$nameRaw, true) ?: []);
                    $cat->name_str = $name[$locale] ?? ($name['cs'] ?? ($name['en'] ?? 'Untitled'));
                    return $cat;
                });

            $allArticles = \DB::table('help_articles')
                ->select(['id', 'slug', 'title', 'category_id', 'audience_roles', 'sort_order', 'metadata'])
                ->where('is_published', true)
                ->where(function ($q) {
                    $this->applySectionFilterToArticles($q);
                })
                ->orderBy('sort_order')
                ->get()
                ->map(function ($art) use ($locale) {
                    $titleRaw = $art->title;
                    $title = is_array($titleRaw) ? $titleRaw : (json_decode((string)$titleRaw, true) ?: []);
                    $art->title_str = $title[$locale] ?? ($title['cs'] ?? ($title['en'] ?? 'Untitled'));

                    $metaRaw = $art->metadata;
                    $m = is_array($metaRaw) ? $metaRaw : (json_decode((string)$metaRaw ?? '[]', true) ?: []);
                    if (array_key_exists('cs', $m) || array_key_exists('en', $m) || array_key_exists($locale, $m)) {
                        $rawM = $m[$locale] ?? ($m['cs'] ?? ($m['en'] ?? '[]'));
                        $art->metadata = is_string($rawM) ? (json_decode($rawM, true) ?: []) : ($rawM ?: []);
                    } else {
                        $art->metadata = $m;
                    }

                    return $art;
                })
                ->groupBy('category_id');

            // Sestavení stromu v paměti
            $tree = $categories->whereNull('parent_id')->values();
            foreach ($tree as $root) {
                $root->articles = $allArticles->get($root->id, collect());
                $root->children = $categories->where('parent_id', $root->id)->values();
                foreach ($root->children as $child) {
                    $child->articles = $allArticles->get($child->id, collect());
                }
            }

            return $tree;
        });
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
            ->select(['id', 'slug', 'title', 'metadata'])
            ->where('category_id', $article->category_id)
            ->where('is_published', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $currentIndex = $articles->search(fn ($a) => $a->id === $article->id);

        $prev = $currentIndex > 0 ? $articles->get($currentIndex - 1) : null;
        $next = $currentIndex !== false && $currentIndex < $articles->count() - 1 ? $articles->get($currentIndex + 1) : null;

        if ($prev) {
            $tRaw = $prev->title;
            $t = is_array($tRaw) ? $tRaw : (json_decode((string)$tRaw, true) ?: []);
            $prev->title_str = $t[$locale] ?? ($t['cs'] ?? ($t['en'] ?? 'Untitled'));

            $mRaw = $prev->metadata;
            $m = is_array($mRaw) ? $mRaw : (json_decode((string)$mRaw, true) ?: []);
            if (array_key_exists('cs', $m) || array_key_exists('en', $m) || array_key_exists($locale, $m)) {
                $rawM = $m[$locale] ?? ($m['cs'] ?? ($m['en'] ?? '[]'));
                $prev->metadata = is_string($rawM) ? (json_decode($rawM, true) ?: []) : ($rawM ?: []);
            } else {
                $prev->metadata = $m;
            }
        }
        if ($next) {
            $tRaw = $next->title;
            $t = is_array($tRaw) ? $tRaw : (json_decode((string)$tRaw, true) ?: []);
            $next->title_str = $t[$locale] ?? ($t['cs'] ?? ($t['en'] ?? 'Untitled'));

            $mRaw = $next->metadata;
            $m = is_array($mRaw) ? $mRaw : (json_decode((string)$mRaw, true) ?: []);
            if (array_key_exists('cs', $m) || array_key_exists('en', $m) || array_key_exists($locale, $m)) {
                $rawM = $m[$locale] ?? ($m['cs'] ?? ($m['en'] ?? '[]'));
                $next->metadata = is_string($rawM) ? (json_decode($rawM, true) ?: []) : ($rawM ?: []);
            } else {
                $next->metadata = $m;
            }
        }

        return ['prev' => $prev, 'next' => $next];
    }
}
