<?php

namespace App\Services\Help;

use App\Models\HelpArticle;
use App\Models\HelpCategory;
use Illuminate\Support\Collection;

class HelpService
{
    public function __construct(
        protected HelpQueryService $queryService,
        protected HelpSearchService $searchService,
        protected HelpNavigationService $navigationService
    ) {}

    /**
     * Nastaví role pro filtrování obsahu.
     *
     * @param array|string $roles
     * @return $this
     */
    public function forAudience(array|string $roles): self
    {
        $this->queryService->forAudience($roles);
        $this->searchService->forAudience($roles);
        return $this;
    }

    /**
     * Načte data pro úvodní stránku nápovědy.
     *
     * @return array
     */
    public function getHomeData(): array
    {
        return [
            'categories' => $this->queryService->getHomeCategories(),
            'featured_articles' => $this->queryService->getFeaturedArticles(),
            // Na landing stránce breadcrumbs negenerujeme, je to fixní kořen bez rekurze
            'breadcrumbs' => collect(),
        ];
    }

    /**
     * Načte data pro stránku kategorie.
     *
     * @param string $slug
     * @return array|null
     */
    public function getCategoryData(string $slug): ?array
    {
        $category = $this->queryService->getCategoryBySlug($slug);

        if (!$category) {
            return null;
        }

        return [
            'category' => $category,
            'articles' => $category->articles ?? collect(),
            'subcategories' => $category->subcategories ?? collect(),
            'breadcrumbs' => $this->navigationService->getBreadcrumbs($category),
        ];
    }

    /**
     * Načte data pro detail článku.
     *
     * @param string $slug
     * @return array|null
     */
    public function getArticleData(string $slug): ?array
    {
        $article = $this->queryService->getArticleBySlug($slug);

        if (!$article) {
            return null;
        }

        $navigation = $this->queryService->getArticleNavigation($article);

        return [
            'article' => $article,
            'breadcrumbs' => $this->navigationService->getBreadcrumbs($article),
            'prev_article' => $navigation['prev'] ?? null,
            'next_article' => $navigation['next'] ?? null,
            'faqs' => $article->faqs ?? collect(),
        ];
    }

    /**
     * Vyhledá články.
     *
     * @param string $query
     * @return Collection
     */
    public function search(string $query): Collection
    {
        return $this->searchService->search($query);
    }

    /**
     * Vrátí strom kategorií pro navigaci/sidebar.
     *
     * @return Collection
     */
    public function getNavigationTree(): Collection
    {
        return $this->queryService->getCategoryTree();
    }
}
