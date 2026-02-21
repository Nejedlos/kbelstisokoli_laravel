<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    protected $fillable = [
        'title',
        'message',
        'cta_label',
        'cta_url',
        'is_active',
        'audience',
        'style_variant',
        'starts_at',
        'ends_at',
        'priority',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'priority' => 'integer',
    ];

    /**
     * Scope pro aktivní oznámení v daném čase.
     */
    public function scopeActive($query)
    {
        $now = now();

        return $query->where('is_active', true)
            ->where(function ($q) use ($now) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', $now);
            });
    }

    /**
     * Scope pro konkrétní publikum.
     */
    public function scopeForAudience($query, string $audience)
    {
        return $query->whereIn('audience', [$audience, 'both']);
    }
}
