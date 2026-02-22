@component('filament.admin.auth.partials.shell', [
    'title' => 'Zpátky do hry',
    'subtitle' => 'Přihlaste se a aréna je vaše.',
    'icon' => 'fa-basketball-hoop',
    'showBack' => true,
    'backUrl' => route('public.home'),
])
    {{ $this->content }}
@endcomponent
