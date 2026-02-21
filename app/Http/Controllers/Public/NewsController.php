<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class NewsController extends Controller
{
    public function index(): View
    {
        return view('public.news.index');
    }

    public function show(string $slug): View
    {
        return view('public.news.show', compact('slug'));
    }
}
