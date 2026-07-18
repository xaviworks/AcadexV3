<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class UserSessionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function forceLogout(Request $request, User $user)
    {
        Gate::authorize('admin');

        try {
            // Only attempt to delete sessions if the application is using database sessions
            $driver = config('session.driver');
            $skippedDeletion = false;
            if ($driver === 'database' && Schema::hasTable('sessions') && Schema::hasColumn('sessions', 'user_id')) {
                DB::table('sessions')
                    ->where('user_id', $user->id)
                    ->delete();
                // Invalidate remember_token so "remember me" cookie cannot silently recreate the session
                $user->forceFill(['remember_token' => null])->save();
            } else {
                // Log a note for maintainers if this isn't possible
                \Illuminate\Support\Facades\Log::warning("Skipping session deletion for user {$user->id}. Session driver: {$driver}");
                $skippedDeletion = true;
            }

            return response()->json([
                'success' => true,
                'skipped_session_deletion' => $skippedDeletion,
                'message' => $skippedDeletion
                    ? "Sessions not deleted because session driver is not 'database' or sessions table missing."
                    : "Successfully logged out {$user->first_name} {$user->last_name} from all devices.",
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to logout user. Please try again.',
            ], 500);
        }
    }

    public function countForUser(User $user)
    {
        Gate::authorize('admin');

        $driver = config('session.driver');
        $sessionCount = 0;
        if ($driver === 'database' && Schema::hasTable('sessions') && Schema::hasColumn('sessions', 'user_id')) {
            $sessionCount = DB::table('sessions')
                ->where('user_id', $user->id)
                ->count();
        }

        return response()->json([
            'success' => true,
            'count' => $sessionCount,
        ]);
    }

    public function sessions(Request $request)
    {
        Gate::authorize('admin');

        $sessionsQuery = DB::table('sessions')
            ->leftJoin('users', 'sessions.user_id', '=', 'users.id')
            ->select(
                'sessions.id',
                'sessions.user_id',
                'sessions.ip_address',
                'sessions.user_agent',
                'sessions.last_activity',
                'sessions.last_activity_at',
                'sessions.device_type',
                'sessions.browser',
                'sessions.platform',
                'sessions.device_fingerprint',
                DB::raw('CONCAT(users.first_name, " ", users.last_name) as user_name'),
                'users.email',
                'users.role',
                'users.is_active',
                'users.two_factor_secret',
                'users.two_factor_confirmed_at'
            )
            ->whereNotNull('sessions.user_id')
            ->orderByDesc('sessions.last_activity');

        $sessions = $sessionsQuery->paginate(10, ['*'], 'sessions_page')->through(function ($session) {
            $lastActivity = \Carbon\Carbon::createFromTimestamp($session->last_activity);
            $session->last_activity_formatted = $session->last_activity_at
                ? \Carbon\Carbon::parse($session->last_activity_at)->diffForHumans()
                : $lastActivity->diffForHumans();
            $session->last_activity_date = $lastActivity->format('M d, Y g:i A');
            $session->is_current = $session->id === session()->getId();

            // Determine session status
            $minutesInactive = $lastActivity->diffInMinutes(now());
            $session->status = $minutesInactive > config('session.lifetime', 120) ? 'expired' : 'active';

            return $session;
        })->appends($request->except('logs_page'));

        // Get user logs with optional date filtering and pagination
        $userLogsQuery = UserLog::with('user')
            ->orderByDesc('created_at');

        if ($request->has('date') && $request->input('date')) {
            $date = $request->input('date');
            $userLogsQuery->whereDate('created_at', $date);
        }

        $userLogs = $userLogsQuery->paginate(10, ['*'], 'logs_page')->appends($request->except('sessions_page'));
        $selectedDate = $request->input('date', '');

        return view('admin.sessions', compact('sessions', 'userLogs', 'selectedDate'));
    }

    public function revokeSession(Request $request)
    {
        Gate::authorize('admin');

        $request->validate([
            'session_id' => 'required|string',
            'password' => 'required|string',
        ]);

        // Verify admin password
        if (! Hash::check($request->input('password'), Auth::user()->password)) {
            return back()
                ->withErrors(['password' => 'The provided password is incorrect.'])
                ->withInput();
        }

        $sessionId = $request->input('session_id');

        // Prevent admin from revoking their own session
        if ($sessionId === session()->getId()) {
            return back()
                ->withErrors(['session_id' => 'You cannot revoke your own active session.'])
                ->withInput();
        }

        // Get session info before deletion for logging
        $sessionInfo = DB::table('sessions')
            ->leftJoin('users', 'sessions.user_id', '=', 'users.id')
            ->select('sessions.user_id', DB::raw('CONCAT(users.first_name, " ", users.last_name) as user_name'))
            ->where('sessions.id', $sessionId)
            ->first();

        if (! $sessionInfo) {
            return back()
                ->withErrors(['session_id' => 'Session not found or already terminated.'])
                ->withInput();
        }

        // Destroy the session in the actual session store (file, database, redis, etc.)
        // This is critical: deleting from the DB tracking table alone does NOT invalidate
        // file-based sessions, leaving the user still authenticated.
        app('session')->driver()->getHandler()->destroy($sessionId);

        // Also remove the tracking row (needed when session driver != database,
        // since the handler above only touches the driver's own store)
        DB::table('sessions')->where('id', $sessionId)->delete();

        // Invalidate remember_token so "remember me" cookie cannot silently recreate the session
        if ($sessionInfo->user_id) {
            User::where('id', $sessionInfo->user_id)->update(['remember_token' => null]);
        }

        // Log the session revocation
        DB::table('user_logs')->insert([
            'user_id' => $sessionInfo->user_id,
            'event_type' => 'session_revoked',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'browser' => (new \Jenssegers\Agent\Agent)->browser(),
            'device' => (new \Jenssegers\Agent\Agent)->device(),
            'platform' => (new \Jenssegers\Agent\Agent)->platform(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()
            ->route('admin.sessions')
            ->with('success', "Session for {$sessionInfo->user_name} has been revoked successfully.");
    }

    public function revokeUserSessions(Request $request)
    {
        Gate::authorize('admin');

        $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'password' => 'required|string',
        ]);

        // Verify admin password
        if (! Hash::check($request->input('password'), Auth::user()->password)) {
            return back()
                ->withErrors(['password' => 'The provided password is incorrect.'])
                ->withInput();
        }

        $userId = $request->input('user_id');

        // Prevent admin from revoking their own sessions
        if ($userId === Auth::id()) {
            return back()
                ->withErrors(['user_id' => 'You cannot revoke your own sessions.'])
                ->withInput();
        }

        // Get user info
        $user = User::findOrFail($userId);

        // Collect session IDs before deletion so we can destroy through the session handler
        $sessionIds = DB::table('sessions')
            ->where('user_id', $userId)
            ->pluck('id');

        $deleted = $sessionIds->count();

        // Destroy each session in the actual session store (file, database, redis, etc.)
        $handler = app('session')->driver()->getHandler();
        foreach ($sessionIds as $id) {
            $handler->destroy($id);
        }

        // Also remove tracking rows (needed when session driver != database)
        DB::table('sessions')->where('user_id', $userId)->delete();

        // Invalidate remember_token so "remember me" cookie cannot silently recreate a session
        $user->forceFill(['remember_token' => null])->save();

        if ($deleted > 0) {
            // Log the bulk session revocation
            DB::table('user_logs')->insert([
                'user_id' => $userId,
                'event_type' => 'all_sessions_revoked',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'browser' => (new \Jenssegers\Agent\Agent)->browser(),
                'device' => (new \Jenssegers\Agent\Agent)->device(),
                'platform' => (new \Jenssegers\Agent\Agent)->platform(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return redirect()
                ->route('admin.sessions')
                ->with('success', "All {$deleted} session(s) for {$user->full_name} have been revoked successfully.");
        }

        return back()
            ->withErrors(['user_id' => 'No active sessions found for this user.'])
            ->withInput();
    }

    public function reset2FA(Request $request)
    {
        Gate::authorize('admin');

        $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'password' => 'required|string',
        ]);

        // Verify admin password
        if (! Hash::check($request->input('password'), Auth::user()->password)) {
            return back()
                ->withErrors(['password' => 'The provided password is incorrect.'])
                ->withInput();
        }

        $userId = $request->input('user_id');

        // Prevent admin from resetting their own 2FA
        if ($userId === Auth::id()) {
            return back()
                ->withErrors(['user_id' => 'You cannot reset your own 2FA. Please use your profile settings.'])
                ->withInput();
        }

        // Get user info
        $user = User::findOrFail($userId);

        // Check if user has 2FA enabled
        if (! $user->two_factor_secret) {
            return back()
                ->with('info', "{$user->full_name} does not have two-factor authentication enabled.");
        }

        // Disable 2FA for the user
        $user->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();

        // Also clear trusted devices
        $user->devices()->delete();

        // Log the 2FA reset action
        DB::table('user_logs')->insert([
            'user_id' => $userId,
            'event_type' => '2fa_reset_by_admin',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'browser' => (new \Jenssegers\Agent\Agent)->browser(),
            'device' => (new \Jenssegers\Agent\Agent)->device(),
            'platform' => (new \Jenssegers\Agent\Agent)->platform(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()
            ->route('admin.sessions')
            ->with('success', "Two-factor authentication has been reset for {$user->full_name}. They will need to set it up again if needed.");
    }

    public function revokeAllSessions(Request $request)
    {
        Gate::authorize('admin');

        $request->validate([
            'password' => 'required|string',
        ]);

        // Verify admin password
        if (! Hash::check($request->input('password'), Auth::user()->password)) {
            return back()
                ->withErrors(['password' => 'The provided password is incorrect.'])
                ->withInput();
        }

        $currentSessionId = session()->getId();

        // Collect session IDs before deletion so we can destroy through the session handler
        $sessionIds = DB::table('sessions')
            ->where('id', '!=', $currentSessionId)
            ->whereNotNull('user_id')
            ->pluck('id');

        $deleted = $sessionIds->count();

        // Destroy each session in the actual session store (file, database, redis, etc.)
        $handler = app('session')->driver()->getHandler();
        foreach ($sessionIds as $id) {
            $handler->destroy($id);
        }

        // Also remove tracking rows (needed when session driver != database)
        DB::table('sessions')
            ->where('id', '!=', $currentSessionId)
            ->whereNotNull('user_id')
            ->delete();

        // Invalidate remember_token for all affected users so "remember me" cookies cannot recreate sessions
        User::where('id', '!=', Auth::id())->update(['remember_token' => null]);

        // Log the bulk revocation
        DB::table('user_logs')->insert([
            'user_id' => Auth::id(),
            'event_type' => 'bulk_sessions_revoked',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'browser' => (new \Jenssegers\Agent\Agent)->browser(),
            'device' => (new \Jenssegers\Agent\Agent)->device(),
            'platform' => (new \Jenssegers\Agent\Agent)->platform(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()
            ->route('admin.sessions')
            ->with('success', "Successfully revoked {$deleted} user session(s). Your session remains active.");
    }
}
