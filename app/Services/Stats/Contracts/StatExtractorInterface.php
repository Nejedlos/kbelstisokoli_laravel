<?php

namespace App\Services\Stats\Contracts;

interface StatExtractorInterface
{
    /**
     * Extrahuje surová data a relevantní HTML fragment z obsahu.
     * Vrací pole: ['data' => array, 'fragment_html' => string]
     */
    public function extract(string $content, array $config): array;
}
