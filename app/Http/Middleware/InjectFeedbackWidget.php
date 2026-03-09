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

        if (!in_array(app()->environment(), config('feedback.environments', ['production', 'local']))) {
            return false;
        }

        if ($request->isXmlHttpRequest() || $request->expectsJson() || $request->routeIs('feedback.widget')) {
            return false;
        }

        if (!Auth::check() && app()->environment() !== 'local') {
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

        try {
            $widgetUrl = route('feedback.widget');
            $manifestPath = public_path('build/manifest.json');
            $jsUrl = '';

            if (file_exists($manifestPath)) {
                $manifest = json_decode(file_get_contents($manifestPath), true);
                if (isset($manifest['resources/js/feedback-widget.js']['file'])) {
                    $jsUrl = asset('build/' . $manifest['resources/js/feedback-widget.js']['file']);
                }
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Feedback widget route or asset not found, skipping injection.');
            return;
        }

        if (empty($jsUrl)) {
            \Illuminate\Support\Facades\Log::warning('Feedback widget JS not found in manifest, skipping injection.');
            return;
        }

        $loader = <<<HTML
<script id="ks-fb-loader" data-navigate-once>
    (function() {
        let isFeedbackLoading = false;

        function loadFeedback() {
            if (isFeedbackLoading || document.getElementById('ks-fb-root')) {
                return;
            }
            isFeedbackLoading = true;

            // 1. Load the script first (if not already loaded)
            if (!window.registerKsFeedbackWidget && !document.querySelector('script[src*="feedback-widget"]')) {
                const script = document.createElement('script');
                script.src = '{$jsUrl}';
                script.async = true;
                document.head.appendChild(script);
            }

            // 2. Fetch the widget HTML
            fetch('{$widgetUrl}')
                .then(response => response.text())
                .then(html => {
                    if (!html || document.getElementById('ks-fb-root')) return;
                    const temp = document.createElement('div');
                    temp.innerHTML = html.trim();

                    // Wait for Alpine to be ready and component to be registered
                    const inject = () => {
                        if (window.Alpine && window.Alpine.data('ksFeedbackWidget')) {
                             while (temp.firstChild) {
                                document.body.appendChild(temp.firstChild);
                            }
                        } else {
                            setTimeout(inject, 50);
                        }
                    };
                    inject();
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
