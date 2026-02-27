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
     */
    protected function getBasePath(Media $media): string
    {
        $uploadsRoot = trim(config('filesystems.uploads.dir', 'uploads'), '/');
        $model = Str::snake(class_basename($media->model_type));
        $modelId = $media->model_id;
        $collection = $media->collection_name ?: 'default';
        $id = $media->uuid ?: $media->id;

        return "{$uploadsRoot}/media/{$model}/{$modelId}/{$collection}/{$id}";
    }
}
