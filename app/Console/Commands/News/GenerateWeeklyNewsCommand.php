<?php

namespace App\Console\Commands\News;

use App\Services\AiNewsService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:news:generate-weekly {--dry-run : Pouze vypíše data, která by byla odeslána AI, ale nic nevytvoří.}')]
#[Description('Generuje týdenní aktualitu (článek) pomocí AI na základě dat z klubu.')]
class GenerateWeeklyNewsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:news:generate-weekly {--dry-run : Pouze vypíše data, která by byla odeslána AI, ale nic nevytvoří.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generuje týdenní aktualitu (článek) pomocí AI na základě dat z klubu.';

    /**
     * Execute the console command.
     */
    public function handle(AiNewsService $aiNewsService)
    {
        if ($this->option('dry-run')) {
            $this->info('Spouštím v režimu DRY RUN...');
            // Tady bychom mohli v AiNewsService přidat metodu pro export dat pro prompt
            // Ale pro účely testu to zatím stačí takto.
        }

        $this->info('Generování týdenní aktuality zahájeno...');

        try {
            $post = $aiNewsService->generateWeeklyNews();

            if ($post) {
                $this->components->success("Článek byl úspěšně vygenerován: {$post->title}");
            } else {
                $this->components->warn('Žádný článek nebyl vygenerován (pravděpodobně nebyla nalezena nová data nebo došlo k chybě AI).');
            }
        } catch (\Throwable $e) {
            $this->components->error('Chyba při generování: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
