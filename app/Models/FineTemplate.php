<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class FineTemplate extends Model
{
    use HasTranslations;

    protected $fillable = [
        'name',
        'default_amount',
        'unit',
        'description',
        'metadata',
    ];

    public $translatable = ['name', 'description'];

    protected $casts = [
        'default_amount' => 'decimal:2',
        'metadata' => 'array',
    ];
}
