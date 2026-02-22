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
                title: $page->title,
                snippet: $this->makeSnippet($page->content),
                url: route('public.page', $page->slug),
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
                title: $post->title,
                snippet: $this->makeSnippet($post->excerpt ?: $post->content),
                url: route('public.post.show', $post->slug),
                type: __('search.types.post'),
                image: $post->featured_image,
                date: $post->publish_at?->format('d.m.Y') ?: $post->created_at->format('d.m.Y'),
            ));
        }

        return $results->take($limit);
    }

    private function makeSnippet(?string $content): string
    {
        if (!$content) return '';

        // Odstranění HTML tagů a zkrácení
        $text = strip_tags($content);
        return Str::limit($text, 160);
    }
}
