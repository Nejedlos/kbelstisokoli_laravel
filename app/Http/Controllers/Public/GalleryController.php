<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Gallery;
use Illuminate\View\View;

class GalleryController extends Controller
{
    public function index(): View
    {
        $galleries = Gallery::with('coverAsset')
            ->where('is_public', true)
            ->where('is_visible', true)
            ->where(function($query) {
                $query->whereNull('published_at')
                      ->orWhere('published_at', '<=', now());
            })
            ->orderBy('published_at', 'desc')
            ->paginate(12);

        return view('public.galleries.index', compact('galleries'));
    }

    public function show(string $slug): View
    {
        $gallery = Gallery::with(['mediaAssets', 'coverAsset'])
            ->where('slug', $slug)
            ->where('is_public', true)
            ->firstOrFail();

        return view('public.galleries.show', compact('gallery'));
    }
}
