<?php

namespace App\Models;

use Spatie\Permission\Models\Permission as SpatiePermission;
use Spatie\Translatable\HasTranslations;

class Permission extends SpatiePermission
{
    use HasTranslations;

    public $translatable = ['display_name'];

    /**
     * Získá název pro zobrazení (fallback na name).
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->getTranslation('display_name', app()->getLocale()) ?: $this->name;
    }
}
