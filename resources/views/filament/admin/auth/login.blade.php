<div class="relative">
    {{-- Globální basketbalový loader pro okamžitou zpětnou vazbu při přihlašování --}}
    <x-loader.basketball wire:loading.delay.shortest wire:target="authenticate" wire:loading.class.remove="hidden">
        {{ __('Ověřování taktiky...') }}
    </x-loader.basketball>

    {{ $this->content }}
</div>
