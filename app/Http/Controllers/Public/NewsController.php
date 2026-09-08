<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\Post;
use App\Services\BreadcrumbService;
use Illuminate\View\View;

class NewsController extends Controller
{
    public function index(BreadcrumbService $breadcrumbService): View
    {
        $posts = Post::with('category')
            ->where('status', 'published')
            ->where('is_visible', true)
            ->orderByRaw('COALESCE(publish_at, created_at) DESC')
            ->paginate(12);

        $page = Page::where('slug', 'novinky')->first();
        $breadcrumbs = $breadcrumbService->addHome()->add(__('general.nav.news'))->get();

        return view('public.news.index', compact('posts', 'page', 'breadcrumbs'));
    }

    public function show(BreadcrumbService $breadcrumbService, ?string $slug = null): View
    {
        if (is_null($slug)) {
            abort(404);
        }
        $post = Post::with(['category', 'seo'])
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
