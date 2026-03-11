<?php

namespace App\Services\Help;

use App\Models\HelpArticle;
use App\Models\HelpCategory;
use Illuminate\Support\Collection;

class HelpNavigationService
{
    private const MAX_BREADCRUMB_DEPTH = 50;

    /**
     * Vygeneruje breadcrumbs pro danou položku (kategorii nebo článek).
     *
     * @param object|null $item
     * @return Collection
     */
    public function getBreadcrumbs(object|null $item = null): Collection
    {
        $locale = app()->getLocale();
        $itemId = $item->id ?? 'root';
        $itemType = $item ? get_class($item) : 'none';
        $cacheKey = "help_breadcrumbs_{$itemType}_{$itemId}_{$locale}";

        return \Illuminate\Support\Facades\Cache::remember($cacheKey, now()->addHours(24), function () use ($item) {
            $breadcrumbs = collect([
                [
                    'label' => __('admin.navigation.pages.help'),
                    'slug' => null,
                    'url' => \App\Filament\Pages\Help::getUrl(),
                    'is_active' => $item === null,
                ]
            ]);

            if ($item === null) {
                return $breadcrumbs;
            }

            // Podpora pro stdClass i Eloquent modely
            $isArticle = isset($item->category_id) || $item instanceof HelpArticle;

            if ($isArticle) {
                $category = null;
                if (isset($item->category) && $item->category) {
                    $category = $item->category;
                } elseif (isset($item->category_id)) {
                    $category = HelpCategory::find($item->category_id);
                }

                if ($category) {
                    $this->addCategoryBreadcrumbs($category, $breadcrumbs);
                }

                $label = $item->title_str ?? (method_exists($item, 'getTranslation') ? $item->getTranslation('title', app()->getLocale(), false) : ($item->title ?? 'Untitled'));

                $breadcrumbs->push([
                    'label' => $label,
                    'slug' => $item->slug,
                    'url' => \App\Filament\Pages\Help::getUrl(['file' => $item->slug]),
                    'is_active' => true,
                ]);
            } else {
                $this->addCategoryBreadcrumbs($item, $breadcrumbs, true);
            }

            return $breadcrumbs;
        });
    }

    /**
     * Rekurzivně přidá kategorie do breadcrumbs.
     *
     * @param object|null $category
     * @param Collection $breadcrumbs
     * @param bool $isCurrent
     */
    protected function addCategoryBreadcrumbs(object|null $category, Collection $breadcrumbs, bool $isCurrent = false): void
    {
        if (!$category) {
            return;
        }

        $path = [];
        $current = $category;
        $visitedIds = [];
        $depth = 0;

        while ($current) {
            $depth++;

            if ($depth > self::MAX_BREADCRUMB_DEPTH) {
                if (function_exists('pre_log')) {
                    pre_log('Help breadcrumbs depth limit reached', ['category_id' => $category->id ?? 'unknown', 'depth' => $depth]);
                } else {
                    \Log::error('Help breadcrumbs depth limit reached', ['category_id' => $category->id ?? 'unknown', 'depth' => $depth]);
                }
                break;
            }

            $id = $current->id ?? null;
            if ($id && in_array($id, $visitedIds, true)) {
                if (function_exists('pre_log')) {
                    pre_log('Help breadcrumbs cycle detected', ['category_id' => $id, 'visited_ids' => $visitedIds]);
                } else {
                    \Log::error('Help breadcrumbs cycle detected', ['category_id' => $id, 'visited_ids' => $visitedIds]);
                }
                break;
            }

            if ($id) {
                $visitedIds[] = $id;
            }

            array_unshift($path, $current);

            $parentId = $current->parent_id ?? null;
            if (!$parentId) {
                break;
            }

            // Načítáme parenta - zkusíme Query Builder pro rychlost
            $current = \DB::table('help_categories')->where('id', $parentId)->first();
        }

        foreach ($path as $cat) {
            $label = $cat->name_str ?? (method_exists($cat, 'getTranslation') ? $cat->getTranslation('name', app()->getLocale(), false) : (json_decode($cat->name ?? '{}', true)[app()->getLocale()] ?? 'Untitled'));

            $breadcrumbs->push([
                'label' => $label,
                'slug' => $cat->slug,
                'url' => \App\Filament\Pages\Help::getUrl(['cat' => $cat->slug]),
                'is_active' => $isCurrent && (isset($cat->id) && isset($category->id) && $cat->id === $category->id),
            ]);
        }
    }
}
