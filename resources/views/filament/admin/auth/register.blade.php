<x-filament-panels::layout.base :livewire="$this">
    @include('filament.admin.auth.partials.shell', [
        'title' => 'Nová registrace',
        'subtitle' => 'Přidejte se do našeho týmu',
        'icon' => 'fa-user-plus',
        'showBack' => true,
        'backLabel' => 'Už máte účet? Přihlaste se',
        'backUrl' => filament()->getLoginUrl()
    ])
        {{ $this->getFormContentComponent() }}
    @endinclude
</x-filament-panels::layout.base>
