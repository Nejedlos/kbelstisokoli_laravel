<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EconomyController extends Controller
{
    /**
     * Zobrazí přehled plateb a příspěvků člena.
     */
    public function index(Request $request): View
    {
        // Zatím bez reálných dat, vracíme shell
        return view('member.economy.index', [
            'summary' => [
                'total_to_pay' => 0,
                'overdue' => 0,
                'paid' => 0,
            ],
            'payments' => collect(), // Placeholder pro kolekci plateb
        ]);
    }
}
