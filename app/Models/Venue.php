<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Venue extends Model
{
    protected $fillable = [
        'name',
        'address',
        'city',
        'zip',
        'latitude',
        'longitude',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    public function matches(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(BasketballMatch::class);
    }

    public function teams(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Team::class, 'primary_venue_id');
    }

    public function opponents(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Opponent::class, 'primary_venue_id');
    }
}
