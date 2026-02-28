<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiChunk extends Model
{
    use HasFactory;

    protected $fillable = [
        'ai_document_id',
        'section',
        'chunk_index',
        'chunk_text',
        'chunk_hash',
        'embedding',
        'token_estimate',
    ];

    protected $casts = [
        'embedding' => 'json',
    ];

    public function document()
    {
        return $this->belongsTo(AiDocument::class, 'ai_document_id');
    }
}
