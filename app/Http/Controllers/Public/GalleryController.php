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
            ->where(function ($query) {
                $query->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            })
            ->orderBy('published_at', 'desc')
            ->get();

        $pools = \App\Models\PhotoPool::with(['mediaAssets' => function ($query) {
            $query->where('media_assets.is_public', true)
                ->where('photo_pool_media_asset.is_visible', true)
                ->orderBy('photo_pool_media_asset.sort_order');
        }])
            ->where('is_public', true)
            ->where('is_visible', true)
            ->orderBy('event_date', 'desc')
            ->get();

        // Náhodný výběr fotek pro celou stránku
        $randomPhotos = \App\Support\PhotoGallery::getRandomPhotos(12);

        return view('public.galleries.index', compact('galleries', 'pools', 'randomPhotos'));
    }

    public function show(string $slug): View
    {
        // Nejdřív zkusíme starou galerii
        $gallery = Gallery::with(['mediaAssets', 'coverAsset'])
            ->where('slug', $slug)
            ->where('is_public', true)
            ->first();

        if ($gallery) {
            return view('public.galleries.show', compact('gallery'));
        }

        // Pak zkusíme Photo Pool (sbírku)
        $pool = \App\Models\PhotoPool::with(['mediaAssets' => function ($query) {
            $query->where('media_assets.is_public', true)
                ->where('photo_pool_media_asset.is_visible', true)
                ->orderBy('photo_pool_media_asset.sort_order');
        }])
            ->where('slug', $slug)
            ->where('is_public', true)
            ->firstOrFail();

        return view('public.galleries.show_pool', compact('pool'));
    }
}
