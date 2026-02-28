<?php

namespace App\Console\Commands;

use App\Services\AiIndexService;
use Illuminate\Console\Command;

class AiReindexCommand extends Command
{
    protected $signature = 'ai:index
                            {--locale= : Jazyk indexu (cs/en/all)}
                            {--section= : Sekce k indexaci (frontend/member/admin)}
                            {--fresh : Smazat existující index před reindexací}
                            {--enrich : Obohatit dokumenty o AI shrnutí a klíčová slova (pomalé)}
                            {--no-ai : Přeskočit generování chunků pro AI hledání}';

    protected $description = 'Sestaví nebo aktualizuje AI vyhledávací index z obsahu sekcí';

    public function handle(AiIndexService $index): int
    {
        $locale = (string) $this->option('locale') ?: 'cs';
        $section = $this->option('section');
        $fresh = (bool) $this->option('fresh');
        $enrich = (bool) $this->option('enrich');
        $noAi = (bool) $this->option('no-ai');

        if ($noAi) {
            config(['ai.indexing.skip_chunks' => true]);
        }

        $locales = $locale === 'all' ? ['cs', 'en'] : [$locale];

        foreach ($locales as $l) {
            // Nastavíme globální locale pro aktuální iteraci
            \Illuminate\Support\Facades\App::setLocale($l);

            $this->info("Indexing documents for locale '{$l}'" . ($section ? " section '{$section}'" : "") . ($fresh ? " (fresh)" : "") . "...");

            // Pokud není fresh, aspoň promažeme typy, které už nechceme indexovat (staré dokumenty z docs)
            if (!$fresh) {
                $query = \App\Models\AiDocument::where('locale', $l)
                    ->where('section', 'documentation'); // Vyčistíme starou dokumentaci, pokud tam zbyla

                if ($section) {
                    $query->where('section', $section);
                }

                $deletedCount = $query->delete();
                if ($deletedCount > 0) {
                    $this->line("  - Cleaned up $deletedCount old documentation entries.");
                }
            }

            $count = $index->reindex($l, $fresh, $section, function($message) {
                $this->line("  - $message");
            });

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
