<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Support\FilamentIcon;
use BladeUI\Icons\Factory as BladeIconsFactory;

class AppIconsDoctorCommand extends Command
{
    protected $signature = 'app:icons:doctor';
    protected $description = 'Prověří integritu ikon v projektu (existence SVG, balíčky, syntaxe).';

    public function handle()
    {
        $this->info('--- Diagnostika ikon v administraci ---');

        // 1. Kontrola balíčků
        $composer = json_decode(file_get_contents(base_path('composer.json')), true);
        $packages = array_merge($composer['require'] ?? [], $composer['require-dev'] ?? []);

        if (isset($packages['owenvoke/blade-fontawesome'])) {
            $this->info('✓ owenvoke/blade-fontawesome je nainstalován.');
        } else {
            $this->error('✗ owenvoke/blade-fontawesome chybí v composer.json!');
        }

        // 2. Kontrola existence SVG ikon pro všechny klíče ve FilamentIcon
        $this->info('Prověřuji existenci SVG ikon pro definované klíče...');

        $reflection = new \ReflectionClass(FilamentIcon::class);
        $constants = $reflection->getConstants();
        $factory = app(BladeIconsFactory::class);

        $missingCount = 0;
        foreach ($constants as $key => $iconValue) {
            // Získáme název, jak ho vrací FilamentIcon::get()
            $bladeIconName = FilamentIcon::get($iconValue);

            try {
                // Pokusíme se načíst SVG
                $factory->svg($bladeIconName);
                // $this->line("✓ {$key}: {$bladeIconName}"); // Omezíme výstup jen na chyby
            } catch (\Exception $e) {
                $this->error("✗ {$key}: {$bladeIconName} [NENALEZENO v SVG sadě]");
                $missingCount++;
            }
        }

        if ($missingCount > 0) {
            $this->error("Diagnostika dokončena s chybami ({$missingCount} chybějících ikon).");
            $this->warn("DŮVOD: Tyto ikony pravděpodobně nejsou v bezplatné (Solid) sadě Font Awesome.");
            $this->warn("ŘEŠENÍ: Změňte název ikony v App\Support\FilamentIcon.php na dostupný ekvivalent.");
            return 1;
        }

        $this->info('Všechny SVG ikony jsou v pořádku.');
        return 0;
    }
}
