<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function index(): View
    {
        $page = \App\Models\Page::where('slug', 'kontakt')->first();

        return view('public.contact.index', compact('page'));
    }
}
