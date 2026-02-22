<?php

namespace App\Services\Media;

use Spatie\MediaLibrary\Support\UrlGenerator\DefaultUrlGenerator;

class CustomUrlGenerator extends DefaultUrlGenerator
{
    /**
     * Získá URL k médiu.
     * Pokud je na privátním disku, vrátí zabezpečený odkaz.
     */
    public function getUrl(): string
    {
        if ($this->media->disk === 'media_private') {
            return route('media.download', ['uuid' => $this->media->uuid]);
        }

        return parent::getUrl();
    }

    /**
     * Získá cestu pro stažení (pro interní potřeby).
     */
    public function getPath(): string
    {
        return parent::getPath();
    }
}
