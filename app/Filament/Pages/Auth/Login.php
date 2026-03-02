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
        return null; // Don't send notification, handle via exception
    }

    protected function getRateLimitedException(TooManyRequestsException $exception): never
    {
        throw \Illuminate\Validation\ValidationException::withMessages([
            'data.email' => __('auth.throttle', [
                'seconds' => $exception->secondsUntilAvailable,
                'minutes' => ceil($exception->secondsUntilAvailable / 60),
            ]),
        ]);
    }

    protected function throwFailureValidationException(): never
    {
        throw \Illuminate\Validation\ValidationException::withMessages([
            'data.email' => __('auth.failed'),
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
