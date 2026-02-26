<?php

namespace App\Services;

use App\DataTransferObjects\SearchResult;
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
    public function search(string $query, int $limit = 20): Collection
    {
        if (empty($query) || strlen($query) < 3) {
            return collect();
        }

        $locale = app()->getLocale();
        $aiResults = $this->aiIndexService->search($query, $locale, $limit, 'frontend');

        return $aiResults->map(function ($doc) {
            $aiDocument = is_array($doc) ? $doc[0] : $doc;

            return new SearchResult(
                title: $aiDocument->title,
                snippet: $aiDocument->summary ?: $this->makeSnippet($aiDocument->content),
                url: $aiDocument->url,
                type: $this->getDocTypeLabel($aiDocument->type),
                image: $aiDocument->metadata['image'] ?? null,
                date: $aiDocument->updated_at?->format('d.m.Y'),
            );
        });
    }

    protected function getDocTypeLabel(string $type): string
    {
        return match ($type) {
            'frontend.page' => __('search.types.page'),
            'frontend.post' => __('search.types.post'),
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
