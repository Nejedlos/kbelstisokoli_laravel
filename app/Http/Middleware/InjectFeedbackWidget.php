<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class InjectFeedbackWidget
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (!$this->shouldInject($request, $response)) {
            return $response;
        }

        $this->injectWidget($response);

        return $response;
    }

    protected function shouldInject(Request $request, Response $response): bool
    {
        if (!config('feedback.enabled', true)) {
            return false;
        }

        if (!in_array(app()->environment(), config('feedback.environments', ['production']))) {
            return false;
        }

        if ($request->isXmlHttpRequest() || $request->expectsJson()) {
            return false;
        }

        if (!Auth::check()) {
            return false;
        }

        // Only inject in HTML responses
        $contentType = $response->headers->get('Content-Type');
        if (!str_contains((string)$contentType, 'text/html')) {
            return false;
        }

        return true;
    }

    protected function injectWidget(Response $response): void
    {
        $content = $response->getContent();

        if (str_contains($content, 'feedback-fab')) {
            return; // Already injected
        }

        $widget = view('partials.feedback-widget')->render();

        $pos = strripos($content, '</body>');

        if (false !== $pos) {
            $content = substr($content, 0, $pos) . $widget . substr($content, $pos);
        } else {
            // Fallback: append to the end
            $content .= $widget;
        }

        $response->setContent($content);
    }
}
