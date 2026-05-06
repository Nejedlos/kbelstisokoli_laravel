<x-filament-panels::page>
    <div class="space-y-6">
        <p class="text-sm text-gray-500 dark:text-gray-400">
            {{ __('internal-analytics.description') }}
        </p>

        {{-- Widgety se statistikami --}}
        <div class="grid grid-cols-1 gap-6">
            @foreach ($this->getHeaderWidgets() as $widget)
                @livewire($widget, ['filters' => $tableFilters], key($widget))
            @endforeach
        </div>

        {{-- Grafy a tabulky --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            @foreach ($this->getFooterWidgets() as $widget)
                <div class="{{ $loop->last && $loop->count % 2 !== 0 ? 'lg:col-span-2' : '' }}">
                    @livewire($widget, ['filters' => $tableFilters], key($widget))
                </div>
            @endforeach
        </div>
    </div>
</x-filament-panels::page>
