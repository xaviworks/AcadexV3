<?php

namespace Tests\Feature\Auth;

use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class CsrfHandlingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Route::middleware('web')->post('/testing/token-mismatch', function () {
            throw new TokenMismatchException('CSRF token mismatch.');
        });
    }

    public function test_csrf_refresh_endpoint_returns_a_fresh_token(): void
    {
        $response = $this->get(route('csrf.refresh'));

        $response->assertOk()
            ->assertJsonStructure(['token']);

        $this->assertSame(session()->token(), $response->json('token'));
    }

    public function test_html_token_mismatch_redirects_back_with_a_flash_error(): void
    {
        $response = $this->from('/login')->post('/testing/token-mismatch');

        $response->assertRedirect('/login');
        $response->assertSessionHas('error', 'Your session has expired. Please refresh and try again.');
    }

    public function test_json_token_mismatch_returns_a_structured_419_response(): void
    {
        $response = $this->postJson('/testing/token-mismatch');

        $response->assertStatus(419)
            ->assertJson([
                'message' => 'Your session has expired. Please refresh and try again.',
                'refresh' => true,
            ]);
    }
}
