<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class NoCacheHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Get content type, defaulting to text/html for responses without content-type
        $contentType = $response->headers->get('Content-Type', 'text/html');

        // Apply no-cache headers for HTML responses (pages that should not be cached)
        // This prevents browser back button from showing stale authenticated pages
        if (str_contains($contentType, 'text/html') || $contentType === null) {
            $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0, post-check=0, pre-check=0');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT');
            $response->headers->set('Vary', 'Cookie, Authorization');
        }

        return $response;
    }
}

