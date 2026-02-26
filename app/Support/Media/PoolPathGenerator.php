<?php

namespace App\Support\Media;

use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\PathGenerator\PathGenerator;

class PoolPathGenerator implements PathGenerator
{
    /**
     * Získá cestu k médiu pro PhotoPool a MediaAsset.
     * Cesta: pools/{pool-slug}/{original-filename}
     */
    public function getPath(Media $media): string
    {
        $base = $this->getBasePath($media);

        return $base.$media->id.'/';
    }

    public function getPathForConversions(Media $media): string
    {
        return $this->getPath($media).'conversions/';
    }

    public function getPathForResponsiveImages(Media $media): string
    {
        return $this->getPath($media).'responsive-images/';
    }

    protected function getBasePath(Media $media): string
    {
        // Pokud je model MediaAsset a je propojen s PhotoPool, můžeme zkusit získat slug poolu.
        // Ale v MediaLibrary je path generator globální nebo na úrovni modelu.

        if ($media->model_type === \App\Models\MediaAsset::class) {
            // Zkusíme najít první pool, ke kterému patří (pokud existuje)
            /** @var \App\Models\MediaAsset $asset */
            $asset = $media->model;
            $pool = $asset->photoPools()->first();

            if ($pool) {
                return 'pools/'.$pool->slug.'/';
            }
        }

        return 'media/';
    }
}
