<?php

namespace App\Http\Middleware;

use App\Models\AcademicPeriod;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureAcademicPeriodSet
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        $isInstructor = $user && $user->role === 0;
        $isGeCoordinator = $user && $user->role === 4;
        $autoAssignLatestPeriod = $user && in_array($user->role, [2, 5], true);

        if (
            Auth::check() &&
            $autoAssignLatestPeriod &&
            !session()->has('active_academic_period_id')
        ) {
            $latestPeriod = AcademicPeriod::where('is_deleted', false)
                ->orderByDesc('academic_year')
                ->orderByRaw("CASE semester WHEN '1st' THEN 1 WHEN '2nd' THEN 2 WHEN 'Summer' THEN 3 ELSE 4 END")
                ->first();

            if ($latestPeriod) {
                session(['active_academic_period_id' => $latestPeriod->id]);
            }
        }
        
        if (
            Auth::check() &&
            ($isInstructor || $isGeCoordinator) &&
            !session()->has('active_academic_period_id') &&
            !$request->is('select-academic-period') &&
            !$request->is('set-academic-period')
        ) {
            // Return JSON for AJAX requests to avoid returning HTML content that causes
            // fetch().json() to throw parse errors in the frontend.
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['error' => 'Academic period not selected'], 403);
            }

            if ($request->isMethod('get')) {
                $request->session()->put('academic_period_redirect_url', $request->getRequestUri());
            }

            return redirect()->route('select.academicPeriod');
        }

        return $next($request);
    }
}
