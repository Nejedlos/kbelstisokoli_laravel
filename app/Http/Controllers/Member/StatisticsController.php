<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StatisticsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        return view('member.statistics.index', [
            'title' => __('nav.statistics'),
        ]);
    }

    /**
     * Display my statistics.
     */
    public function me(Request $request): View
    {
        return view('member.statistics.me', [
            'title' => __('nav.my_statistics'),
        ]);
    }

    /**
     * Display players statistics.
     */
    public function players(Request $request): View
    {
        return view('member.statistics.players', [
            'title' => __('nav.players_statistics'),
        ]);
    }

    /**
     * Display matches statistics.
     */
    public function matches(Request $request): View
    {
        return view('member.statistics.matches', [
            'title' => __('nav.matches_statistics'),
        ]);
    }
}
