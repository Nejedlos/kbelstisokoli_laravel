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
        $branding = app(\App\Services\BrandingService::class)->getSettings();
        $clubName = $branding['club_name'] ?? 'Kbelští sokoli';

        return new HtmlString('
            <div class="flex flex-col items-center">
                <div class="mb-16 flex items-center justify-center w-24 h-24 rounded-full bg-slate-50 shadow-xl relative group ring-8 ring-slate-100">
                    <i class="fa-solid fa-basketball-hoop text-primary text-5xl fa-glow icon-bounce-slow relative z-10"></i>
                    <div class="absolute inset-0 rounded-full bg-primary/10 blur-xl opacity-50 group-hover:opacity-100 transition-opacity"></div>
                </div>
                <h2 class="text-slate-900 font-black uppercase tracking-widest leading-none text-base m-0 p-0">' . e($clubName) . '</h2>
                <p class="text-slate-500 font-bold uppercase tracking-[0.3em] text-[9px] mt-4 m-0 p-0">Vstup do administrace</p>
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
}
