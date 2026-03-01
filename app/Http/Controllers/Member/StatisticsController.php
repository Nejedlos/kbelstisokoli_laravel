<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Services\Member\MemberContext;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StatisticsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $activeTeamId = app(MemberContext::class)->getActiveTeamId();

        return view('member.statistics.index', [
            'title' => __('nav.statistics'),
            'activeTeamId' => $activeTeamId,
        ]);
    }

    /**
     * Display my statistics.
     */
    public function me(Request $request): View
    {
        $activeTeamId = app(MemberContext::class)->getActiveTeamId();

        return view('member.statistics.me', [
            'title' => __('nav.my_statistics'),
            'activeTeamId' => $activeTeamId,
        ]);
    }

    /**
     * Display players statistics.
     */
    public function players(Request $request): View
    {
        $activeTeamId = app(MemberContext::class)->getActiveTeamId();

        return view('member.statistics.players', [
            'title' => __('nav.players_statistics'),
            'activeTeamId' => $activeTeamId,
        ]);
    }

    /**
     * Display matches statistics.
     */
    public function matches(Request $request): View
    {
        $activeTeamId = app(MemberContext::class)->getActiveTeamId();

        return view('member.statistics.matches', [
            'title' => __('nav.matches_statistics'),
            'activeTeamId' => $activeTeamId,
        ]);
    }
}
