<?php

namespace App\Services\Help;

use App\Models\HelpArticle;
use App\Models\HelpCategory;
use Illuminate\Support\Collection;

class HelpNavigationService
{
    /**
     * Vygeneruje breadcrumbs pro danou položku (kategorii nebo článek).
     *
     * @param HelpCategory|HelpArticle|null $item
     * @return Collection
     */
    public function getBreadcrumbs($item = null): Collection
    {
        $breadcrumbs = collect([
            [
                'label' => __('admin.navigation.pages.help'),
                'slug' => null,
                'url' => \App\Filament\Pages\Help::getUrl(),
                'is_active' => $item === null,
            ]
        ]);

        if ($item instanceof HelpArticle) {
            $this->addCategoryBreadcrumbs($item->category, $breadcrumbs);
            $breadcrumbs->push([
                'label' => $item->getTranslation('title', app()->getLocale(), false),
                'slug' => $item->slug,
                'url' => \App\Filament\Pages\Help::getUrl(['file' => $item->slug]),
                'is_active' => true,
            ]);
        } elseif ($item instanceof HelpCategory) {
            $this->addCategoryBreadcrumbs($item, $breadcrumbs, true);
        }

        return $breadcrumbs;
    }

    /**
     * Rekurzivně přidá kategorie do breadcrumbs.
     *
     * @param HelpCategory|null $category
     * @param Collection $breadcrumbs
     * @param bool $isCurrent
     */
    protected function addCategoryBreadcrumbs(?HelpCategory $category, Collection $breadcrumbs, bool $isCurrent = false): void
    {
        if (!$category) {
            return;
        }

        $path = [];
        $current = $category;
        $visitedIds = [];

        while ($current && !in_array($current->id, $visitedIds)) {
            $visitedIds[] = $current->id;
            array_unshift($path, $current);

            if (count($visitedIds) > 10) { // Ochrana proti příliš hlubokému nebo nekonečnému cyklu
                break;
            }

            // Místo $current->parent použijeme přímé DB volání, abychom zamezili rekurzi v relacích a magic getterech
            $currentId = $current->parent_id;
            $current = $currentId ? HelpCategory::find($currentId) : null;
        }

        foreach ($path as $cat) {
            $breadcrumbs->push([
                'label' => $cat->getTranslation('name', app()->getLocale(), false),
                'slug' => $cat->slug,
                'url' => \App\Filament\Pages\Help::getUrl(['cat' => $cat->slug]),
                'is_active' => $isCurrent && ($cat->id === $category->id),
            ]);
        }
    }
}
