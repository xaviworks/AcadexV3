<?php

namespace Tests\Feature;

use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    public function test_security_headers_are_sent_for_https_requests(): void
    {
        $response = $this->get('https://localhost/');

        $response->assertHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('Permissions-Policy');
        $response->assertHeader('Content-Security-Policy');
        $this->assertStringContainsString("default-src 'self'", $response->headers->get('Content-Security-Policy'));
        $this->assertStringContainsString('camera=()', $response->headers->get('Permissions-Policy'));
    }

    public function test_x_powered_by_header_is_removed(): void
    {
        $this->app['router']->get('/test-powered-by-header', function () {
            return response('ok')->header('X-Powered-By', 'PHP/8.2.32');
        });

        $response = $this->get('/test-powered-by-header');

        $response->assertHeaderMissing('X-Powered-By');
    }

    public function test_hsts_is_only_sent_for_secure_requests(): void
    {
        $response = $this->get('/');

        $response->assertHeaderMissing('Strict-Transport-Security');
    }
}
