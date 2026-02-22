@component('filament.admin.auth.partials.shell', [
    'title' => 'Nové heslo',
    'subtitle' => 'Nastavte si bezpečné heslo k vašemu účtu',
    'icon' => 'fa-lock-keyhole',
    'showBack' => true,
    'backLabel' => 'Zpět na přihlášení',
    'backUrl' => filament()->getLoginUrl()
])
    {{ $this->content }}
@endcomponent
