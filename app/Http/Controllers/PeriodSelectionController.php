<?php

namespace App\Http\Controllers;

use App\Models\AcademicPeriod;
use Illuminate\Http\Request;

class PeriodSelectionController extends Controller
{
    public function show()
    {
        $periods = AcademicPeriod::where('is_deleted', false)
            ->orderBy('academic_year', 'desc')
            ->orderBy('semester')
            ->get();

        return view('instructor.select-academic-period', compact('periods'));
    }

    public function set(Request $request)
    {
        $request->validate([
            'academic_period_id' => 'required|exists:academic_periods,id',
        ]);

        session(['active_academic_period_id' => $request->academic_period_id]);

        return redirect()->intended(route('instructor.dashboard'));
    }
}
