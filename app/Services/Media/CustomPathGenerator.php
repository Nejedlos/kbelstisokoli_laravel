<?php

namespace App\Services\Media;

use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\PathGenerator\PathGenerator;

class CustomPathGenerator implements PathGenerator
{
    /*
     * Získá cestu k médiu.
     * Formát: {kolekce}/{rok}/{mesic}/{id}/
     */
    public function getPath(Media $media): string
    {
        return $this->getBasePath($media) . '/';
    }

    /*
     * Získá cestu pro konverze.
     */
    public function getPathForConversions(Media $media): string
    {
        return $this->getBasePath($media) . '/conversions/';
    }

    /*
     * Získá cestu pro responzivní obrázky.
     */
    public function getPathForResponsiveImages(Media $media): string
    {
        return $this->getBasePath($media) . '/responsive-images/';
    }

    /*
     * Základní cesta pro médium.
     */
    protected function getBasePath(Media $media): string
    {
        $date = $media->created_at ?? now();
        return $media->collection_name
            . '/' . $date->format('Y')
            . '/' . $date->format('m')
            . '/' . $media->id;
    }
}
