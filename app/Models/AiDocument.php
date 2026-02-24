<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiDocument extends Model
{
    use HasFactory;

    protected $table = 'ai_documents';

    protected $fillable = [
        'type',
        'source',
        'title',
        'url',
        'locale',
        'content',
        'keywords',
        'metadata',
        'checksum',
    ];

    protected $casts = [
        'keywords' => 'array',
        'metadata' => 'array',
    ];
}
