@component('filament.admin.auth.partials.shell', [
    'title' => 'Nová registrace',
    'subtitle' => 'Přidejte se do našeho týmu',
    'icon' => 'fa-user-plus',
    'showBack' => true,
    'backLabel' => 'Už máte účet? Přihlaste se',
    'backUrl' => filament()->getLoginUrl()
])
    {{ $this->content }}
@endcomponent
