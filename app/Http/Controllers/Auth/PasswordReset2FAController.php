<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;
use PragmaRX\Google2FA\Google2FA;

class PasswordReset2FAController extends Controller
{
    /**
     * Show the 2FA challenge for password reset.
     */
    public function show(): View|RedirectResponse
    {
        if (!session('password_reset.requires_2fa')) {
            return redirect()->route('password.request');
        }

        $email = session('password_reset.email');
        $user = User::where('email', $email)->first();

        if (!$user) {
            return redirect()->route('password.request')
                ->withErrors(['email' => 'User not found.']);
        }

        return view('auth.password-reset-2fa', compact('user'));
    }

    /**
     * Verify the 2FA code and send password reset link.
     */
    public function verify(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        if (!session('password_reset.requires_2fa')) {
            return redirect()->route('password.request');
        }

        $email = session('password_reset.email');
        $user = User::where('email', $email)->first();

        if (!$user) {
            return redirect()->route('password.request')
                ->withErrors(['email' => 'User not found.']);
        }

        // Verify the 2FA code
        $google2fa = new Google2FA();
        $valid = $google2fa->verifyKey($user->two_factor_secret, $request->code);

        if (!$valid) {
            return back()->withErrors(['code' => 'The provided two factor authentication code was invalid.']);
        }

        // 2FA verified, send password reset link
        $status = Password::sendResetLink(['email' => $email]);

        // Clear session
        session()->forget(['password_reset.email', 'password_reset.requires_2fa']);

        return $status == Password::RESET_LINK_SENT
                    ? redirect()->route('password.request')->with('status', __($status))
                    : redirect()->route('password.request')
                        ->withInput(['email' => $email])
                        ->withErrors(['email' => __($status)]);
    }
}
