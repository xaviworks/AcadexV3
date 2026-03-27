<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Auth\LoginFlowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    public function __construct(private readonly LoginFlowService $loginFlowService)
    {
    }

    /**
     * Redirect to Google OAuth page.
     *
     * @return RedirectResponse
     */
    public function redirectToGoogle(Request $request): RedirectResponse
    {
        if ($request->filled('device_fingerprint')) {
            $request->session()->put(
                'auth.google.device_fingerprint',
                (string) $request->string('device_fingerprint')
            );
        }

        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle Google OAuth callback.
     *
     * @return RedirectResponse
     */
    public function handleGoogleCallback(Request $request): RedirectResponse
    {
        try {
            $deviceFingerprint = $this->resolveDeviceFingerprint($request);

            // Log the incoming request for debugging
            Log::info('Google OAuth Callback Received', [
                'has_state' => $request->has('state'),
                'has_code' => $request->has('code'),
                'session_id' => $request->session()->getId(),
                'has_device_fingerprint' => filled($deviceFingerprint),
            ]);

            $googleUser = Socialite::driver('google')->user();
            
            $email = $googleUser->getEmail();
            $googleId = $googleUser->getId();
            
            // Validate email is not null
            if (!$email) {
                return redirect()->route('login')
                    ->withErrors(['email' => 'Unable to retrieve email from Google account.']);
            }
            
            // Only allow @brokenshire.edu.ph domain emails
            if (!str_ends_with($email, '@brokenshire.edu.ph')) {
                return redirect()->route('login')
                    ->withErrors(['email' => 'Only @brokenshire.edu.ph email addresses are allowed.']);
            }

            // Find user by google_id or email (active users only)
            $user = User::where(function ($query) use ($googleId, $email) {
                    $query->where('google_id', $googleId)
                          ->orWhere('email', $email);
                })
                ->where('is_active', true)
                ->first();

            // User not found - prevent auto-registration
            if (!$user) {
                return redirect()->route('login')
                    ->withErrors(['email' => 'No account found. Please contact your administrator.']);
            }

            // Update google_id if not set
            if (!$user->google_id) {
                $user->update(['google_id' => $googleId]);
            }

            if ($user->role !== 3 && $this->loginFlowService->hasActiveSession($user->id, $deviceFingerprint)) {
                    return redirect()->route('login')->withErrors([
                        'email' => 'This account is already logged in on another device. Please logout from the other device first or contact your administrator.',
                    ]);
            }

            if ($this->loginFlowService->requiresTwoFactorChallenge($user, $deviceFingerprint)) {
                return $this->loginFlowService->beginTwoFactorChallenge($request, $user, $deviceFingerprint);
            }

            $this->loginFlowService->markTrustedDeviceUsed($user, $deviceFingerprint, $request->ip());
            Auth::login($user);
            $this->loginFlowService->sanitizeIntendedUrl($request, $user);
            $this->loginFlowService->finalizeLogin($request, $deviceFingerprint);

            return $this->loginFlowService->redirectAfterLogin($user);

        } catch (\Laravel\Socialite\Two\InvalidStateException $e) {
            Log::error('Google OAuth Invalid State: ' . $e->getMessage());
            
            return redirect()->route('login')
                ->withErrors(['email' => 'Your Google sign-in session expired. Please try again.']);
                
        } catch (\Exception $e) {
            Log::error('Google OAuth Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('login')
                ->withErrors(['email' => 'Unable to login with Google. Please try again.']);
        }
    }

    private function resolveDeviceFingerprint(Request $request): ?string
    {
        $fingerprint = $request->input('device_fingerprint');

        if ($fingerprint) {
            return (string) $fingerprint;
        }

        $storedFingerprint = $request->session()->pull('auth.google.device_fingerprint');

        return $storedFingerprint ? (string) $storedFingerprint : null;
    }

}
