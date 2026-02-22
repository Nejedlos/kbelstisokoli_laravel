<?php

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;

use Illuminate\Support\HtmlString;
use Filament\Schemas\Components\Component;

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
        return __('Vstup do kabiny');
    }

    /**
     * @return string|Htmlable
     */
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
