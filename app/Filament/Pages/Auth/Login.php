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

        return new HtmlString('
            <div class="flex flex-col items-center">
                <div class="mb-14 flex items-center justify-center w-24 h-24 rounded-full bg-slate-50 shadow-2xl relative group ring-8 ring-slate-100/50">
                    <i class="fa-light fa-basketball-hoop text-primary text-5xl fa-glow icon-bounce-slow relative z-10"></i>
                    <div class="absolute inset-0 rounded-full bg-primary/10 blur-2xl opacity-50 group-hover:opacity-100 transition-opacity"></div>
                </div>
                <p class="text-primary font-black uppercase tracking-[0.5em] text-[10px] mb-4 leading-none">' . e(brand_text('Vstup do hry')) . '</p>
                <h2 class="text-slate-900 font-black uppercase tracking-tight text-2xl leading-none m-0 p-0">' . e($clubName) . '</h2>
                <p class="text-slate-400 font-medium italic text-[11px] mt-4 opacity-80">Strategická porada a příprava na vítězství</p>
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
