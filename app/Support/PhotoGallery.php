<?php

namespace App\Support;

use App\Models\MediaAsset;
use App\Models\PhotoPool;
use Illuminate\Database\Eloquent\Collection;

class PhotoGallery
{
    /**
     * Získá náhodné fotografie z poolů.
     *
     * @param  int  $limit  Počet fotografií
     * @param  int|null  $teamId  ID týmu pro filtraci
     */
    public static function getRandomPhotos(int $limit = 8, ?int $teamId = null): Collection
    {
        $query = MediaAsset::query()
            ->select('media_assets.*')
            ->join('photo_pool_media_asset', 'media_assets.id', '=', 'photo_pool_media_asset.media_asset_id')
            ->join('photo_pools', 'photo_pool_media_asset.photo_pool_id', '=', 'photo_pools.id')
            ->where('photo_pools.is_public', true)
            ->where('photo_pools.is_visible', true)
            ->where('photo_pool_media_asset.is_visible', true)
            ->where('media_assets.is_public', true);

        if ($teamId) {
            $query->where('photo_pools.team_id', $teamId);
        }

        return $query->inRandomOrder()
            ->limit($limit)
            ->get();
    }

    /**
     * Získá všechny viditelné pooly jako galerie.
     */
    public static function getPools()
    {
        return PhotoPool::where('is_public', true)
            ->where('is_visible', true)
            ->orderBy('event_date', 'desc')
            ->paginate(12);
    }
}
