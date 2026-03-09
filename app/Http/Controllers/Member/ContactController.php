<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    /**
     * Zobrazí formulář pro kontaktování trenéra.
     */
    public function coachForm(Request $request): View
    {
        return view('member.contact.coach');
    }

    /**
     * Zobrazí formulář pro kontaktování administrátora.
     */
    public function adminForm(Request $request): View
    {
        return view('member.contact.admin');
    }
}
