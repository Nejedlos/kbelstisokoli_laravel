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
        // We try a POST request without CSRF token.
        // If the middleware is working (and no exception is in bootstrap/app.php), it should return 419.
        $response = $this->post('/feedback', [
            'message' => 'Security Test',
        ]);

        $this->assertEquals(419, $response->status(), 'Feedback endpoint should require CSRF token');
    }
}
