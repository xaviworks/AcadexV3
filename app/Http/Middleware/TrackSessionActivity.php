<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Jenssegers\Agent\Agent;
use Symfony\Component\HttpFoundation\Response;

class TrackSessionActivity
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only track for authenticated users
        if (Auth::check() && $request->session()->getId()) {
                $this->updateSessionMetadata($request);
        }

        return $response;
    }

    /**
     * Update session metadata with device information.
     *
     * Rate-limited to one DB write per minute using a session key so that
     * high-frequency AJAX endpoints (notifications, announcements, session-check)
     * do not hammer the sessions table on every request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    protected function updateSessionMetadata(Request $request): void
    {
        try {
            // Only write to the DB at most once per 60 seconds per session.
            $lastTracked = $request->session()->get('_session_tracked_at', 0);
            if (time() - $lastTracked < 60) {
                return;
            }
            $request->session()->put('_session_tracked_at', time());

            $agent = new Agent();
            $agent->setUserAgent($request->userAgent());

            $updateData = [
                'last_activity_at' => now(),
                'device_type' => $this->getDeviceType($agent),
                'browser' => $agent->browser() ?: 'Unknown',
                'platform' => $agent->platform() ?: 'Unknown',
            ];

            // Add device fingerprint from session if available
            if ($request->session()->has('device_fingerprint')) {
                $updateData['device_fingerprint'] = $request->session()->get('device_fingerprint');
            }
            // Or from request if provided
            elseif ($request->has('device_fingerprint')) {
                $updateData['device_fingerprint'] = $request->input('device_fingerprint');
            }

            DB::table('sessions')
                ->where('id', $request->session()->getId())
                ->update($updateData);
        } catch (\Exception $e) {
            // Silently fail to avoid disrupting the request
            \Log::error('Failed to update session metadata: ' . $e->getMessage());
        }
    }

    /**
     * Determine device type from agent.
     *
     * @param  \Jenssegers\Agent\Agent  $agent
     * @return string
     */
    protected function getDeviceType(Agent $agent): string
    {
        if ($agent->isDesktop()) {
            return 'Desktop';
        }

        if ($agent->isTablet()) {
            return 'Tablet';
        }

        if ($agent->isMobile()) {
            return 'Mobile';
        }

        return 'Unknown';
    }
}
