<?php

namespace App\Services\Stats\Contracts;

interface StatExtractorInterface
{
    /**
     * Extrahuje surová data (např. pole polí) z obsahu na základě konfigurace.
     */
    public function extract(string $content, array $config): array;
}
