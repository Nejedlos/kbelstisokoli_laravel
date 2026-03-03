<?php

namespace App\Services\Stats\Sync;

use App\Models\Opponent;
use Illuminate\Support\Str;

class OpponentSyncService
{
    /**
     * Synchronizuje soupeře v databázi.
     *
     * @param string $name
     * @param string|null $city
     * @return Opponent
     */
    public function sync(string $name, ?string $city = null): Opponent
    {
        $name = trim($name);
        $city = $city ? trim($city) : null;

        // Pro hledání použijeme case-insensitive porovnání (v MySQL default, v Postgres ne nutně)
        // Ale pro jistotu a přehlednost budeme hledat přes Eloquent.
        $query = Opponent::where('name', $name);

        if ($city) {
            $query->where('city', $city);
        } else {
            $query->whereNull('city');
        }

        $opponent = $query->first();

        if (!$opponent) {
            // Zkusíme najít aspoň podle jména, pokud město nesouhlasí?
            // Raději budeme striktní podle zadání "name + city pokud existuje".
            $opponent = Opponent::create([
                'name' => $name,
                'city' => $city,
                'metadata' => [
                    'source_key' => 'czbasketball',
                    'last_seen_at' => now()->toDateTimeString(),
                    'external_name_variants' => [$name],
                ],
            ]);
        } else {
            $metadata = $opponent->metadata ?? [];
            $metadata['source_key'] = 'czbasketball';
            $metadata['last_seen_at'] = now()->toDateTimeString();

            $variants = $metadata['external_name_variants'] ?? [];
            if (!in_array($name, $variants)) {
                $variants[] = $name;
            }
            $metadata['external_name_variants'] = $variants;

            $opponent->update(['metadata' => $metadata]);
        }

        return $opponent;
    }
}
