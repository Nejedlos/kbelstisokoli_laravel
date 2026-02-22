<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Services\BackendSearchService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function __construct(
        protected BackendSearchService $backendSearchService
    ) {}

    public function __invoke(Request $request): View
    {
        $query = $request->input('q', '');
        $results = collect();

        if (strlen($query) >= 3) {
            $results = $this->backendSearchService->search($query, 'member');
        }

        return view('member.search.results', [
            'query' => $query,
            'results' => $results,
        ]);
    }
}
