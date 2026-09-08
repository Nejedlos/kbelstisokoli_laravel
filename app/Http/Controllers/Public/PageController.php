<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Services\BreadcrumbService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PageController extends Controller
{
    public function show(BreadcrumbService $breadcrumbService, ?string $slug = null): View|RedirectResponse
    {
        if (is_null($slug)) {
            abort(404);
        }
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
