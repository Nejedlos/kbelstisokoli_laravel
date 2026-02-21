<?php

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;

use Illuminate\Support\HtmlString;

class Login extends BaseLogin
{
    /**
     * @return string|Htmlable
     */
    public function getHeading(): string|Htmlable
    {
        $branding = app(\App\Services\BrandingService::class)->getSettings();
        $clubName = $branding['club_name'] ?? 'Kbelští sokoli';

        if ($branding['logo_path'] ?? null) {
            return new HtmlString('
                <div class="flex flex-col items-center">
                    <div class="mb-10 w-24 h-24 bg-white/5 backdrop-blur-md rounded-3xl flex items-center justify-center mx-auto shadow-2xl border border-white/10 p-4 transition-transform hover:scale-105 duration-500">
                        <img src="' . asset('storage/' . $branding['logo_path']) . '" class="max-w-full max-h-full object-contain filter drop-shadow-lg" alt="' . e($clubName) . '">
                    </div>
                    <h1 class="auth-title">Vítejte zpět</h1>
                    <p class="auth-sub tracking-tight">Vstupte na palubovku ' . e($clubName) . '</p>
                </div>
            ');
        }

        return new HtmlString('
            <div class="flex flex-col items-center">
                <div class="auth-icon-container">
                    <i class="fa-duotone fa-light fa-basketball-hoop text-5xl text-primary icon-bounce icon-glow"></i>
                </div>
                <h1 class="auth-title">Vítejte zpět</h1>
                <p class="auth-sub tracking-tight">Vstupte na palubovku ' . e($clubName) . '</p>
            </div>
        ');
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
