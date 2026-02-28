<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiDocument extends Model
{
    use HasFactory;

    protected $table = 'ai_documents';

    protected $fillable = [
        'section',
        'type',
        'source',
        'source_type',
        'source_id',
        'title',
        'summary',
        'url',
        'locale',
        'content',
        'keywords',
        'metadata',
        'checksum',
        'content_hash',
        'is_active',
        'last_indexed_at',
    ];

    protected $casts = [
        'keywords' => 'array',
        'metadata' => 'array',
        'is_active' => 'boolean',
        'last_indexed_at' => 'datetime',
    ];

    public function chunks()
    {
        return $this->hasMany(AiChunk::class);
    }
}
