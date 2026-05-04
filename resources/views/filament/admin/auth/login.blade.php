<div class="relative">
    {{-- Alerty pro status a chyby – umístěno uvnitř Livewire komponentu pro interaktivní update --}}

    @if (session('status'))
        <x-auth-alert type="success" :message="session('status')" />
    @endif

    @if ($errors->any())
        <x-auth-alert type="error">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </x-auth-alert>
    @endif

    {{-- Globální basketbalový loader pro okamžitou zpětnou vazbu při přihlašování --}}
    <x-loader-basketball wire:loading.delay.shortest wire:target="authenticate" wire:loading.class.remove="hidden">
        {{ __('Ověřování taktiky...') }}
    </x-loader-basketball>

    {{ $this->content }}
</div>
