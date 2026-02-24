<?php

namespace App\Console\Commands;

use App\Services\AiIndexService;
use Illuminate\Console\Command;

class AiReindexCommand extends Command
{
    protected $signature = 'ai:index {--locale= : Jazyk indexu (cs/en/all)} {--fresh : Smazat existující index před reindexací} {--enrich : Obohatit dokumenty o AI klíčová slova (pomalé)}';

    protected $description = 'Rebuild or update AI search index from Blade views and docs';

    public function handle(AiIndexService $index): int
    {
        $locale = (string) $this->option('locale') ?: 'cs';
        $fresh = (bool) $this->option('fresh');
        $enrich = (bool) $this->option('enrich');

        $locales = $locale === 'all' ? ['cs', 'en'] : [$locale];

        foreach ($locales as $l) {
            $this->info("Indexing AI documents for locale '{$l}'" . ($fresh ? " (fresh)" : "") . "...");
            $count = $index->reindex($l, $fresh);
            $this->info("Processed {$count} documents for locale '{$l}'.");

            if ($enrich) {
                $this->info("Enriching documents with AI keywords for locale '{$l}'...");
                $docs = \App\Models\AiDocument::where('locale', $l)->whereNull('keywords')->get();
                $total = $docs->count();
                $current = 0;

                foreach ($docs as $doc) {
                    $current++;
                    $this->line("[$current/$total] Enriching: {$doc->title}");
                    $index->enrichWithAi($doc);
                }
                $this->info("Enrichment for '{$l}' completed.");
            }
        }

        $this->info('Done.');

        return self::SUCCESS;
    }
}
