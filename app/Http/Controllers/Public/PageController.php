<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\View\View;

class PageController extends Controller
{
    public function show(string $slug, \App\Services\BreadcrumbService $breadcrumbService): View|\Illuminate\Http\RedirectResponse
    {
        // Homepage by měla být dostupná pouze na kořenové URL
        if ($slug === 'home') {
            return redirect()->route('public.home');
        }

        $page = Page::with('seo')
            ->where('slug', $slug)
            ->where('status', 'published')
            ->where('is_visible', true)
            ->firstOrFail();

        return view('public.pages.show', [
            'page' => $page,
            'head_code' => $page->head_code,
            'footer_code' => $page->footer_code,
            'breadcrumbs' => $breadcrumbService->generateForPage($page)->get(),
        ]);
    }
}
