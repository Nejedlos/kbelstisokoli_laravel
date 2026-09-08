<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\View\View;

class HistoryController extends Controller
{
    public function index(): View
    {
        $page = Page::where('slug', 'historie')->first();

        return view('public.history.index', compact('page'));
    }
}
