<?php

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\Register as BaseRegister;

class Register extends BaseRegister
{
    protected string $view = 'filament.admin.auth.register';

    public function getHeading(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return '';
    }

    public function getSubheading(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return '';
    }
}
