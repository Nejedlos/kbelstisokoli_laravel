<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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

    public function matches(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(BasketballMatch::class, 'opponent_id');
    }

    public function primaryVenue(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Venue::class, 'primary_venue_id');
    }
}
