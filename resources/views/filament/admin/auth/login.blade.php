@component('filament.admin.auth.partials.shell', [
    'title' => 'Vítejte zpět',
    'subtitle' => 'Vstupte na palubovku vaší arény',
    'icon' => 'fa-basketball-hoop',
    'showBack' => false
])
    {{ $this->content }}

    @if (filament()->hasRegistration())
        <div class="mt-6 text-center text-sm text-slate-500">
            {{ __('filament-panels::auth/pages/login.actions.register.before') }}
            {{ $this->registerAction }}
        </div>
    @endif
@endcomponent
