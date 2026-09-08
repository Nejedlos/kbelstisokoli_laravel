<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Opponent extends Model
{
    protected $fillable = [
        'name',
        'city',
        'primary_venue_id',
        'logo',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function matches(): HasMany
    {
        return $this->hasMany(BasketballMatch::class, 'opponent_id');
    }

    public function primaryVenue(): BelongsTo
    {
        return $this->belongsTo(Venue::class, 'primary_venue_id');
    }
}
