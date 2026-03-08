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

        if ($request->isXmlHttpRequest() || $request->expectsJson() || $request->routeIs('feedback.widget')) {
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

        if (str_contains($content, 'ks-fb-loader') || str_contains($content, 'ks-fb-root')) {
            return; // Již injektováno nebo přítomno
        }

        $widgetUrl = route('feedback.widget');
        $loader = <<<HTML
<script id="ks-fb-loader" data-navigate-once>
    (function() {
        let isFeedbackLoading = false;

        function loadFeedback() {
            if (isFeedbackLoading || document.getElementById('ks-fb-root')) {
                return;
            }
            isFeedbackLoading = true;

            fetch('{$widgetUrl}')
                .then(response => response.text())
                .then(html => {
                    if (!html || document.getElementById('ks-fb-root')) return;
                    const temp = document.createElement('div');
                    temp.innerHTML = html.trim();
                    const fragment = document.createDocumentFragment();
                    while (temp.firstChild) {
                        fragment.appendChild(temp.firstChild);
                    }
                    document.body.appendChild(fragment);
                })
                .finally(() => {
                    isFeedbackLoading = false;
                });
        }

        if (document.readyState === 'complete') {
            setTimeout(loadFeedback, 500);
        } else {
            window.addEventListener('load', () => setTimeout(loadFeedback, 500));
        }

        document.addEventListener('livewire:navigated', () => setTimeout(loadFeedback, 500));
    })();
</script>
HTML;

        $pos = strripos($content, '</body>');

        if (false !== $pos) {
            $content = substr($content, 0, $pos) . $loader . substr($content, $pos);
        } else {
            // Fallback: append to the end
            $content .= $loader;
        }

        $response->setContent($content);
    }
}
