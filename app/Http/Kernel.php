<?php

namespace App\Http;

use App\Http\Middleware\EnsureAcademicPeriodSet;
use App\Http\Middleware\EnsureUserIsVPAA;
use App\Http\Middleware\NoCacheHeaders;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * Global HTTP middleware stack.
     *
     * Runs during every request to your application.
     */
    protected $middleware = [
        SecurityHeaders::class,
        NoCacheHeaders::class, // Applies no-cache headers to all responses
    ];

    /**
     * Route middleware groups.
     */
    protected $middlewareGroups = [
        'web' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],

        'api' => [
            // \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            \Illuminate\Routing\Middleware\ThrottleRequests::class.':api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],
    ];

    /**
     * Route-specific middleware.
     */
    protected $routeMiddleware = [
        'no.cache' => NoCacheHeaders::class,
        'academic.period.set' => EnsureAcademicPeriodSet::class,
        'vpaa' => EnsureUserIsVPAA::class,
    ];
}
