<?php

namespace App\Models;

use App\Services\BrandingService;
use App\Services\PerformanceService;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Setting extends Model
{
    use Auditable, HasTranslations;

    protected $fillable = ['key', 'value'];

    public $translatable = ['value'];

    protected static function booted()
    {
        static::saved(function ($setting) {
            try {
                app(BrandingService::class)->clearCache();
                app(PerformanceService::class)->clearCache();
            } catch (\Throwable $e) {
                // Ignorovat během migrací/seedování pokud služba není připravena
            }
        });
    }
}
