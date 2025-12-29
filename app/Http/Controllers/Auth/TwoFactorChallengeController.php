<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserDevice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Jenssegers\Agent\Agent;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorChallengeController extends Controller
{
    public function create()
    {
        if (!session()->has('auth.2fa.id')) {
            return redirect()->route('login');
        }

        $userId = session()->get('auth.2fa.id');
        $user = User::findOrFail($userId);

        return view('auth.two-factor-challenge', ['user' => $user]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string'],
            'device_fingerprint' => ['nullable', 'string'],
            'recovery' => ['nullable', 'string'],
        ]);

        $userId = session()->get('auth.2fa.id');
        $fingerprint = $request->input('device_fingerprint') ?? session()->get('auth.2fa.fingerprint');
        $isRecoveryMode = $request->input('recovery') === '1';

        if (!$userId) {
            return redirect()->route('login');
        }

        $user = User::findOrFail($userId);
        
        // If user doesn't have 2FA secret set up, we shouldn't be here
        if (!$user->two_factor_secret) {
             Auth::login($user);
             session()->forget(['auth.2fa.id', 'auth.2fa.fingerprint']);
             session()->regenerate();
             return redirect()->intended(route('dashboard'));
        }

        $valid = false;
        
        if ($isRecoveryMode) {
            // Verify recovery code
            $recoveryCodes = json_decode(decrypt($user->two_factor_recovery_codes), true);
            
            if (is_array($recoveryCodes) && in_array($request->code, $recoveryCodes)) {
                $valid = true;
                
                // Remove used recovery code
                $recoveryCodes = array_values(array_diff($recoveryCodes, [$request->code]));
                $user->forceFill([
                    'two_factor_recovery_codes' => encrypt(json_encode($recoveryCodes)),
                ])->save();
            }
        } else {
            // Verify authenticator code
            $google2fa = new Google2FA();
            $valid = $google2fa->verifyKey($user->two_factor_secret, $request->code);
        }

        if ($valid) {
            // Mark 2FA as confirmed on first successful challenge
            if (!$user->two_factor_confirmed_at) {
                $user->forceFill([
                    'two_factor_confirmed_at' => now(),
                ])->save();
            }

            Auth::login($user);
            session()->forget(['auth.2fa.id', 'auth.2fa.fingerprint']);
            
            // Store device fingerprint in session for middleware to use
            if ($fingerprint) {
                session()->put('device_fingerprint', $fingerprint);
            }
            
            session()->regenerate();

            // Save device as trusted
            if ($fingerprint) {
                $agent = new Agent();
                $agent->setUserAgent($request->userAgent());

                UserDevice::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'device_fingerprint' => $fingerprint,
                    ],
                    [
                        'ip_address' => $request->ip(),
                        'browser' => $agent->browser(),
                        'platform' => $agent->platform(),
                        'last_used_at' => now(),
                    ]
                );
            }

            return redirect()->intended(route('dashboard'));
        }

        $errorMessage = $isRecoveryMode 
            ? 'The provided recovery code was invalid or has already been used.'
            : 'The provided two factor authentication code was invalid.';

        return back()->withErrors(['code' => $errorMessage]);
    }

    public function destroy(Request $request)
    {
        $request->session()->forget(['auth.2fa.id', 'auth.2fa.fingerprint']);
        return redirect()->route('login');
    }
}
