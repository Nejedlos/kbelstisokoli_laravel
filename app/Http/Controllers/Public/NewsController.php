<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class NewsController extends Controller
{
    public function index(\App\Services\BreadcrumbService $breadcrumbService): View
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

        $breadcrumbs = $breadcrumbService->addHome()->add(__('nav.news'))->get();

        return view('public.news.index', compact('posts', 'breadcrumbs'));
    }

    public function show(string $slug, \App\Services\BreadcrumbService $breadcrumbService): View
    {
        $post = \App\Models\Post::with(['category', 'seo'])
            ->where('slug', $slug)
            ->where('status', 'published')
            ->where('is_visible', true)
            ->firstOrFail();

        return view('public.news.show', [
            'post' => $post,
            'head_code' => $post->head_code,
            'footer_code' => $post->footer_code,
            'breadcrumbs' => $breadcrumbService->generateForPost($post)->get(),
        ]);
    }
}
