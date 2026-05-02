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
        'keywords' => 'json',
        'metadata' => 'json',
        'is_active' => 'boolean',
        'last_indexed_at' => 'datetime',
    ];

    /**
     * Získá lokalizovanou hodnotu z atributu, který může být string nebo JSON pole.
     */
    public function getLocalizedValue(string $attribute): string
    {
        $value = $this->getAttribute($attribute);

        if (is_array($value)) {
            $locale = app()->getLocale();
            return $value[$locale] ?? $value['cs'] ?? array_values($value)[0] ?? '';
        }

        if (is_string($value)) {
            if (str_starts_with($value, '{') || str_starts_with($value, '[')) {
                $decoded = json_decode($value, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $locale = app()->getLocale();
                    return $decoded[$locale] ?? $decoded['cs'] ?? array_values($decoded)[0] ?? '';
                }
            }
            return $value;
        }

        return (string) ($value ?? '');
    }

    public function chunks()
    {
        return $this->hasMany(AiChunk::class);
    }
}
