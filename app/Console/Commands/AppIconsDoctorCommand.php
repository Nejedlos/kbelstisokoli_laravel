<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Support\IconHelper;
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

        // 2. Kontrola existence ikon pro všechny klíče ve IconHelper
        $this->info('Prověřuji existenci ikon pro definované klíče...');

        $reflection = new \ReflectionClass(IconHelper::class);
        $constants = $reflection->getConstants();
        $factory = app(BladeIconsFactory::class);

        $missingSvgCount = 0;
        foreach ($constants as $key => $iconValue) {
            // Kontrola pro Solid (fas) - musí existovat v SVG sadě pro fallbacky
            $solidIconName = IconHelper::get($iconValue, 'fas');
            try {
                $factory->svg($solidIconName);
            } catch (\Exception $e) {
                $this->error("✗ {$key}: {$solidIconName} [NENALEZENO v Solid SVG sadě]");
                $missingSvgCount++;
            }

            // Kontrola pro Light (fal) - ověřujeme, zda vrací správný název aliasu
            $lightIconAlias = IconHelper::get($iconValue, 'fal');
            if (!str_starts_with($lightIconAlias, 'app::fal-')) {
                $this->error("✗ {$key}: [Chybný formát Light ikony - musí začínat app::fal-]");
            }

            // Ověření, že alias existuje v registru Filamentu
            $resolved = \Filament\Support\Facades\FilamentIcon::resolve($lightIconAlias);
            if (!($resolved instanceof \Illuminate\Support\HtmlString)) {
                $this->error("✗ {$key}: [Alias {$lightIconAlias} není zaregistrován jako HtmlString ve Filamentu]");
            }
        }

        if ($missingSvgCount > 0) {
            $this->error("Diagnostika dokončena s chybami ({$missingSvgCount} chybějících SVG ikon).");
            $this->warn("DŮVOD: Tyto ikony pravděpodobně nejsou v bezplatné (Solid) sadě Font Awesome.");
            $this->warn("ŘEŠENÍ: Změňte název ikony v App\Support\FilamentIcon.php na dostupný ekvivalent.");
            return 1;
        }

        $this->info('Všechny SVG ikony jsou v pořádku.');
        return 0;
    }
}
