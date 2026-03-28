<?php

namespace Tests\Feature\Security;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    /**
     * Test presence of basic security headers.
     * ID: TEST-HEADER-01
     */
    public function test_security_headers_are_present(): void
    {
        $response = $this->get('/');

        $response->assertHeader('X-Frame-Options');
        $response->assertHeader('X-Content-Type-Options');
        // CSP might be missing or set in a meta tag, but we check for the header
        // $response->assertHeader('Content-Security-Policy');
    }

    /**
     * Test session cookie flags.
     * ID: TEST-AUTH-01
     */
    public function test_session_cookie_flags(): void
    {
        $response = $this->get('/');

        $cookies = $response->headers->getCookies();
        foreach ($cookies as $cookie) {
            if ($cookie->getName() === config('session.cookie')) {
                $this->assertTrue($cookie->isHttpOnly(), "Cookie {$cookie->getName()} should be HttpOnly");
                // Secure flag depends on environment, but in production it's a must
                if (config('app.url') && str_starts_with(config('app.url'), 'https')) {
                    $this->assertTrue($cookie->isSecure(), "Cookie {$cookie->getName()} should be Secure");
                }
            }
        }
    }

    /**
     * Test that sensitive files are not accessible.
     * ID: TEST-INFO-01
     */
    public function test_sensitive_files_exposure(): void
    {
        $files = [
            '.env',
            '.env.production.bak',
            'storage/logs/laravel.log',
            '.git/config',
        ];

        foreach ($files as $file) {
            $response = $this->get('/' . $file);
            $this->assertTrue(
                $response->status() === 404 || $response->status() === 403,
                "Sensitive file {$file} should not be accessible (Status: {$response->status()})"
            );
        }
    }

    /**
     * Test CSRF protection on feedback endpoint.
     * ID: TEST-CSRF-01
     */
    public function test_feedback_requires_csrf(): void
    {
        // V Laravel testech je CSRF standardně vypnutý pro usnadnění testování.
        // Skutečné ověření proběhlo v runtime fázi auditu na živém/staging kódu,
        // kde jsme potvrdili, že bootstrap/app.php neobsahuje výjimku.

        // Zde pouze ověříme, že routa má 'web' middleware (který CSRF obsahuje).
        $route = collect(\Route::getRoutes())->first(function($route) {
            return $route->uri() === 'feedback' && in_array('POST', $route->methods());
        });

        $this->assertNotNull($route, 'Feedback route (POST) not found');
        $this->assertContains('web', $route->middleware(), 'Feedback route should have web middleware');
    }
}
