<?php

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\Register as BaseRegister;

class Register extends BaseRegister
{
    // Use custom full-page auth layout instead of Filament's simple layout
    protected static string $layout = 'filament.admin.layouts.auth';

    protected string $view = 'filament.admin.auth.register';

    public function getHeading(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return __('Registrace nového hráče');
    }

    public function getSubheading(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return __('Staň se součástí týmu a vyběhni na palubovku.');
    }

    public function getIcon(): string
    {
        return 'fa-user-plus';
    }
}
