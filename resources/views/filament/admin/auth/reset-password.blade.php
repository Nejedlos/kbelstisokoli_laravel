<x-filament-panels::layout.base :livewire="$this">
    @include('filament.admin.auth.partials.shell', [
        'title' => 'Nové heslo',
        'subtitle' => 'Nastavte si bezpečné heslo k vašemu účtu',
        'icon' => 'fa-lock-keyhole',
        'showBack' => true,
        'backLabel' => 'Zpět na přihlášení',
        'backUrl' => filament()->getLoginUrl()
    ])
        {{ $this->content }}
    @endinclude
</x-filament-panels::layout.base>
