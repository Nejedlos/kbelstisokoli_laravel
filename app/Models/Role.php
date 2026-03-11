<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;
use Spatie\Translatable\HasTranslations;

class Role extends SpatieRole
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
