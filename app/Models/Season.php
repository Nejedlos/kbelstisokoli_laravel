<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Season extends Model
{
    protected $fillable = [
        'name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Vrátí název sezóny, která by měla být aktuální podle data.
     * Sezóna začíná 1. září.
     */
    public static function getExpectedCurrentSeasonName(): string
    {
        $now = now();
        $year = $now->year;

        if ($now->month < 9) {
            // Jsme před zářím, sezóna začala loni
            return ($year - 1).'/'.$year;
        }

        // Jsme v září nebo později, sezóna začala letos
        return $year.'/'.($year + 1);
    }

    /**
     * Vrátí název předchozí sezóny.
     */
    public static function getPreviousSeasonName(): string
    {
        $now = now();
        $year = $now->year;

        if ($now->month < 9) {
            // Aktuální je (year-1)/year, předchozí je (year-2)/(year-1)
            return ($year - 2).'/'.($year - 1);
        }

        // Aktuální je year/(year+1), předchozí je (year-1)/year
        return ($year - 1).'/'.$year;
    }

    /**
     * Vrátí název předchozí sezóny z libovolného názvu.
     */
    public static function getPreviousSeasonNameFrom(string $name): string
    {
        if (str_contains($name, '/')) {
            [$year1, $year2] = explode('/', $name);
            if (is_numeric($year1) && is_numeric($year2)) {
                return ($year1 - 1).'/'.($year2 - 1);
            }
        }

        return $name;
    }

    public function games(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(BasketballMatch::class, 'season_id');
    }
}
