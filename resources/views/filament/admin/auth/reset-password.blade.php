@component('filament.admin.auth.partials.shell', [
    'title' => 'Nové heslo',
    'subtitle' => 'Zavažte tkaničky – nastavíme nové heslo a jde se zpátky do hry.',
    'icon' => 'fa-lock-keyhole',
    'showBack' => true,
    'backLabel' => 'Zpět na přihlášení',
    'backUrl' => filament()->getLoginUrl()
])
    {{ $this->content }}
@endcomponent
