<?php

namespace App\Filament\Pages\Auth;

use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Component;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Validation\ValidationException;

class Login extends BaseLogin
{
    // Použij vlastní layout místo výchozího jednoduchého layoutu Filamentu
    protected static string $layout = 'filament.admin.layouts.auth';

    // DŮLEŽITÉ: `$view` musí být NEstatická vlastnost, aby odpovídala `Filament\Pages\SimplePage`
    protected string $view = 'filament.admin.auth.login';

    public function getHeading(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return __('Vstup do kabiny');
    }

    public function getSubheading(): string|Htmlable
    {
        return __('Z palubovky rovnou k taktické tabuli.');
    }

    public function getIcon(): string
    {
        return 'fa-basketball-hoop';
    }

    protected function getPasswordFormComponent(): Component
    {
        // Přepisujeme původní metodu, abychom odstranili helper link "Zapomněli jste heslo?",
        // který Filament automaticky přidává, protože ho máme v custom layoutu.
        return parent::getPasswordFormComponent()
            ->helperText(null)
            ->hint(null);
    }

    protected function getRateLimitedNotification(TooManyRequestsException $exception): ?Notification
    {
        return Notification::make()
            ->title(__('Too many login attempts. Please try again in :seconds seconds.', [
                'seconds' => $exception->secondsUntilAvailable,
                'minutes' => $exception->minutesUntilAvailable,
            ]))
            ->danger();
    }

    protected function throwFailureValidationException(): never
    {
        Notification::make()
            ->title(__('These credentials do not match our records.'))
            ->danger()
            ->send();

        throw ValidationException::withMessages([
            'data.email' => ' ',
        ]);
    }

    public function form(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
    {
        return parent::form($schema)
            ->components([
                $this->getEmailFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getRememberFormComponent(),
            ]);
    }
}
