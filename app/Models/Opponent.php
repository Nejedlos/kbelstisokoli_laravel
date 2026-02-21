<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Opponent extends Model
{
    protected $fillable = [
        'name',
        'city',
        'logo',
    ];

    public function games(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(BasketballMatch::class, 'opponent_id');
    }
}
