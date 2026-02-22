<?php

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;

use Illuminate\Support\HtmlString;

class Login extends BaseLogin
{
    // Použij vlastní layout místo výchozího jednoduchého layoutu Filamentu
    protected static string $layout = 'filament.admin.layouts.auth';

    // DŮLEŽITÉ: `$view` musí být NEstatická vlastnost, aby odpovídala `Filament\Pages\SimplePage`
    protected string $view = 'filament.admin.auth.login';

    /**
     * @return string|Htmlable
     */
    public function getHeading(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return 'Zpátky do hry';
    }

    /**
     * @return string|Htmlable
     */
    public function getSubheading(): string|Htmlable
    {
        return 'Přihlaste se a aréna je vaše.';
    }

    public function form(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
    {
        return parent::form($schema)
            ->components([
                $this->getEmailFormComponent()->live()->debounce(500),
                $this->getPasswordFormComponent()->live()->debounce(500),
                $this->getRememberFormComponent(),
            ]);
    }
}
