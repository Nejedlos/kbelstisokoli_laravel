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
     *
     * @return Collection<int, HelpCategory>
     */
    public function getHomeCategories(): Collection
    {
        $filteringRoles = $this->getFilteringRoles();

        $categories = HelpCategory::query()
            ->active()
            ->root()
            ->forAudience($filteringRoles)
            ->withCount(['articles' => function ($query) use ($filteringRoles) {
                $query->published()->forAudience($filteringRoles);
            }])
            ->orderBy('sort_order')
            ->get();

        if (!empty($this->roles)) {
            $categories = $categories->sortByDesc(function ($category) {
                $score = $category->is_featured ? 100 : 0;
                if (!empty($category->audience_roles)) {
                    foreach ($this->roles as $role) {
                        if (in_array($role, $category->audience_roles)) {
                            $score += 1000;
                            break;
                        }
                    }
                }
                return $score;
            })->values();
        }

        return $categories;
    }

    /**
     * Načte doporučené (featured) články.
     *
     * @param int $limit
     * @return Collection<int, HelpArticle>
     */
    public function getFeaturedArticles(int $limit = 5): Collection
    {
        $filteringRoles = $this->getFilteringRoles();

        $articles = HelpArticle::query()
            ->published()
            ->forAudience($filteringRoles)
            ->with('category')
            ->orderBy('is_featured', 'desc')
            ->orderBy('sort_order')
            ->get();

        if (!empty($this->roles)) {
            $articles = $articles->sortByDesc(function ($article) {
                $score = $article->is_featured ? 100 : 0;
                if (!empty($article->audience_roles)) {
                    foreach ($this->roles as $role) {
                        if (in_array($role, $article->audience_roles)) {
                            $score += 1000;
                            break;
                        }
                    }
                }
                return $score;
            })->values();
        }

        return $articles->take($limit);
    }

    /**
     * Načte kategorii podle slugu včetně jejích článků a podkategorií.
     *
     * @param string $slug
     * @return HelpCategory|null
     */
    public function getCategoryBySlug(string $slug): ?HelpCategory
    {
        $filteringRoles = $this->getFilteringRoles();

        return HelpCategory::query()
            ->active()
            ->forAudience($filteringRoles)
            ->where('slug', $slug)
            ->with([
                'children' => function ($query) use ($filteringRoles) {
                    $query->active()->forAudience($filteringRoles)->orderBy('sort_order');
                },
                'articles' => function ($query) use ($filteringRoles) {
                    $query->published()->forAudience($filteringRoles)->orderBy('sort_order');
                }
            ])
            ->first();
    }

    /**
     * Načte článek podle slugu se všemi souvisejícími daty.
     *
     * @param string $slug
     * @return HelpArticle|null
     */
    public function getArticleBySlug(string $slug): ?HelpArticle
    {
        $filteringRoles = $this->getFilteringRoles();

        return HelpArticle::query()
            ->published()
            ->forAudience($filteringRoles)
            ->where('slug', $slug)
            ->with([
                'category.parent',
                'faqs',
                'quickActions',
                'relatedArticles' => function ($query) use ($filteringRoles) {
                    $query->published()->forAudience($filteringRoles);
                }
            ])
            ->first();
    }

    /**
     * Načte všechny aktivní kategorie jako strom.
     *
     * @return Collection<int, HelpCategory>
     */
    public function getCategoryTree(): Collection
    {
        $filteringRoles = $this->getFilteringRoles();

        return HelpCategory::query()
            ->active()
            ->root()
            ->forAudience($filteringRoles)
            ->with([
                'children' => function ($query) use ($filteringRoles) {
                    $query->active()
                        ->forAudience($filteringRoles)
                        ->orderBy('sort_order')
                        ->with(['articles' => function ($query) use ($filteringRoles) {
                            $query->published()->forAudience($filteringRoles)->orderBy('sort_order');
                        }]);
                },
                'articles' => function ($query) use ($filteringRoles) {
                    $query->published()->forAudience($filteringRoles)->orderBy('sort_order');
                }
            ])
            ->orderBy('sort_order')
            ->get();
    }
    /**
     * Vrátí předchozí a následující článek ve stejné kategorii.
     *
     * @param HelpArticle $article
     * @return array{prev: HelpArticle|null, next: HelpArticle|null}
     */
    public function getArticleNavigation(HelpArticle $article): array
    {
        $filteringRoles = $this->getFilteringRoles();

        $articles = HelpArticle::query()
            ->published()
            ->forAudience($filteringRoles)
            ->where('category_id', $article->category_id)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $currentIndex = $articles->search(fn ($a) => $a->id === $article->id);

        return [
            'prev' => $currentIndex > 0 ? $articles->get($currentIndex - 1) : null,
            'next' => $currentIndex !== false && $currentIndex < $articles->count() - 1
                ? $articles->get($currentIndex + 1)
                : null,
        ];
    }
}
