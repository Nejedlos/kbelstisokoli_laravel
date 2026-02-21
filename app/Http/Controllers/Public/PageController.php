<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Page;
use Illuminate\View\View;

class PageController extends Controller
{
    public function show(string $slug): View
    {
        $page = Page::with('seo')
            ->where('slug', $slug)
            ->where('status', 'published')
            ->where('is_visible', true)
            ->firstOrFail();

        $seo = $page->seo;

        return view('public.pages.show', [
            'page' => $page,
            'seo_title' => $seo?->title ?? $page->title,
            'seo_description' => $seo?->description,
            'seo_keywords' => $seo?->keywords,
            'og_title' => $seo?->og_title,
            'og_description' => $seo?->og_description,
            'og_image' => ($seo?->og_image) ? asset('storage/' . $seo->og_image) : null,
            'head_code' => $page->head_code,
            'footer_code' => $page->footer_code,
        ]);
    }
}
