<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Support\Organization\GEContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class UnverifiedEmailVerificationPromptController extends Controller
{
    /**
     * Display the email verification prompt for unverified users.
     */
    public function __invoke(Request $request): RedirectResponse|View
    {
        $user = Auth::guard('unverified')->user();

        if (!$user) {
            return redirect()->route('login');
        }

        if ($user->hasVerifiedEmail()) {
            $isGEDepartment = GEContext::isGERegistrationTarget(
                (int) $user->department_id,
                (int) $user->course_id
            );

            $approvalMessage = $isGEDepartment 
                ? 'Your account request has been submitted and is pending GE Coordinator approval.'
                : 'Your account request has been submitted and is pending Department Chairperson approval.';

            // Log out the unverified user
            Auth::guard('unverified')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('status', $approvalMessage);
        }

        return view('auth.verify-email-unverified');
    }
}
