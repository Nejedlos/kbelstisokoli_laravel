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
     * Normalizuje název sezóny do jednotného formátu YYYY/YYYY.
     */
    public static function normalizeName(string $name): string
    {
        $name = trim($name);

        // 1. Podpora YYYY - YYYY, YYYY-YYYY, YYYY / YYYY -> YYYY/YYYY
        if (preg_match('/(\d{4})\s*[\-\/]\s*(\d{4})/', $name, $matches)) {
            return $matches[1].'/'.$matches[2];
        }

        // 2. Podpora YYYY - YY, YYYY-YY, YYYY / YY -> YYYY/20YY
        if (preg_match('/^(\d{4})\s*[\-\/]\s*(\d{2})$/', $name, $matches)) {
            return $matches[1].'/20'.$matches[2];
        }

        // 3. Podpora samotného roku YYYY -> YYYY/YYYY+1
        if (preg_match('/^(\d{4})$/', $name, $matches)) {
            $year = (int) $matches[1];

            return $year.'/'.($year + 1);
        }

        // 4. Odstranění vícenásobných mezer kolem lomítka, pokud už tam je
        if (str_contains($name, '/')) {
            return preg_replace('/\s*\/\s*/', '/', $name);
        }

        return $name;
    }

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

    public function matches(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(BasketballMatch::class, 'season_id');
    }
}
