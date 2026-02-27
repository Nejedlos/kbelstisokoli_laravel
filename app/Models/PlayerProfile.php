<?php

namespace App\Models;

use App\Enums\BasketballPosition;
use App\Enums\DominantHand;
use App\Enums\JerseySize;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PlayerProfile extends Model
{
    protected $fillable = [
        'user_id',
        'jersey_number',
        'preferred_jersey_number',
        'position',
        'dominant_hand',
        'height_cm',
        'weight_kg',
        'jersey_size',
        'shorts_size',
        'license_number',
        'medical_note',
        'coach_note',
        'public_bio',
        'private_note',
        'is_active',
        'valid_from',
        'valid_to',
        'joined_team_at',
        'primary_team_id',
        'metadata',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'valid_from' => 'date',
        'valid_to' => 'date',
        'metadata' => 'array',
        'position' => BasketballPosition::class,
        'dominant_hand' => DominantHand::class,
        'jersey_size' => JerseySize::class,
        'shorts_size' => JerseySize::class,
        'joined_team_at' => 'date',
    ];

    /**
     * Uživatel, kterému profil patří.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Primární tým.
     */
    public function primaryTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'primary_team_id');
    }

    /**
     * Týmy, ve kterých hráč působí.
     */
    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'player_profile_team')
            ->withPivot(['role_in_team', 'is_primary_team', 'is_on_roster', 'active_from', 'active_to'])
            ->withTimestamps();
    }

    /**
     * Scope pro profily platné k určitému datu.
     */
    public function scopeInEffect($query, $date = null)
    {
        $date = $date ?: now();
        return $query->where('is_active', true)
            ->where(function ($q) use ($date) {
                $q->whereNull('valid_from')
                    ->orWhere('valid_from', '<=', $date);
            })
            ->where(function ($q) use ($date) {
                $q->whereNull('valid_to')
                    ->orWhere('valid_to', '>=', $date);
            });
    }
}
