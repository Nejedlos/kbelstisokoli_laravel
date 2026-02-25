<?php

namespace App\Services;

use App\Models\Gallery;
use App\Models\PhotoPool;
use Illuminate\Support\Facades\DB;

class GalleryService
{
    /**
     * Naplní galerii náhodně vybranými fotkami z poolu/poolů.
     * Pokud je $poolId null, bere se z veřejných a viditelných poolů.
     */
    public function fillFromPoolRandom(Gallery $gallery, int $count = 20, ?int $poolId = null): int
    {
        $rows = DB::table('photo_pool_media_asset as ppma')
            ->join('media_assets as ma', 'ma.id', '=', 'ppma.media_asset_id')
            ->join('photo_pools as pp', 'pp.id', '=', 'ppma.photo_pool_id')
            ->where('ppma.is_visible', true)
            ->where('ma.is_public', true)
            ->when($poolId, fn($q) => $q->where('pp.id', $poolId), function ($q) {
                $q->where('pp.is_public', true)->where('pp.is_visible', true);
            })
            ->select('ma.id as media_id', 'pp.title as pool_title', 'pp.event_date')
            ->inRandomOrder()
            ->limit($count)
            ->get();

        $attached = 0;
        $maxSort = (int) DB::table('gallery_media')->where('gallery_id', $gallery->id)->max('sort_order');

        foreach ($rows as $row) {
            $mediaId = $row->media_id;
            // Zabránit duplicitě
            $exists = DB::table('gallery_media')
                ->where('gallery_id', $gallery->id)
                ->where('media_asset_id', $mediaId)
                ->exists();
            if ($exists) {
                continue;
            }

            $caption = trim((string) brand_text($row->pool_title));
            if (!empty($row->event_date)) {
                $caption .= ' — ' . (string) optional(\Carbon\Carbon::parse($row->event_date))->format('d.m.Y');
            }

            DB::table('gallery_media')->insert([
                'gallery_id' => $gallery->id,
                'media_asset_id' => $mediaId,
                'sort_order' => ++$maxSort,
                'caption_override' => $caption ?: null,
                'is_visible' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $attached++;
        }

        return $attached;
    }
}
