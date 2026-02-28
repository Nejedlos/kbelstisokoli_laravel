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

        // Zjednodušená cesta pro výchozí avatary v galerii
        if ($modelName === 'MediaAsset' && $media->collection_name === 'default') {
            return "{$uploadsRoot}/defaults/{$media->id}";
        }

        // Zjednodušená cesta pro avatary uživatelů
        if ($modelName === 'User' && $media->collection_name === 'avatar') {
            return "{$uploadsRoot}/avatars/{$media->model_id}";
        }

        $model = Str::snake($modelName);
        $modelId = $media->model_id;
        $collection = $media->collection_name ?: 'default';
        $id = $media->uuid ?: $media->id;

        return "{$uploadsRoot}/media/{$model}/{$modelId}/{$collection}/{$id}";
    }
}
