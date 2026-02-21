<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PlayerProfile extends Model
{
    protected $fillable = [
        'user_id',
        'jersey_number',
        'position',
        'public_bio',
        'private_note',
        'is_active',
        'metadata',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'metadata' => 'array',
    ];

    /**
     * Uživatel, kterému profil patří.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Týmy, ve kterých hráč působí.
     */
    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'player_profile_team')
            ->withPivot(['role_in_team', 'is_primary_team', 'active_from', 'active_to'])
            ->withTimestamps();
    }
}
