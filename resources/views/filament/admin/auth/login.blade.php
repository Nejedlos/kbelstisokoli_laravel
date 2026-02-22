@component('filament.admin.auth.partials.shell', [
    'title' => 'Vítejte zpět',
    'subtitle' => 'Přihlaste se a pojďme na rozcvičku – zápas právě začíná.',
    'icon' => 'fa-basketball',
    'showBack' => false
])
    {{ $this->content }}
@endcomponent
