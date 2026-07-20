<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTemporaryPasswordIsChanged
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user?->must_change_password) {
            return $next($request);
        }

        if ($this->isAllowedRoute($request)) {
            return $next($request);
        }

        return redirect()
            ->route('profile.edit')
            ->with('warning', 'Please change your temporary password before continuing.');
    }

    private function isAllowedRoute(Request $request): bool
    {
        return $request->routeIs(
            'profile.edit',
            'profile.password.update',
            'password.update',
            'logout',
        );
    }
}
