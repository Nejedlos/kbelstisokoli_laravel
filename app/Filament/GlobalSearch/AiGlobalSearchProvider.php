<?php

namespace App\Filament\GlobalSearch;

use App\Models\AiDocument;
use Filament\GlobalSearch\GlobalSearchResult;
use Filament\GlobalSearch\GlobalSearchResults;
use Filament\GlobalSearch\Providers\Contracts\GlobalSearchProvider;
use Illuminate\Support\Facades\App;

class AiGlobalSearchProvider implements GlobalSearchProvider
{
    public function getResults(string $query): ?GlobalSearchResults
    {
        $locale = App::getLocale();
        $results = GlobalSearchResults::make();

        // Vyhledávání v AiDocument
        // Priorita: Title (LIKE %) > Keywords (JSON) > Content (LIKE %)
        // Omezíme na admin dokumenty
        $documents = AiDocument::where('locale', $locale)
            ->where('type', 'like', 'admin.%')
            ->where(function ($q) use ($query) {
                $queryLower = mb_strtolower($query);
                $q->whereRaw('LOWER(title) LIKE ?', ["%{$queryLower}%"])
                    ->orWhereRaw('LOWER(content) LIKE ?', ["%{$queryLower}%"])
                    ->orWhereRaw('LOWER(keywords) LIKE ?', ["%{$queryLower}%"]);
            })
            ->orderBy('title')
            ->limit(10)
            ->get();

        $categories = [];

        foreach ($documents as $doc) {
            $category = $this->getCategoryName($doc->type);

            if (! array_key_exists($category, $categories)) {
                $categories[$category] = [];
            }

            $categories[$category][] = new GlobalSearchResult(
                title: $doc->title,
                url: $doc->url ?? '#',
                details: $this->getDetails($doc),
            );
        }

        foreach ($categories as $name => $items) {
            $results->category($name, $items);
        }

        return $results;
    }

    protected function getCategoryName(string $type): string
    {
        return match ($type) {
            'admin.resource' => __('admin.search.categories.resources'),
            default => __('admin.search.categories.other'),
        };
    }

    protected function getDetails(AiDocument $doc): array
    {
        $details = [];

        // Pokud je v metadatech uložena skupina v menu, přidáme ji
        if (isset($doc->metadata['group'])) {
            $details[__('admin.search.details.group')] = $doc->metadata['group'];
        }

        // AI vygenerované shrnutí (pokud existuje) nebo náhled obsahu
        $summary = $doc->summary ?: mb_substr(strip_tags($doc->content), 0, 100).'...';
        $details[__('admin.search.details.content')] = $summary;

        return $details;
    }
}
