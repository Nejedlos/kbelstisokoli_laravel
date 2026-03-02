<div class="relative">
    {{-- Alerty pro status a chyby – umístěno uvnitř Livewire komponentu pro interaktivní update --}}

    @if (session('status'))
        <x-auth-alert type="success" :message="session('status')" />
    @endif

    @if ($errors->any())
        @php
            $filteredErrors = collect($errors->all())->filter(fn($error) => trim($error) !== '')->all();
        @endphp
        @if (!empty($filteredErrors))
            <x-auth-alert type="error">
                @foreach ($filteredErrors as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </x-auth-alert>
        @endif
    @endif

    {{-- Globální basketbalový loader pro okamžitou zpětnou vazbu při přihlašování --}}
    <x-loader.basketball wire:loading.delay.shortest wire:target="authenticate" wire:loading.class.remove="hidden">
        {{ __('Ověřování taktiky...') }}
    </x-loader.basketball>

    {{ $this->content }}
</div>
