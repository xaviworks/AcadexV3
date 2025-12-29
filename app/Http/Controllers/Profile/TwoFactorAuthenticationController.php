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
        try {
            $request->validate([
                'password' => ['required', 'current_password'],
            ]);

            $recoveryCodes = json_decode(decrypt($request->user()->two_factor_recovery_codes));

            // Ensure we have valid recovery codes
            if (!$recoveryCodes || !is_array($recoveryCodes) || count($recoveryCodes) === 0) {
                return response()->json([
                    'message' => 'No recovery codes found. Please regenerate your codes.',
                ], 404);
            }

            return response()->json([
                'recovery_codes' => $recoveryCodes,
            ], 200);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Invalid password. Please try again.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while retrieving recovery codes.',
            ], 500);
        }
    }

    /**
     * Regenerate recovery codes.
     */
    public function regenerateRecoveryCodes(Request $request)
    {
        try {
            $request->validate([
                'password' => ['required', 'current_password'],
            ]);

            // Generate new recovery codes
            $newCodes = \Illuminate\Support\Collection::times(8, function () {
                return \Illuminate\Support\Str::random(10) . '-' . \Illuminate\Support\Str::random(10);
            })->all();

            $request->user()->forceFill([
                'two_factor_recovery_codes' => encrypt(json_encode($newCodes)),
            ])->save();

            // Return new codes in JSON for AJAX requests
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'recovery_codes' => $newCodes,
                    'message' => 'Recovery codes regenerated successfully.'
                ], 200);
            }

            // Fallback for non-AJAX requests
            return back()->with('status', 'recovery-codes-regenerated');
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'message' => 'Invalid password. Please try again.',
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;
        } catch (\Exception $e) {
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'message' => 'An error occurred while regenerating recovery codes.',
                ], 500);
            }
            throw $e;
        }
    }
}
