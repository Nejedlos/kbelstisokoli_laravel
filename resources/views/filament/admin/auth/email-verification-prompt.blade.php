<x-filament-panels::layout.base :livewire="$this">
    @include('filament.admin.auth.partials.shell', [
        'title' => 'Ověření e-mailu',
        'subtitle' => 'Prosím potvrďte svou e-mailovou adresu',
        'icon' => 'fa-envelope-dot',
        'showBack' => true,
        'backLabel' => 'Odhlásit se',
        'backUrl' => filament()->getLogoutUrl()
    ])
        {{ $this->content }}
    @endinclude
</x-filament-panels::layout.base>
