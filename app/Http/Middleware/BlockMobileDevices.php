<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Jenssegers\Agent\Agent;

/**
 * BlockMobileDevices Middleware
 * 
 * Blocks all mobile device access to the application.
 * Only desktop computers and laptops can access.
 * Mobile phones and tablets receive a blocked message.
 */
class BlockMobileDevices
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $agent = new Agent();
        
        // Set the user agent from the request
        $agent->setUserAgent($request->header('User-Agent'));
        
        // Block all mobile devices (phones and tablets)
        if ($agent->isMobile() || $agent->isTablet()) {
            // Return the mobile blocked view
            return response()->view('mobile-blocked', [], 403);
        }

        return $next($request);
    }
}
