<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * Service for managing user sessions.
 *
 * Provides methods for force-logging out users by invalidating their sessions.
 * Used when deactivating users, disabling accounts, or administrative actions.
 */
class SessionService
{
    /**
     * Invalidate all sessions for a given user.
     *
     * This method deletes all session records for the user from the database,
     * effectively logging them out from all devices immediately.
     *
     * @param  User|int  $user  The user instance or user ID
     * @return array{success: bool, message: string, sessions_deleted: int}
     */
    public static function invalidateUserSessions(User|int $user): array
    {
        $userId = $user instanceof User ? $user->id : $user;

        try {
            $driver = config('session.driver');

            // Only attempt to delete sessions if using database driver
            if ($driver !== 'database') {
                Log::warning("Cannot invalidate sessions for user {$userId}: Session driver is '{$driver}', not 'database'.");

                return [
                    'success' => false,
                    'message' => "Session invalidation skipped: session driver is not 'database'.",
                    'sessions_deleted' => 0,
                ];
            }

            // Verify the sessions table exists and has the user_id column
            if (! Schema::hasTable('sessions') || ! Schema::hasColumn('sessions', 'user_id')) {
                Log::warning("Cannot invalidate sessions for user {$userId}: sessions table or user_id column not found.");

                return [
                    'success' => false,
                    'message' => 'Session invalidation skipped: sessions table not properly configured.',
                    'sessions_deleted' => 0,
                ];
            }

            // Delete all sessions for this user
            $deletedCount = DB::table('sessions')
                ->where('user_id', $userId)
                ->delete();

            Log::info("Invalidated {$deletedCount} session(s) for user {$userId}.");

            return [
                'success' => true,
                'message' => "Successfully invalidated {$deletedCount} session(s).",
                'sessions_deleted' => $deletedCount,
            ];
        } catch (\Exception $e) {
            Log::error("Failed to invalidate sessions for user {$userId}: " . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Failed to invalidate sessions: ' . $e->getMessage(),
                'sessions_deleted' => 0,
            ];
        }
    }

    /**
     * Check if a user has any active sessions.
     *
     * @param  User|int  $user  The user instance or user ID
     * @return bool
     */
    public static function hasActiveSessions(User|int $user): bool
    {
        $userId = $user instanceof User ? $user->id : $user;

        $driver = config('session.driver');
        if ($driver !== 'database') {
            return false; // Cannot determine without database sessions
        }

        if (! Schema::hasTable('sessions') || ! Schema::hasColumn('sessions', 'user_id')) {
            return false;
        }

        return DB::table('sessions')
            ->where('user_id', $userId)
            ->exists();
    }

    /**
     * Get the count of active sessions for a user.
     *
     * @param  User|int  $user  The user instance or user ID
     * @return int
     */
    public static function getActiveSessionCount(User|int $user): int
    {
        $userId = $user instanceof User ? $user->id : $user;

        $driver = config('session.driver');
        if ($driver !== 'database') {
            return 0;
        }

        if (! Schema::hasTable('sessions') || ! Schema::hasColumn('sessions', 'user_id')) {
            return 0;
        }

        return DB::table('sessions')
            ->where('user_id', $userId)
            ->count();
    }
}
