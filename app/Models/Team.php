<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'category',
        'description',
    ];

    public function games(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(BasketballMatch::class, 'team_id');
    }

    public function trainings(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Training::class);
    }
}
