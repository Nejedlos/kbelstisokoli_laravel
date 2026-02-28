<?php

namespace App\Services\Stats\Pipelines;

use App\Models\ExternalStatSource;
use App\Services\Stats\Contracts\StatExtractorInterface;
use App\Services\Stats\Contracts\StatFetcherInterface;
use App\Services\Stats\Contracts\StatNormalizerInterface;

class IngestPipeline
{
    public function __construct(
        protected StatFetcherInterface $fetcher,
        protected StatExtractorInterface $extractor,
        protected StatNormalizerInterface $normalizer
    ) {}

    /**
     * Spustí celou pipeline pro daný zdroj.
     */
    public function process(ExternalStatSource $source): void
    {
        // 1. Fetch
        $content = $this->fetcher->fetch($source->source_url);

        // 2. Extract
        $rawData = $this->extractor->extract($content, $source->extractor_config ?? []);

        // 3. Normalize (zde bude AI transformace)
        $normalizedDTO = $this->normalizer->normalize($rawData, $source->mapping_config ?? []);

        // 4. Import / Update DB (Implementace dle StatisticSet/Row)
        // ... logika uložení do DB ...

        $source->update([
            'last_run_at' => now(),
            'last_status' => 'success',
        ]);
    }
}
