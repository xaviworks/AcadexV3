<?php

namespace App\Listeners;

use App\Models\User;
use App\Services\NotificationService;
use App\Notifications\SecurityAlert;
use Illuminate\Support\Facades\Log;

/**
 * Listener for account activation/deactivation events.
 * Sends security notifications to admins.
 */
class NotifyAccountStatusChanged
{
    /**
     * Handle account activated event.
     */
    public static function activated(User $user, ?User $activatedBy = null): void
    {
        try {
            NotificationService::notifySecurityAlert(
                SecurityAlert::TYPE_ACCOUNT_ACTIVATED,
                $user,
                $activatedBy,
                [
                    'ip_address' => request()->ip(),
                    'activated_at' => now()->toIso8601String(),
                ]
            );

            Log::info('Account activated notification sent', [
                'user_id' => $user->id,
                'activated_by_id' => $activatedBy?->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send account activated notification', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle account deactivated event.
     */
    public static function deactivated(User $user, ?User $deactivatedBy = null): void
    {
        try {
            NotificationService::notifySecurityAlert(
                SecurityAlert::TYPE_ACCOUNT_DEACTIVATED,
                $user,
                $deactivatedBy,
                [
                    'ip_address' => request()->ip(),
                    'deactivated_at' => now()->toIso8601String(),
                ]
            );

            Log::info('Account deactivated notification sent', [
                'user_id' => $user->id,
                'deactivated_by_id' => $deactivatedBy?->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send account deactivated notification', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
