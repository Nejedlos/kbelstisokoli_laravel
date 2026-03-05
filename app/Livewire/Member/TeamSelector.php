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

        $this->redirect(request()->header('Referer') ?: route('member.dashboard'));
    }

    public function render()
    {
        $context = app(MemberContext::class);
        $teams = $context->getAvailableTeams();
        $activeTeam = $this->activeTeamId ? $teams->firstWhere('id', $this->activeTeamId) : null;

        return view('livewire.member.team-selector', [
            'teams' => $teams,
            'activeTeam' => $activeTeam,
        ]);
    }
}
