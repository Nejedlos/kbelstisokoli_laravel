<?php

namespace App\Services\Media;

use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\PathGenerator\PathGenerator;

class CustomPathGenerator implements PathGenerator
{
    /**
     * Získá cestu k médiu.
     */
    public function getPath(Media $media): string
    {
        return $this->getBasePath($media) . '/';
    }

    /**
     * Získá cestu pro konverze.
     */
    public function getPathForConversions(Media $media): string
    {
        return $this->getBasePath($media) . '/conversions/';
    }

    /**
     * Získá cestu pro responzivní obrázky.
     */
    public function getPathForResponsiveImages(Media $media): string
    {
        return $this->getBasePath($media) . '/responsive-images/';
    }

    /**
     * Základní cesta pro médium.
     * Formát: uploads/media/{model}/{model_id}/{collection}/{id}
     * Pro vybrané modely používáme zjednodušenou cestu.
     */
    protected function getBasePath(Media $media): string
    {
        $uploadsRoot = trim(config('filesystems.uploads.dir', 'uploads'), '/');
        $modelName = class_basename($media->model_type);

        // Zjednodušená cesta pro avatary uživatelů
        if ($modelName === 'User' && $media->collection_name === 'avatar') {
            return "{$uploadsRoot}/avatars/{$media->model_id}";
        }

        // Pro MediaAsset se snažíme najít, jestli nepatří do PhotoPoolu (pro lepší organizaci na disku)
        if ($modelName === 'MediaAsset') {
            /** @var \App\Models\MediaAsset $asset */
            $asset = $media->model;

            if ($asset instanceof \App\Models\MediaAsset) {
                // Pokud má uploader_id === null a kolekce je default, považujeme to za systémovou věc (defaults)
                // (např. výchozí avatary synchronizované příkazem)
                if ($asset->uploaded_by_id === null && $media->collection_name === 'default') {
                    return "{$uploadsRoot}/defaults/{$media->id}";
                }

                // Pokud patří do PhotoPoolu, dáme ho do složky daného poolu (podle prvního nalezeného)
                $pool = $asset->photoPools()->first();
                if ($pool) {
                    return "{$uploadsRoot}/photo_pools/{$pool->id}/{$media->id}";
                }
            }
        }

        $model = Str::snake($modelName);
        $modelId = $media->model_id;
        $collection = $media->collection_name ?: 'default';
        $id = $media->uuid ?: $media->id;

        // Výchozí struktura: uploads/{model}/{model_id}/{collection}/{id}
        // Odstraněn segment /media/ pro zjednodušení dle požadavku "uploads/model/id"
        return "{$uploadsRoot}/{$model}/{$modelId}/{$collection}/{$id}";
    }
}
