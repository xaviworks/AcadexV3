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
    /**
     * How often (in seconds) to write session metadata to the DB.
     * Prevents a DB UPDATE on every single request.
     */
    private const UPDATE_INTERVAL_SECONDS = 60;

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only track for authenticated users
        if (Auth::check() && $request->session()->getId()) {
            $lastTracked = $request->session()->get('_session_tracked_at', 0);

            if ((time() - $lastTracked) >= self::UPDATE_INTERVAL_SECONDS) {
                $this->updateSessionMetadata($request);
                $request->session()->put('_session_tracked_at', time());
            }
        }

        return $response;
    }

    /**
     * Update session metadata with device information.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    protected function updateSessionMetadata(Request $request): void
    {
        try {
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

            // Use upsert so the row is created even when SESSION_DRIVER=file
            // (file driver never inserts into the sessions table itself).
            DB::table('sessions')->upsert(
                [array_merge([
                    'id'            => $request->session()->getId(),
                    'user_id'       => Auth::id(),
                    'ip_address'    => $request->ip(),
                    'user_agent'    => substr((string) $request->userAgent(), 0, 500),
                    'payload'       => '',
                    'last_activity' => time(),
                ], $updateData)],
                ['id'],
                ['user_id', 'ip_address', 'user_agent', 'last_activity',
                 'last_activity_at', 'device_type', 'browser', 'platform', 'device_fingerprint']
            );
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
