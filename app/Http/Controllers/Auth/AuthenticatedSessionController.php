<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use App\Services\Auth\LoginFlowService;
use App\Services\Auth\TrackedSessionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function __construct(
        private readonly LoginFlowService $loginFlowService,
        private readonly TrackedSessionService $trackedSessionService,
    ) {
    }

    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->authenticate();
        $deviceFingerprint = $request->input('device_fingerprint');

        if (! $user->is_active) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'email' => 'Your account has been deactivated. Please contact the admin or your chairperson.',
            ]);
        }

        if ($user->role !== 3 && $this->loginFlowService->hasActiveSession($user->id, $deviceFingerprint)) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'email' => 'This account is already logged in on another device. Please logout from the other device first or contact your administrator.',
            ]);
        }

        if ($this->loginFlowService->requiresTwoFactorChallenge($user, $deviceFingerprint)) {
            return $this->loginFlowService->beginTwoFactorChallenge($request, $user, $deviceFingerprint);
        }

        Auth::login($user, $request->boolean('remember'));
        $this->loginFlowService->markTrustedDeviceUsed($user, $deviceFingerprint, $request->ip());
        $this->loginFlowService->sanitizeIntendedUrl($request, $user);
        $this->loginFlowService->finalizeLogin($request, $deviceFingerprint);

        return $this->loginFlowService->redirectAfterLogin($user);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $user = $request->user();
        $userId = $user?->getAuthIdentifier();
        $sessionId = $request->session()->getId();

        if ($user) {
            $user->forceFill(['remember_token' => null])->save();
        }

        Auth::guard('web')->logout();

        if ($sessionId) {
            $this->trackedSessionService->destroySessions([$sessionId]);
        } elseif ($userId) {
            $this->trackedSessionService->destroyUserSessions($userId);
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
