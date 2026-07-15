<?php

namespace App\Livewire\Public;

use App\Support\HeroEventsHelper;
use Livewire\Component;

class HeroEvents extends Component
{
    public string $alignment = 'left';

    public function placeholder()
    {
        return <<<'HTML'
        <div class="mb-10 opacity-50 animate-pulse">
            <div class="h-4 w-32 bg-white/10 rounded mb-4"></div>
            <div class="space-y-4">
                <div class="h-24 bg-white/5 rounded-3xl"></div>
                <div class="h-24 bg-white/5 rounded-3xl"></div>
            </div>
        </div>
        HTML;
    }

    public function render()
    {
        $upcomingEvents = HeroEventsHelper::getUpcomingEvents();

        return view('livewire.public.hero-events', [
            'upcomingEvents' => $upcomingEvents,
        ]);
    }
}
