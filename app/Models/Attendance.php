<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Attendance extends Model
{
    protected $fillable = [
        'user_id',
        'attendable_id',
        'attendable_type',
        'planned_status',
        'actual_status',
        'is_mismatch',
        'note',
        'internal_note',
        'responded_at',
        'metadata',
    ];

    protected $casts = [
        'responded_at' => 'datetime',
        'is_mismatch' => 'boolean',
        'metadata' => 'array',
    ];

    /**
     * Automatický výpočet mismatch při ukládání.
     */
    protected static function booted()
    {
        static::saving(function ($attendance) {
            if ($attendance->shouldTrackAttendance()) {
                $attendance->is_mismatch = self::calculateMismatch($attendance->planned_status, $attendance->actual_status);
            } else {
                $attendance->is_mismatch = false;
            }
        });
    }

    /**
     * Rozhodne, zda se má pro tohoto uživatele v dané sezóně sledovat docházka.
     */
    public function shouldTrackAttendance(): bool
    {
        $seasonId = $this->getSeasonId();

        if (!$seasonId) {
            return true;
        }

        $config = UserSeasonConfig::where('user_id', $this->user_id)
            ->where('season_id', $seasonId)
            ->first();

        // Pokud config neexistuje, defaultně trackujeme (nebo ne?)
        // Prompt říká: "Pokud config neexistuje ... nastav is_mismatch = false."
        if (!$config) {
            return false;
        }

        return (bool) $config->track_attendance;
    }

    /**
     * Pokusí se zjistit sezónu pro událost.
     */
    public function getSeasonId(): ?int
    {
        $attendable = $this->attendable;

        if (!$attendable) {
            // Pokud není relace načtená, zkusíme ji načíst (v booted saving by měla být dostupná nebo načitelná)
            $attendable = $this->attendable()->first();
        }

        if ($attendable && isset($attendable->season_id)) {
            return $attendable->season_id;
        }

        // Pro tréninky a jiné události bez season_id zkusíme aktivní sezónu
        // Do budoucna by bylo lepší mít season_id i u tréninků nebo Season::forDate()
        return Season::where('is_active', true)->first()?->id;
    }

    /**
     * Logika pro určení, zda je v docházce rozpor.
     */
    public static function calculateMismatch(?string $planned, ?string $actual): bool
    {
        // Pokud trenér ještě nezapsal realitu, není co porovnávat (není mismatch)
        if (empty($actual)) {
            return false;
        }

        // Hráč potvrdil, ale trenér zapsal nepřítomen
        if ($planned === 'confirmed' && $actual === 'absent') {
            return true;
        }

        // Hráč odmítl, ale trenér zapsal přítomen
        if ($planned === 'declined' && $actual === 'attended') {
            return true;
        }

        // Hráč se nevyjádřil (pending), ale trenér zapsal cokoli
        // (Bereme jako mismatch, protože hráč má povinnost se vyjádřit)
        if (($planned === 'pending' || empty($planned)) && !empty($actual)) {
            return true;
        }

        return false;
    }

    /**
     * Získá vlastníka docházky.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Polymorfní vazba na událost (Training, BasketballMatch, ClubEvent).
     */
    public function attendable(): MorphTo
    {
        return $this->morphTo();
    }
}
