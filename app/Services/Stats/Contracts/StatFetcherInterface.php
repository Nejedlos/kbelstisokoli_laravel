<?php

namespace App\Services\Stats\Contracts;

interface StatFetcherInterface
{
    /**
     * Stáhne obsah z dané URL.
     */
    public function fetch(string $url): string;
}
