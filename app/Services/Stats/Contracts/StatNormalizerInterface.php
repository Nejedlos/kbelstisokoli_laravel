<?php

namespace App\Services\Stats\Contracts;

use App\Services\Stats\DTO\NormalizedTableDTO;

interface StatNormalizerInterface
{
    /**
     * Normalizuje surová data do DTO (zde je místo pro budoucí AI logiku).
     */
    public function normalize(array $rawData, array $mappingConfig): NormalizedTableDTO;
}
