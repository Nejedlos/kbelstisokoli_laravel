@component('filament.admin.auth.partials.shell', [
    'title' => 'Ověření e‑mailu',
    'subtitle' => 'Ještě jeden přesný zásah do sítě – potvrďte svou adresu.',
    'icon' => 'fa-envelope-dot',
    'showBack' => true,
    'backLabel' => 'Odhlásit se',
    'backUrl' => filament()->getLogoutUrl()
])
    {{ $this->content }}
@endcomponent
