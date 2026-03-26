<?php

namespace App\Console\Commands;

use App\Services\AiIndexService;
use App\Models\AiDocument;
use Illuminate\Console\Command;

class AiIndexCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ai:index
                            {--fresh : Smazat stávající index před začátkem}
                            {--enrich : Obohatit výsledky pomocí AI (pomalé)}
                            {--no-ai : Přeskočit AI obohacení (výchozí)}
                            {--locale=all : Jazyk (all, cs, en)}
                            {--section=all : Sekce (all, frontend, member, admin, documentation, help)}
                            {--force : Vynutit reindexaci i nezměněných dokumentů}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Kompletní sestavení vyhledávacího indexu z obsahu webu a administrace.';

    /**
     * Execute the console command.
     */
    public function handle(AiIndexService $aiIndexService): int
    {
        $fresh = $this->option('fresh');
        $enrich = $this->option('enrich');
        $force = $this->option('force');
        $localeOption = $this->option('locale');
        $sectionOption = $this->option('section');

        $locales = $localeOption === 'all' ? ['cs', 'en'] : [$localeOption];

        // Mapování sekcí
        $availableSections = ['frontend', 'member', 'admin', 'documentation', 'help'];
        if ($sectionOption === 'all') {
            $sections = [null]; // null v reindex() znamená všechny sekce
        } else {
            $sections = [$sectionOption];
        }

        $this->info("=== AI Search Indexer ===");
        if ($fresh) $this->warn("! Režim FRESH: Stávající index pro vybrané parametry bude smazán.");
        if ($enrich) $this->warn("! Režim ENRICH: Bude provedeno AI obohacení (OpenAI API).");
        if ($force) $this->warn("! Režim FORCE: Všechny dokumenty budou přepsány bez ohledu na změny.");

        foreach ($locales as $locale) {
            foreach ($sections as $section) {
                $sectionName = $section ?: 'VŠECHNY SEKCE';
                $this->newLine();
                $this->comment(">>> Indexuji [{$locale}] / [{$sectionName}]");

                $count = $aiIndexService->reindex(
                    locale: $locale,
                    fresh: $fresh,
                    section: $section,
                    onProgress: function ($message) {
                        $this->line("  - " . $message);
                    },
                    force: $force
                );

                $this->info("✓ Dokončeno: {$count} dokumentů zaindexováno.");

                if ($enrich) {
                    $this->comment("--- Provádím AI obohacení pro [{$locale}] / [{$sectionName}] ---");

                    $query = AiDocument::where('locale', $locale)
                        ->where('is_active', true);

                    if ($section) {
                        $query->where('section', $section);
                    }

                    $docsToEnrich = $query->get();

                    if ($docsToEnrich->isEmpty()) {
                        $this->line("Žádné dokumenty k obohacení.");
                        continue;
                    }

                    $bar = $this->output->createProgressBar($docsToEnrich->count());
                    $bar->start();

                    $enrichedCount = 0;
                    foreach ($docsToEnrich as $doc) {
                        if ($aiIndexService->enrichWithAi($doc)) {
                            $enrichedCount++;
                        }
                        $bar->advance();
                    }
                    $bar->finish();
                    $this->newLine();
                    $this->info("✓ AI Obohacení dokončeno: {$enrichedCount} / " . $docsToEnrich->count() . " úspěšně.");
                }
            }
        }

        $this->newLine();
        $this->info("=== Indexace kompletně dokončena ===");

        return self::SUCCESS;
    }
}
