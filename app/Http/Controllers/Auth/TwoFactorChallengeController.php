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
        ]);

        $userId = session()->get('auth.2fa.id');
        $fingerprint = $request->input('device_fingerprint') ?? session()->get('auth.2fa.fingerprint');

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

        $google2fa = new Google2FA();
        $valid = $google2fa->verifyKey($user->two_factor_secret, $request->code);

        if ($valid) {
            // Mark 2FA as confirmed on first successful challenge
            if (!$user->two_factor_confirmed_at) {
                $user->forceFill([
                    'two_factor_confirmed_at' => now(),
                ])->save();
            }

            Auth::login($user);
            session()->forget(['auth.2fa.id', 'auth.2fa.fingerprint']);
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

        return back()->withErrors(['code' => 'The provided two factor authentication code was invalid.']);
    }

    public function destroy(Request $request)
    {
        $request->session()->forget(['auth.2fa.id', 'auth.2fa.fingerprint']);
        return redirect()->route('login');
    }
}
