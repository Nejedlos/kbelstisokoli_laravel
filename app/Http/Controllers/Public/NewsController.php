<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class NewsController extends Controller
{
    public function index(): View
    {
        $posts = \App\Models\Post::with('category')
            ->where('status', 'published')
            ->where('is_visible', true)
            ->where(function($query) {
                $query->whereNull('publish_at')
                      ->orWhere('publish_at', '<=', now());
            })
            ->orderBy('publish_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        return view('public.news.index', compact('posts'));
    }

    public function show(string $slug): View
    {
        $post = \App\Models\Post::with(['category', 'seo'])
            ->where('slug', $slug)
            ->where('status', 'published')
            ->where('is_visible', true)
            ->firstOrFail();

        // SEO data
        $seo = $post->seo;

        return view('public.news.show', [
            'post' => $post,
            'seo_title' => $seo?->title ?? $post->title,
            'seo_description' => $seo?->description ?? $post->excerpt,
            'seo_keywords' => $seo?->keywords,
            'og_title' => $seo?->og_title,
            'og_description' => $seo?->og_description,
            'og_image' => ($seo?->og_image) ? asset('storage/' . $seo->og_image) : (($post->featured_image) ? asset('storage/' . $post->featured_image) : null),
            'head_code' => $post->head_code,
            'footer_code' => $post->footer_code,
        ]);
    }
}
