<?php

namespace App\Listeners;

use App\Services\UserLogRecorder;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\DB;
use Jenssegers\Agent\Agent;

class LogUserLogout
{
    public function __construct(private readonly UserLogRecorder $recorder)
    {
    }

    public function handle(Logout $event)
    {
        $user = $event->user;

        if (!$user) {
            return;
        }

        $userId = $user->getAuthIdentifier();

        $agent = new Agent();
        $browser = $agent->browser();
        $platform = $agent->platform();
        $device = $agent->isMobile() ? 'Mobile' : ($agent->isTablet() ? 'Tablet' : 'Desktop');

        $this->recorder->record($userId, 'logout', [
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'browser' => $browser,
            'platform' => $platform,
            'device' => $device,
        ]);

        // Remove the session tracking row so the admin monitor
        // doesn't show stale entries after logout (works with both
        // database and file session drivers).
        try {
            DB::table('sessions')
                ->where('id', session()->getId())
                ->delete();
        } catch (\Exception $e) {
            // non-critical
        }
    }
}
