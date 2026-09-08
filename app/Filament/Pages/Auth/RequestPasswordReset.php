<?php

namespace App\Filament\Pages\Auth;

use App\Services\Auth\PasswordResetService;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Auth\Pages\PasswordReset\RequestPasswordReset as BaseRequestPasswordReset;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class RequestPasswordReset extends BaseRequestPasswordReset
{
    public function request(): void
    {
        $data = $this->form->getState();

        try {
            app(PasswordResetService::class)->sendResetLink(
                $data['email'],
                Filament::getCurrentPanel()?->getId()
            );
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (\Exception $e) {
            Log::error('Filament password reset request failed', [
                'email' => $data['email'] ?? 'unknown',
                'error' => $e->getMessage(),
            ]);
        }

        // Vždy zobrazíme úspěch, abychom neprozradili existenci emailu (Anti-enumeration)
        Notification::make()
            ->title(__('passwords.sent'))
            ->success()
            ->send();

        session()->flash('status', __('passwords.sent'));

        $this->form->fill();
    }

    protected function getRateLimitedNotification(TooManyRequestsException $exception): ?Notification
    {
        return null;
    }

    // Override layout to use our custom full auth layout
    protected static string $layout = 'filament.admin.layouts.auth';

    protected string $view = 'filament.admin.auth.request-password-reset';

    public function getHeading(): string|Htmlable
    {
        return __('Zapomenuté heslo');
    }

    public function getSubheading(): string|Htmlable
    {
        return __('Stává se i nejlepším střelcům. Pošleme přihrávku na nový start.');
    }

    public function getIcon(): string
    {
        return 'fa-key';
    }
}
