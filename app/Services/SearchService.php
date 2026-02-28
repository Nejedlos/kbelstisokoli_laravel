<?php

namespace App\Services;

use App\DataTransferObjects\SearchResult;
use App\Models\AiDocument;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class SearchService
{
    public function __construct(
        protected AiIndexService $aiIndexService
    ) {}

    /**
     * @return Collection<SearchResult>
     */
    public function search(string $query, int $limit = 20, string $section = 'frontend'): Collection
    {
        if (empty($query) || strlen($query) < 2) {
            return collect();
        }

        $locale = app()->getLocale();
        $q = Str::lower($query);

        // FULLTEXT vyhledávání v DB s filtrováním sekce
        $queryBuilder = AiDocument::query()
            ->where('locale', $locale)
            ->where('section', $section)
            ->where('is_active', true);

        if (config('database.default') === 'mysql') {
            $queryBuilder->whereRaw('MATCH(title, content) AGAINST(? IN NATURAL LANGUAGE MODE)', [$q]);
        } else {
            $queryBuilder->where(function ($w) use ($q) {
                $w->whereRaw('LOWER(title) LIKE ?', ['%'.$q.'%'])
                    ->orWhereRaw('LOWER(content) LIKE ?', ['%'.$q.'%'])
                    ->orWhereRaw('LOWER(keywords) LIKE ?', ['%'.$q.'%']);
            });
        }

        $results = $queryBuilder->limit($limit)->get();

        return $results->map(function ($doc) {
            return new SearchResult(
                title: $doc->title,
                snippet: $doc->summary ?: $this->makeSnippet($doc->content),
                url: $doc->url,
                type: $this->getDocTypeLabel($doc->type),
                image: $doc->metadata['image'] ?? null,
                date: $doc->updated_at?->format('d.m.Y'),
            );
        });
    }

    protected function getDocTypeLabel(string $type): string
    {
        return match ($type) {
            'frontend.resource' => __('search.types.page'),
            default => __('search.types.general'),
        };
    }

    private function makeSnippet(string|array|null $content): string
    {
        if (! $content) {
            return '';
        }

        if (is_array($content)) {
            $content = $this->flattenBlocks($content);
        }

        // Odstranění HTML tagů a zkrácení
        $text = strip_tags((string) $content);
        $text = preg_replace('/\s+/', ' ', $text); // Odstranění nadbytečných bílých znaků

        return Str::limit(trim($text), 160);
    }

    /**
     * Převede pole bloků na prostý text.
     */
    private function flattenBlocks(array $blocks): string
    {
        $text = [];
        foreach ($blocks as $block) {
            if (isset($block['data']) && is_array($block['data'])) {
                $text[] = $this->extractStrings($block['data']);
            }
        }

        return implode(' ', array_filter($text));
    }

    /**
     * Rekurzivně vyextrahuje všechny řetězce z pole.
     */
    private function extractStrings(array $data): string
    {
        $strings = [];
        foreach ($data as $value) {
            if (is_string($value)) {
                $strings[] = $value;
            } elseif (is_array($value)) {
                $strings[] = $this->extractStrings($value);
            }
        }

        return implode(' ', array_filter($strings));
    }
}
