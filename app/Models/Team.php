<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Spatie\Translatable\HasTranslations;

class Team extends Model
{
    use HasTranslations;

    protected $fillable = [
        'name',
        'slug',
        'category',
        'description',
    ];

    public $translatable = ['name', 'description'];

    public function games(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(BasketballMatch::class, 'team_id');
    }

    public function trainings(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Training::class);
    }

    /**
     * Hráči v týmu.
     */
    public function players(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(PlayerProfile::class, 'player_profile_team')
            ->withPivot(['role_in_team', 'is_primary_team', 'active_from', 'active_to'])
            ->withTimestamps();
    }

    /**
     * Trenéři přiřazení k tomuto týmu.
     */
    public function coaches(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(User::class, 'coach_team')
            ->withPivot(['email'])
            ->withTimestamps();
    }
}
