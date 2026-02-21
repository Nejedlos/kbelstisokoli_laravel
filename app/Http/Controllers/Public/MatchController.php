<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class MatchController extends Controller
{
    public function index(): View
    {
        return view('public.matches.index');
    }

    public function show(string $id): View
    {
        return view('public.matches.show', compact('id'));
    }
}
