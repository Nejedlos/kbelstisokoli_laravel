<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Services\AiSearchService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AiController extends Controller
{
    public function __construct(
        protected AiSearchService $ai
    ) {}

    public function __invoke(Request $request): View
    {
        $query = (string) $request->input('q', '');
        $locale = (string) config('app.locale', 'cs');

        $answer = '';
        $sources = collect();

        if (mb_strlen($query) >= 3) {
            $result = $this->ai->ask($query, $locale, 'member');
            $answer = $result['answer'] ?? '';
            $sources = $result['sources'] ?? collect();
        }

        return view('member.search.ai', [
            'query' => $query,
            'answer' => $answer,
            'sources' => $sources,
        ]);
    }
}
