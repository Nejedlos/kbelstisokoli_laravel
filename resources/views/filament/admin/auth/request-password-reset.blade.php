@component('filament.admin.auth.partials.shell', [
    'title' => 'Zapomenuté heslo',
    'subtitle' => 'Stává se i nejlepším střelcům. Pošleme přihrávku na nový start.',
    'icon' => 'fa-key-skeleton',
    'showBack' => true,
    'backLabel' => 'Zpět na přihlášení',
    'backUrl' => filament()->getLoginUrl()
])
    {{ $this->content }}
@endcomponent
