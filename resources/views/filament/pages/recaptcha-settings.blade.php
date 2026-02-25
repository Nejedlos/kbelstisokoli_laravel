<x-filament-panels::page>
    <form wire:submit="save">
        {{ $this->form }}

        <div class="mt-6">
            <x-filament::button type="submit" size="lg"
                x-data="{ success: false }"
                x-on:recaptcha-saved.window="success = true; setTimeout(() => success = false, 2500)"
            >
                <span x-show="!success"><i class="fa-light fa-floppy-disk mr-1.5"></i> {{ __('admin/recaptcha-settings.actions.save') }}</span>
                <span x-show="success" x-cloak class="text-white"><i class="fa-solid fa-circle-check mr-1.5 animate-bounce"></i> Uloženo!</span>
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
