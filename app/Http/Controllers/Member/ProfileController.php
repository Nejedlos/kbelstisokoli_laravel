<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(): View
    {
        return view('member.profile.edit');
    }

    public function update(Request $request): RedirectResponse
    {
        // Placeholder pro aktualizaci profilu
        return back()->with('status', 'Profil byl aktualizován');
    }
}
