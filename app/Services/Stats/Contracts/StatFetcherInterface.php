<?php

namespace App\Services\Stats\Contracts;

use App\Models\ExternalImportRun;

interface StatFetcherInterface
{
    /**
     * Stáhne obsah z dané URL.
     */
    public function fetch(string $url, ?ExternalImportRun $run = null): string;
}
