<?php

namespace App\Console\Commands;

use App\Services\AiIndexService;
use Illuminate\Console\Command;

class AiReindexCommand extends Command
{
    protected $signature = 'ai:index {--locale= : Jazyk indexu (cs/en/all)} {--fresh : Smazat existující index před reindexací} {--enrich : Obohatit dokumenty o AI shrnutí a klíčová slova (pomalé)}';

    protected $description = 'Sestaví nebo aktualizuje AI vyhledávací index z obsahu sekcí';

    public function handle(AiIndexService $index): int
    {
        $locale = (string) $this->option('locale') ?: 'cs';
        $fresh = (bool) $this->option('fresh');
        $enrich = (bool) $this->option('enrich');

        $locales = $locale === 'all' ? ['cs', 'en'] : [$locale];

        foreach ($locales as $l) {
            // Nastavíme globální locale pro aktuální iteraci
            \Illuminate\Support\Facades\App::setLocale($l);

            $this->info("Indexing AI documents for locale '{$l}'" . ($fresh ? " (fresh)" : "") . "...");

            // Pokud není fresh, aspoň promažeme typy, které už nechceme indexovat
            if (!$fresh) {
                \App\Models\AiDocument::where('locale', $l)
                    ->whereIn('type', ['docs', 'admin.page', 'admin.navigation'])
                    ->delete();
            }

            $count = $index->reindex($l, $fresh);
            $this->info("Processed {$count} documents for locale '{$l}'.");

            if ($enrich) {
                $this->info("Enriching documents with AI summary and keywords for locale '{$l}'...");
                $docs = \App\Models\AiDocument::where('locale', $l)
                    ->where(function ($q) {
                        $q->whereNull('keywords')
                          ->orWhereNull('summary');
                    })->get();
                $total = $docs->count();
                $current = 0;

                foreach ($docs as $doc) {
                    $current++;
                    $this->line("[$current/$total] Enriching: {$doc->title} ({$doc->type})");
                    $index->enrichWithAi($doc);
                }
                $this->info("Enrichment for '{$l}' completed.");
            }
        }

        $this->info('Done.');

        return self::SUCCESS;
    }
}
