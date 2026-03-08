<?php

namespace App\Livewire\Member;

use App\Services\Member\MemberContext;
use Livewire\Component;

class TeamSelector extends Component
{
    public $activeTeamId = null;

    public function mount(MemberContext $context): void
    {
        $this->activeTeamId = $context->getActiveTeamId();
    }

    public function updatedActiveTeamId($value): void
    {
        $id = ($value === '' || $value === null) ? null : (int) $value;

        app(MemberContext::class)->setActiveTeamId($id);

        $this->redirect(
            request()->header('Referer') ?: route('member.dashboard'),
            navigate: (bool) config('performance.features.livewire_navigate', false)
        );
    }

    public function render()
    {
        $context = app(MemberContext::class);
        $this->activeTeamId = $context->getActiveTeamId();
        $teams = $context->getAvailableTeams();
        $activeTeam = $this->activeTeamId ? $teams->firstWhere('id', $this->activeTeamId) : null;

        return view('livewire.member.team-selector', [
            'teams' => $teams,
            'activeTeam' => $activeTeam,
        ]);
    }
}
