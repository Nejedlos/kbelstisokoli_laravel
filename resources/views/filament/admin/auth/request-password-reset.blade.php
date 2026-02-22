<x-filament-panels::layout.base :livewire="$this">
    @include('filament.admin.auth.partials.shell', [
        'title' => 'Zapomenuté heslo',
        'subtitle' => 'Zašleme vám odkaz pro obnovu přístupu',
        'icon' => 'fa-key-skeleton',
        'showBack' => true,
        'backLabel' => 'Zpět na přihlášení',
        'backUrl' => filament()->getLoginUrl()
    ])
        {{ $this->content }}
    @endinclude
</x-filament-panels::layout.base>
