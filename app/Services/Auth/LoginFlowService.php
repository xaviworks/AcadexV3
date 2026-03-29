<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Models\UserDevice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Jenssegers\Agent\Agent;

class LoginFlowService
{
    public function __construct(private readonly TrackedSessionService $trackedSessionService)
    {
    }

    public function sanitizeIntendedUrl(Request $request, User $user): void
    {
        $intended = $request->session()->get('url.intended');

        if (! $intended) {
            return;
        }

        if ($this->pointsToAdminArea($intended) && ! Gate::forUser($user)->allows('admin')) {
            $request->session()->forget('url.intended');

            return;
        }

        if ($this->pointsToApiOrBackgroundEndpoint($intended)) {
            $request->session()->forget('url.intended');
        }
    }

    public function hasActiveSession(int $userId, ?string $currentFingerprint = null): bool
    {
        $expirationTimestamp = now()->subMinutes(config('session.lifetime', 120))->getTimestamp();

        $this->trackedSessionService->cleanupExpiredSessions($expirationTimestamp, $userId);

        $activeSessions = DB::table('sessions')
            ->where('user_id', $userId)
            ->where('last_activity', '>=', $expirationTimestamp)
            ->get();

        if ($activeSessions->isEmpty()) {
            return false;
        }

        if (! $currentFingerprint) {
            return true;
        }

        $sameDeviceSessions = [];
        $differentDeviceSessions = [];
        $agent = new Agent();
        $agent->setUserAgent(request()->userAgent());
        $currentBrowser = $agent->browser();
        $currentPlatform = $agent->platform();
        $currentDeviceType = $agent->isDesktop() ? 'Desktop' : ($agent->isTablet() ? 'Tablet' : 'Mobile');
        $currentIp = request()->ip();

        foreach ($activeSessions as $session) {
            if ($session->device_fingerprint) {
                $isSameDevice = $session->device_fingerprint === $currentFingerprint;
            } else {
                $fingerprintMatches = (
                    $session->browser === $currentBrowser &&
                    $session->platform === $currentPlatform &&
                    $session->device_type === $currentDeviceType
                );

                $isSameDevice = $fingerprintMatches && $session->ip_address === $currentIp;
            }

            if ($isSameDevice) {
                $sameDeviceSessions[] = $session->id;
            } else {
                $differentDeviceSessions[] = $session->id;
            }
        }

        if ($differentDeviceSessions !== []) {
            return true;
        }

        if ($sameDeviceSessions !== []) {
            $this->trackedSessionService->destroySessions($sameDeviceSessions);
        }

        return false;
    }

    public function requiresTwoFactorChallenge(User $user, ?string $deviceFingerprint): bool
    {
        if (! $user->two_factor_secret) {
            return false;
        }

        if (! $user->two_factor_confirmed_at) {
            return true;
        }

        if (! $deviceFingerprint) {
            return true;
        }

        return ! $this->hasValidTrustedDevice($user, $deviceFingerprint, request());
    }

    public function beginTwoFactorChallenge(
        Request $request,
        User $user,
        ?string $deviceFingerprint,
        bool $logoutCurrentUser = false
    ): RedirectResponse {
        $request->session()->put('auth.2fa.id', $user->id);
        $request->session()->put('auth.2fa.fingerprint', $deviceFingerprint);

        if ($logoutCurrentUser) {
            Auth::logout();
            $request->session()->regenerate();
        }

        return redirect()->route('two-factor.login');
    }

    public function markTrustedDeviceUsed(Request $request, User $user, ?string $deviceFingerprint): void
    {
        if (! $deviceFingerprint) {
            return;
        }

        $device = $this->resolveTrustedDevice($user, $deviceFingerprint, $request);

        if (! $device) {
            return;
        }

        $agent = new Agent();
        $agent->setUserAgent($request->userAgent());

        $plainToken = Str::random(64);
        $trustedUntil = $this->trustedUntil();

        $device->update([
            'trust_token_hash' => hash('sha256', $plainToken),
            'trusted_until' => $trustedUntil,
            'last_used_at' => now(),
            'ip_address' => $request->ip(),
            'browser' => $agent->browser(),
            'platform' => $agent->platform(),
        ]);

        $this->queueTrustedDeviceCookie($user, $plainToken, $trustedUntil);
    }

    public function rememberTrustedDevice(User $user, string $deviceFingerprint, string $ipAddress, ?string $userAgent): void
    {
        $agent = new Agent();
        $agent->setUserAgent($userAgent);
        $plainToken = Str::random(64);
        $trustedUntil = $this->trustedUntil();

        UserDevice::updateOrCreate(
            [
                'user_id' => $user->id,
                'device_fingerprint' => $deviceFingerprint,
            ],
            [
                'trust_token_hash' => hash('sha256', $plainToken),
                'trusted_until' => $trustedUntil,
                'ip_address' => $ipAddress,
                'browser' => $agent->browser(),
                'platform' => $agent->platform(),
                'last_used_at' => now(),
            ]
        );

        $this->queueTrustedDeviceCookie($user, $plainToken, $trustedUntil);
    }

    public function finalizeLogin(Request $request, ?string $deviceFingerprint): void
    {
        $request->session()->regenerate();

        if ($deviceFingerprint) {
            $request->session()->put('device_fingerprint', $deviceFingerprint);

            DB::table('sessions')
                ->where('id', $request->session()->getId())
                ->update(['device_fingerprint' => $deviceFingerprint]);
        } else {
            $request->session()->forget('device_fingerprint');
        }

        Session::forget('active_academic_period_id');
    }

    public function redirectAfterLogin(User $user): RedirectResponse
    {
        if ($user->role === 0) {
            return redirect()->route('select.academicPeriod');
        }

        if ($user->isVPAA()) {
            return redirect()->intended(route('vpaa.dashboard'));
        }

        return redirect()->intended(route('dashboard', absolute: false));
    }

    private function pointsToAdminArea(string $url): bool
    {
        $path = ltrim(parse_url($url, PHP_URL_PATH) ?? '', '/');

        if ($path === '') {
            return false;
        }

        return Str::startsWith($path, 'admin');
    }

    private function pointsToApiOrBackgroundEndpoint(string $url): bool
    {
        $path = ltrim(parse_url($url, PHP_URL_PATH) ?? '', '/');

        if ($path === '') {
            return false;
        }

        $blockedPatterns = [
            'api/',
            'notifications/poll',
            'notifications/unread-count',
            'notifications/paginate',
        ];

        foreach ($blockedPatterns as $pattern) {
            if (Str::startsWith($path, $pattern) || Str::contains($path, $pattern)) {
                return true;
            }
        }

        return false;
    }

    private function hasValidTrustedDevice(User $user, string $deviceFingerprint, Request $request): bool
    {
        return $this->resolveTrustedDevice($user, $deviceFingerprint, $request) !== null;
    }

    private function resolveTrustedDevice(User $user, string $deviceFingerprint, Request $request): ?UserDevice
    {
        $plainToken = $request->cookie($this->trustedDeviceCookieName($user));

        if (! is_string($plainToken) || $plainToken === '') {
            return null;
        }

        $device = $user->devices()
            ->where('device_fingerprint', $deviceFingerprint)
            ->whereNotNull('trust_token_hash')
            ->whereNotNull('trusted_until')
            ->first();

        if (! $device || ! $device->trusted_until || $device->trusted_until->isPast()) {
            return null;
        }

        return hash_equals((string) $device->trust_token_hash, hash('sha256', $plainToken))
            ? $device
            : null;
    }

    private function queueTrustedDeviceCookie(User $user, string $plainToken, Carbon $trustedUntil): void
    {
        $minutes = max(1, now()->diffInMinutes($trustedUntil, false));

        Cookie::queue(Cookie::make(
            $this->trustedDeviceCookieName($user),
            $plainToken,
            $minutes,
            config('session.path', '/'),
            config('session.domain'),
            config('session.secure'),
            true,
            false,
            config('session.same_site', 'lax')
        ));
    }

    private function trustedDeviceCookieName(User $user): string
    {
        return 'trusted_device_'.$user->id;
    }

    private function trustedUntil(): Carbon
    {
        return now()->addDays(max(1, (int) config('auth.trusted_device_days', 30)));
    }
}
