<?php

namespace App\Services\Stats\Contracts;

use App\Services\Stats\DTO\NormalizedTableDTO;

interface StatNormalizerInterface
{
    /**
     * Normalizuje surová data (např. fragment HTML) do DTO pomocí AI/LLM.
     *
     * @param  string  $content Fragment HTML k parsování.
     * @param  array  $mappingConfig Konfigurace cílových sloupců (canonical keys).
     */
    public function normalize(string $content, array $mappingConfig): NormalizedTableDTO;
}
