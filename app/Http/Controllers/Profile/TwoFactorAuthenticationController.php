<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PragmaRX\Google2FA\Google2FA;
use Illuminate\Validation\ValidationException;

class TwoFactorAuthenticationController extends Controller
{
    /**
     * Enable 2FA for the user.
     */
    public function store(Request $request)
    {
        $user = $request->user();
        $google2fa = new Google2FA();

        $user->forceFill([
            'two_factor_secret' => $google2fa->generateSecretKey(),
            'two_factor_recovery_codes' => encrypt(json_encode(
                \Illuminate\Support\Collection::times(8, function () {
                    return \Illuminate\Support\Str::random(10) . '-' . \Illuminate\Support\Str::random(10);
                })->all()
            )),
        ])->save();

        return back()->with('success', 'Two-factor authentication has been enabled. Please scan the QR code with your authenticator app.');
    }

    /**
     * Disable 2FA for the user.
     */
    public function destroy(Request $request)
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $request->user()->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();

        // Also clear trusted devices
        $request->user()->devices()->delete();

        return back()->with('success', 'Two-factor authentication has been disabled successfully.');
    }

    /**
     * Confirm 2FA with a code.
     */
    public function confirm(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string'],
        ]);

        $user = $request->user();
        $google2fa = new Google2FA();

        if (! $google2fa->verifyKey($user->two_factor_secret, $request->code)) {
            throw ValidationException::withMessages([
                'code' => __('The provided two factor authentication code was invalid.'),
            ]);
        }

        $user->forceFill([
            'two_factor_confirmed_at' => now(),
        ])->save();

        return back()->with('success', 'Two-factor authentication has been confirmed and is now fully active.');
    }

    /**
     * Reveal QR code after password verification.
     */
    public function revealQR(Request $request)
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        return response()->json([
            'success' => true,
        ]);
    }

    /**
     * Get the current recovery codes.
     */
    public function showRecoveryCodes(Request $request)
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        return response()->json([
            'recovery_codes' => json_decode(decrypt($request->user()->two_factor_recovery_codes)),
        ]);
    }

    /**
     * Regenerate recovery codes.
     */
    public function regenerateRecoveryCodes(Request $request)
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $request->user()->forceFill([
            'two_factor_recovery_codes' => encrypt(json_encode(
                \Illuminate\Support\Collection::times(8, function () {
                    return \Illuminate\Support\Str::random(10) . '-' . \Illuminate\Support\Str::random(10);
                })->all()
            )),
        ])->save();

        return back()->with('status', 'recovery-codes-regenerated');
    }
}
