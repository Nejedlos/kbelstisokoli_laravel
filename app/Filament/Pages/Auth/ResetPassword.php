<?php

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\PasswordReset\ResetPassword as BaseResetPassword;

class ResetPassword extends BaseResetPassword
{
    // Use our custom auth layout for full control over the page shell
    protected static string $layout = 'filament.admin.layouts.auth';

    protected string $view = 'filament.admin.auth.reset-password';

    public function getHeading(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return '';
    }

    public function getSubheading(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return '';
    }
}
