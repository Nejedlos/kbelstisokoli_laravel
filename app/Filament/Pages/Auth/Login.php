<?php

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\Login as BaseLogin;
use Illuminate\Contracts\Support\Htmlable;

use Illuminate\Support\HtmlString;

class Login extends BaseLogin
{
    /**
     * @return string|Htmlable
     */
    public function getHeading(): string|Htmlable
    {
        return new HtmlString('
            <div class="flex flex-col items-center">
                <i class="fa-solid fa-basketball-hoop text-primary text-4xl mb-4 fa-glow icon-bounce-slow"></i>
                <span>' . brand_text('Vítejte zpět na palubovce') . '</span>
            </div>
        ');
    }

    /**
     * @return string|Htmlable
     */
    public function getSubheading(): string|Htmlable
    {
        return brand_text('Přihlášení do administrace ###TEAM_NAME###');
    }
}
