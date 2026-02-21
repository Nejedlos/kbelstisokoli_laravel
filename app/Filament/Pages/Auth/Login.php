<?php

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\Login as BaseLogin;
use Illuminate\Contracts\Support\Htmlable;

class Login extends BaseLogin
{
    /**
     * @return string|Htmlable
     */
    public function getHeading(): string|Htmlable
    {
        return brand_text('Vítejte zpět na palubovce');
    }

    /**
     * @return string|Htmlable
     */
    public function getSubheading(): string|Htmlable
    {
        return brand_text('Přihlášení do administrace ###TEAM_NAME###');
    }
}
