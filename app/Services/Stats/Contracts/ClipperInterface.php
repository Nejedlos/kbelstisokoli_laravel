<?php

namespace App\Services\Stats\Contracts;

use App\Services\Stats\DTO\ClipDTO;

/**
 * Interface pro vyřezávání fragmentů z HTML pro AI analýzu.
 */
interface ClipperInterface
{
    /**
     * Vyřízne relevantní fragmenty ze stránek cz.basketball.
     *
     * @param string $html Kompletní HTML zdroj
     * @param string|null $baseUrl Základní URL pro absolutizaci odkazů
     * @return array<ClipDTO>
     */
    public function clip(string $html, ?string $baseUrl = null): array;
}
