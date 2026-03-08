<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EmailVerificationNotificationController extends Controller
{
    /**
     * Send a new email verification notification.
     */
    public function store(Request $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard', absolute: false));
        }

        try {
            $request->user()->sendEmailVerificationNotification();
        } catch (\Exception $e) {
            Log::error('Email verification notification failed', [
                'error'     => $e->getMessage(),
                'exception' => get_class($e),
            ]);
            return back()->with('warning', 'Could not send the verification email. Please try again later.');
        }

        return back()->with('status', 'verification-link-sent');
    }
}
