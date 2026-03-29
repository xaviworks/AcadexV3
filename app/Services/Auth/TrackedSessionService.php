<?php

namespace App\Services\Auth;

use Illuminate\Support\Facades\DB;

class TrackedSessionService
{
    /**
     * Destroy session IDs in both the real session handler and the tracking table.
     *
     * @param  iterable<int, string>  $sessionIds
     */
    public function destroySessions(iterable $sessionIds): int
    {
        $ids = collect($sessionIds)
            ->filter(fn ($id) => filled($id))
            ->map(fn ($id) => (string) $id)
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            return 0;
        }

        $handler = app('session')->driver()->getHandler();

        foreach ($ids as $id) {
            $handler->destroy($id);
        }

        DB::table('sessions')->whereIn('id', $ids)->delete();

        return $ids->count();
    }

    public function destroyUserSessions(int $userId, ?string $exceptSessionId = null): int
    {
        $query = DB::table('sessions')->where('user_id', $userId);

        if ($exceptSessionId) {
            $query->where('id', '!=', $exceptSessionId);
        }

        return $this->destroySessions($query->pluck('id'));
    }

    public function cleanupExpiredSessions(int $expirationTimestamp, ?int $userId = null): int
    {
        $query = DB::table('sessions')->where('last_activity', '<', $expirationTimestamp);

        if ($userId) {
            $query->where('user_id', $userId);
        }

        return $this->destroySessions($query->pluck('id'));
    }
}
