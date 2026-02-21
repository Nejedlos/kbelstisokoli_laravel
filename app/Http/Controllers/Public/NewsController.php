<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class NewsController extends Controller
{
    public function index(): View
    {
        return view('public.news.index');
    }

    public function show(string $slug): View
    {
        $post = \App\Models\Post::where('slug', $slug)
            ->where('status', 'published')
            ->firstOrFail();

        return view('public.news.show', [
            'post' => $post,
            'head_code' => $post->head_code,
            'footer_code' => $post->footer_code,
        ]);
    }
}
