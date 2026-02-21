<?php

namespace App\Models;

use App\Traits\HasSeo;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    use HasSeo;

    protected $fillable = [
        'title',
        'slug',
        'content',
        'status',
        'is_visible',
    ];

    protected $casts = [
        'content' => 'array',
                'is_visible' => 'boolean',
    ];
}
