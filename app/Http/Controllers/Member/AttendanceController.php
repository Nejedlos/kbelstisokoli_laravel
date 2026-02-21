<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function index(): View
    {
        return view('member.attendance.index');
    }

    public function confirm(string $id): RedirectResponse
    {
        // Placeholder pro potvrzení docházky
        return back()->with('status', 'Docházka potvrzena (ID: ' . $id . ')');
    }

    public function decline(string $id): RedirectResponse
    {
        // Placeholder pro odmítnutí docházky
        return back()->with('status', 'Docházka odmítnuta (ID: ' . $id . ')');
    }
}
