<?php

namespace App\Listeners;

use App\Models\User;
use App\Services\NotificationService;
use App\Notifications\SecurityAlert;
use Illuminate\Support\Facades\Log;

/**
 * Listener for new user creation events.
 * Sends security notifications to admins.
 */
class NotifyUserCreated
{
    /**
     * Handle user created events.
     * Can be called directly or via event.
     */
    public static function handle(User $newUser, ?User $createdBy = null): void
    {
        try {
            $metadata = [
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'created_at' => now()->toIso8601String(),
            ];

            NotificationService::notifySecurityAlert(
                SecurityAlert::TYPE_NEW_USER,
                $newUser,
                $createdBy,
                $metadata
            );

            Log::info('New user notification sent', [
                'new_user_id' => $newUser->id,
                'new_user_email' => $newUser->email,
                'created_by_id' => $createdBy?->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send new user notification', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
