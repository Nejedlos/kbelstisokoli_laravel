<?php

namespace App\Services;

use App\Models\Page;
use App\Models\Post;
use App\DataTransferObjects\SearchResult;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class SearchService
{
    /**
     * @param string $query
     * @param int $limit
     * @return Collection<SearchResult>
     */
    public function search(string $query, int $limit = 20): Collection
    {
        if (empty($query) || strlen($query) < 3) {
            return collect();
        }

        $results = collect();

        // Prohledávání stránek
        $pages = Page::query()
            ->where('is_visible', true)
            ->where(function ($q) use ($query) {
                $q->where('title->' . app()->getLocale(), 'like', "%{$query}%")
                  ->orWhere('content->' . app()->getLocale(), 'like', "%{$query}%");
            })
            ->limit($limit)
            ->get();

        foreach ($pages as $page) {
            $results->push(new SearchResult(
                title: $page->getTranslation('title', app()->getLocale()),
                snippet: $this->makeSnippet($page->getTranslation('content', app()->getLocale())),
                url: $page->slug === 'home' ? route('public.home') : route('public.pages.show', $page->slug),
                type: __('search.types.page'),
                date: $page->updated_at->format('d.m.Y'),
            ));
        }

        // Prohledávání příspěvků
        $posts = Post::query()
            ->where('is_visible', true)
            ->where('status', 'published') // Předpokládáme, že existuje status
            ->where(function ($q) use ($query) {
                $q->where('title->' . app()->getLocale(), 'like', "%{$query}%")
                  ->orWhere('excerpt->' . app()->getLocale(), 'like', "%{$query}%")
                  ->orWhere('content->' . app()->getLocale(), 'like', "%{$query}%");
            })
            ->limit($limit)
            ->get();

        foreach ($posts as $post) {
            $results->push(new SearchResult(
                title: $post->getTranslation('title', app()->getLocale()),
                snippet: $this->makeSnippet($post->getTranslation('excerpt', app()->getLocale()) ?: $post->getTranslation('content', app()->getLocale())),
                url: route('public.news.show', $post->slug),
                type: __('search.types.post'),
                image: $post->featured_image,
                date: $post->publish_at?->format('d.m.Y') ?: $post->created_at->format('d.m.Y'),
            ));
        }

        return $results->take($limit);
    }

    private function makeSnippet(string|array|null $content): string
    {
        if (!$content) {
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
