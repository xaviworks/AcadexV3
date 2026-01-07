<?php

namespace App\Services\Notifications;

use App\Models\User;
use App\Notifications\SecurityAlert;
use Illuminate\Support\Facades\Notification;

/**
 * Trait for security-related notifications.
 * Handles security alerts to admin users.
 */
trait SendsSecurityNotifications
{
    /**
     * Send security alert to all admin users.
     */
    public static function notifySecurityAlert(
        string $alertType,
        ?User $affectedUser = null,
        ?User $actorUser = null,
        array $metadata = []
    ): void {
        // Add request metadata if not provided
        if (!isset($metadata['ip_address'])) {
            $metadata['ip_address'] = request()->ip();
        }
        if (!isset($metadata['user_agent'])) {
            $metadata['user_agent'] = request()->userAgent();
        }

        $admins = User::where('role', 3)->where('is_active', true)->get();

        if ($admins->isNotEmpty()) {
            Notification::send(
                $admins,
                new SecurityAlert($alertType, $affectedUser, $actorUser, $metadata)
            );
        }
    }
}
