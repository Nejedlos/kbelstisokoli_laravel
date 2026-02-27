<x-filament-panels::page>
    <form wire:submit="create">
        {{ $this->form }}

        <div class="mt-6 flex items-center justify-end gap-3">
            <x-filament::button color="gray" tag="a" :href="Filament\Pages\Dashboard::getUrl()">
                Zrušit
            </x-filament::button>

            <x-filament::button type="submit" size="lg" class="px-8 font-black uppercase tracking-widest bg-primary-600 shadow-lg shadow-primary-500/20">
                Uložit konfigurace a inicializovat sezónu
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
