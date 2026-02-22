<?php

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\PasswordReset\RequestPasswordReset as BaseRequestPasswordReset;

class RequestPasswordReset extends BaseRequestPasswordReset
{
    // Override layout to use our custom full auth layout
    protected static string $layout = 'filament.admin.layouts.auth';

    protected string $view = 'filament.admin.auth.request-password-reset';

    public function getHeading(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return __('Zapomenuté heslo');
    }

    public function getSubheading(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return __('Stává se i nejlepším střelcům. Pošleme přihrávku na nový start.');
    }

    public function getIcon(): string
    {
        return 'fa-key';
    }
}
