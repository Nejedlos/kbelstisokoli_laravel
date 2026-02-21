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
                <div class="mb-12 flex items-center justify-center w-20 h-20 rounded-full bg-slate-50 shadow-xl relative group ring-4 ring-slate-100/50">
                    <i class="fa-solid fa-basketball-hoop text-primary text-4xl fa-glow icon-bounce-slow relative z-10"></i>
                    <div class="absolute inset-0 rounded-full bg-primary/5 blur-xl opacity-50 group-hover:opacity-100 transition-opacity"></div>
                </div>
                <p class="text-primary font-black uppercase tracking-[0.4em] text-[9px] mb-3 leading-none">' . e(brand_text('Vstup do systému')) . '</p>
                <h2 class="text-slate-900 font-black uppercase tracking-tight text-xl leading-none m-0 p-0">' . e($clubName) . '</h2>
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
