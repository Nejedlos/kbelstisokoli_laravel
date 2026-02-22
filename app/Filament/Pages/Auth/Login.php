<?php

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;

use Illuminate\Support\HtmlString;

class Login extends BaseLogin
{
    // Use a fully custom layout to replace Filament's default simple layout
    protected static string $layout = 'filament.admin.layouts.auth';

    // IMPORTANT: Filament expects a static $view property
    protected static string $view = 'filament.admin.auth.login';

    /**
     * @return string|Htmlable
     */
    public function getHeading(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return '';
    }

    /**
     * @return string|Htmlable
     */
    public function getSubheading(): string|Htmlable
    {
        return new HtmlString('&nbsp;');
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
