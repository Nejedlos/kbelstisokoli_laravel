<?php

namespace App\Http\Middleware;

use App\Services\InternalAnalytics\InternalAnalyticsService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InternalAnalyticsMiddleware
{
    public function __construct(
        protected InternalAnalyticsService $analyticsService
    ) {}

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }

    /**
     * Handle tasks after the response has been sent to the browser.
     */
    public function terminate(Request $request, Response $response): void
    {
        $this->analyticsService->trackRequest($request, $response);
    }
}
