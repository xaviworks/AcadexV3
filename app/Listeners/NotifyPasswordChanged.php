<?php

namespace App\Listeners;

use App\Services\NotificationService;
use App\Notifications\SecurityAlert;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Log;

/**
 * Listener for password reset/change events.
 * Sends security notifications to admins.
 */
class NotifyPasswordChanged
{
    public function handle(PasswordReset $event): void
    {
        try {
            $user = $event->user;

            // Get device info from request
            $metadata = [
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'device' => $this->getDeviceType(),
                'browser' => $this->getBrowser(),
            ];

            NotificationService::notifySecurityAlert(
                SecurityAlert::TYPE_PASSWORD_CHANGED,
                $user,
                null, // No actor - user changed their own password
                $metadata
            );

            Log::info('Password change notification sent', [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send password change notification', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function getDeviceType(): string
    {
        $agent = request()->userAgent();
        if (preg_match('/mobile/i', $agent)) {
            return 'Mobile';
        }
        if (preg_match('/tablet/i', $agent)) {
            return 'Tablet';
        }
        return 'Desktop';
    }

    private function getBrowser(): string
    {
        $agent = request()->userAgent();
        if (preg_match('/Chrome/i', $agent)) return 'Chrome';
        if (preg_match('/Firefox/i', $agent)) return 'Firefox';
        if (preg_match('/Safari/i', $agent)) return 'Safari';
        if (preg_match('/Edge/i', $agent)) return 'Edge';
        return 'Unknown';
    }
}
