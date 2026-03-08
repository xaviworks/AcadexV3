<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Kicks out any authenticated user whose account has been disabled by an admin.
 *
 * This runs on every web request so that disabling a user takes effect
 * immediately — regardless of whether the session driver supports server-side
 * deletion (file driver does not; database driver does).
 */
class CheckUserIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && ! Auth::user()->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->expectsJson()) {
                return response()->json([
                    'authenticated' => false,
                    'message' => 'Your account has been disabled by an administrator.',
                ], 401);
            }

            return redirect()->route('login')
                ->withErrors(['email' => 'Your account has been disabled by an administrator. Please contact support.']);
        }

        return $next($request);
    }
}
