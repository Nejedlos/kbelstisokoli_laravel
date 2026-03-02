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

    {{ $this->content }}
</div>
