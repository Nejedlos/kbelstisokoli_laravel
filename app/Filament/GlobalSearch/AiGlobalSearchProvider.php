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
        // Omezíme na admin dokumenty a dokumentaci
        $documents = AiDocument::where('locale', $locale)
            ->where(function ($q) {
                $q->where('type', 'like', 'admin.%')
                  ->orWhere('type', 'like', 'documentation.%');
            })
            ->where(function ($q) use ($query) {
                $q->where('title', 'LIKE', "%{$query}%")
                    ->orWhere('content', 'LIKE', "%{$query}%")
                    ->orWhere('keywords', 'LIKE', "%{$query}%")
                    ->orWhere('summary', 'LIKE', "%{$query}%");
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
                title: (string) $doc->getLocalizedValue('title'),
                url: $doc->url ?? '#',
                details: $this->getDetails($doc),
            );
        }

        foreach ($categories as $name => $items) {
            $results->category($name, $items);
        }

        return $results;
    }

    protected function getLocalizedValue(mixed $value): string
    {
        if (is_array($value)) {
            $locale = App::getLocale();

            return $value[$locale] ?? $value['cs'] ?? array_values($value)[0] ?? '';
        }

        if (is_string($value)) {
            // Pokud je to JSON string (začíná { nebo [), zkusíme ho dekódovat
            if (str_starts_with($value, '{') || str_starts_with($value, '[')) {
                $decoded = json_decode($value, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $locale = App::getLocale();

                    return $decoded[$locale] ?? $decoded['cs'] ?? array_values($decoded)[0] ?? '';
                }
            }

            return $value;
        }

        return (string) ($value ?? '');
    }

    protected function getCategoryName(string $type): string
    {
        if (str_starts_with($type, 'documentation.')) {
            return __('admin.search.categories.documentation');
        }

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
            $group = $doc->metadata['group'];
            $details[__('admin.search.details.group')] = is_array($group) ? (string) json_encode($group) : (string) $group;
        }

        // AI vygenerované shrnutí (pokud existuje) nebo náhled obsahu
        $summary = $doc->getLocalizedValue('summary') ?: mb_substr(strip_tags($doc->getLocalizedValue('content')), 0, 100).'...';
        $details[__('admin.search.details.content')] = (string) $summary;

        return $details;
    }
}
