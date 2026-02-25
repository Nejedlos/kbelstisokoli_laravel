<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Traits\Auditable;
use Spatie\Translatable\HasTranslations;

class Setting extends Model
{
    use HasTranslations, Auditable;

    protected $fillable = ['key', 'value'];

    public $translatable = ['value'];

    protected static function booted()
    {
        static::saved(function ($setting) {
            try {
                app(\App\Services\BrandingService::class)->clearCache();
            } catch (\Throwable $e) {
                // Ignorovat během migrací/seedování pokud služba není připravena
            }
        });
    }
}
