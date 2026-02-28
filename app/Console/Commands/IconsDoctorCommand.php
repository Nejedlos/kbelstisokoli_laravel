<?php

namespace App\Console\Commands;

use App\Support\FilamentIcon;
use App\Support\Icons\AppIcon;
use BladeUI\Icons\Factory as IconFactory;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class IconsDoctorCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:icons:doctor';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Diagnostika a validace ikon v aplikaci';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->header();

        $this->checkEnvironment();
        $this->checkEnumIcons();
        $this->scanForProblematicStrings();

        $this->info("\n✅ Diagnostika dokončena.");
    }

    protected function header(): void
    {
        $this->newLine();
        $this->info('╔══════════════════════════════════════════════════════╗');
        $this->info('║             Filament Icons Doctor 🩺                 ║');
        $this->info('╚══════════════════════════════════════════════════════╝');
    }

    protected function checkEnvironment(): void
    {
        $this->warn("\n[1] Kontrola prostředí:");
        $hasPro = config('app.fontawesome_pro', false);
        $this->line('Font Awesome Pro: '.($hasPro ? '✅ Aktivní' : '❌ Neaktivní (Fallback na Solid)'));

        $iconSets = app(IconFactory::class)->all();
        $this->line('Dostupné sady ikon: '.implode(', ', array_keys($iconSets)));
    }

    protected function checkEnumIcons(): void
    {
        $this->warn("\n[2] Kontrola ikon v AppIcon Enum:");
        $factory = app(IconFactory::class);
        $errors = 0;

        $rows = [];
        foreach (AppIcon::cases() as $case) {
            $iconName = FilamentIcon::get($case);
            $exists = $this->iconExists($factory, $iconName);

            $rows[] = [
                $case->name,
                $iconName,
                $exists ? '✅ OK' : '❌ NENALEZENO',
            ];

            if (! $exists) {
                $errors++;
            }
        }

        $this->table(['Enum Klíč', 'Blade Icon Název', 'Stav'], $rows);

        if ($errors > 0) {
            $this->error("Nalezeno $errors chybějících ikon!");
        }
    }

    protected function scanForProblematicStrings(): void
    {
        $this->warn("\n[3] Vyhledávání problematických řetězců v app/Filament:");
        $problematic = [
            'fal_' => 'Obsahuje podtržítko místo pomlčky',
            'fa_' => 'Starý prefix s podtržítkem',
            'app::fal-' => 'Zastaralý alias',
        ];

        $files = File::allFiles(app_path('Filament'));
        $found = 0;

        foreach ($files as $file) {
            $content = $file->getContents();
            foreach ($problematic as $search => $reason) {
                if (str_contains($content, $search)) {
                    $this->line("⚠️  {$file->getRelativePathname()}: Nalezeno '$search' - $reason");
                    $found++;
                }
            }
        }

        if ($found === 0) {
            $this->line('✅ Žádné zjevné syntaktické chyby nenalezeny.');
        }
    }

    protected function iconExists(IconFactory $factory, string $name): bool
    {
        try {
            return (bool) $factory->svg($name);
        } catch (\Exception $e) {
            return false;
        }
    }
}
