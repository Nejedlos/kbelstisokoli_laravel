<?php

namespace App\Services\Stats\Sync;

use App\Models\Opponent;

class OpponentSyncService
{
    /**
     * Synchronizuje soupeře v databázi.
     */
    public function sync(string $name, ?string $city = null, ?string $externalId = null): Opponent
    {
        $name = trim($name);
        $city = $city ? trim($city) : null;

        $opponent = null;

        // Pro maximální kompatibilitu s produkční DB (Webglobe), která může mít problém s JSON funkcemi,
        // načteme všechny soupeře do paměti a vyhledáme je tam. Soupeřů je jen pár stovek.
        $allOpponents = Opponent::all();

        // 1. Zkusíme hledat podle external_id v metadatech
        if ($externalId) {
            $opponent = $allOpponents->first(function ($op) use ($externalId) {
                return ($op->metadata['external_id'] ?? null) == $externalId;
            });
        }

        // 2. Pokud nemáme external_id nebo jsme nenašli, hledáme podle jména nebo variant v metadatech
        if (! $opponent) {
            // Nejprve zkusíme přesnou shodu jména
            $opponent = $allOpponents->firstWhere('name', $name);

            if (! $opponent) {
                // Pokud nemáme přesnou shodu, prohledáme varianty jmen v paměti.
                $opponent = $allOpponents->first(function ($op) use ($name) {
                    $metadata = $op->metadata;
                    $variants = $metadata['external_name_variants'] ?? [];

                    return in_array($name, (array) $variants);
                });
            }
        }

        // 3. Poslední záchrana: Pokud jsme stále nenašli, zkusíme najít, zda neexistuje přijatý merge
        // pro toto jméno v minulosti (pokud bychom ho museli vytvořit znovu).
        // Poznámka: Toto je spíše pojistka, protože varianty jmen by měly být v metadatech cíle.

        if (! $opponent) {
            $opponent = Opponent::create([
                'name' => $name,
                'city' => $city,
                'metadata' => [
                    'source_key' => 'czbasketball',
                    'last_seen_at' => now()->toDateTimeString(),
                    'external_name_variants' => [$name],
                    'external_id' => $externalId,
                ],
            ]);
        } else {
            $metadata = $opponent->metadata ?? [];
            $metadata['source_key'] = 'czbasketball';
            $metadata['last_seen_at'] = now()->toDateTimeString();
            if ($externalId) {
                $metadata['external_id'] = $externalId;
            }

            $variants = $metadata['external_name_variants'] ?? [];
            if (! in_array($name, $variants)) {
                $variants[] = $name;
            }
            $metadata['external_name_variants'] = $variants;

            $opponent->update(['metadata' => $metadata]);
        }

        return $opponent;
    }
}
