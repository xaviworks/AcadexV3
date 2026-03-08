<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class UnverifiedEmailVerificationNotificationController extends Controller
{
    /**
     * Send a new email verification notification for unverified users.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = Auth::guard('unverified')->user();

        if (!$user) {
            return redirect()->route('login');
        }

        if ($user->hasVerifiedEmail()) {
            return redirect()->intended(route('verification.notice', absolute: false));
        }

        try {
            $user->sendEmailVerificationNotification();
        } catch (\Exception $e) {
            $msg = '[Acadex] Resend verification email failed'
                . ' | user_id=' . $user->id
                . ' | email=' . $user->email
                . ' | exception=' . get_class($e)
                . ' | error=' . $e->getMessage();
            error_log($msg);
            Log::error('Resend verification email failed', [
                'user_id'   => $user->id,
                'email'     => $user->email,
                'error'     => $e->getMessage(),
                'exception' => get_class($e),
            ]);
            return back()->with('warning', 'Could not send the verification email. Please try again later.');
        }

        return back()->with('status', 'verification-link-sent');
    }
}
