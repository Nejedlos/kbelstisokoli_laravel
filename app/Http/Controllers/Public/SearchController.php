<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\SearchService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function __construct(
        protected SearchService $searchService
    ) {}

    public function index(Request $request): View
    {
        $query = $request->input('q', '');
        $results = collect();

        if (strlen($query) >= 3) {
            $results = $this->searchService->search($query);
        }

        return view('public.search.results', [
            'query' => $query,
            'results' => $results,
        ]);
    }
}
