<?php

namespace App\Http\Controllers;

use App\Models\AcademicPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class AcademicPeriodController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    //  View all academic periods
    public function index()
    {
        Gate::authorize('admin');

        $periods = AcademicPeriod::orderBy('created_at', 'desc')->get();
        return view('admin.academic-periods.index', compact('periods'));
    }

    //  Auto-generate next academic year
    public function generate()
    {
        Gate::authorize('admin');

        $latest = AcademicPeriod::orderBy('created_at', 'desc')->first();
        $currentYear = now()->year;

        if ($latest && preg_match('/^\d{4}-\d{4}$/', $latest->academic_year)) {
            [$startYear, $endYear] = explode('-', $latest->academic_year);
            $startYear = intval($startYear) + 1;
            $endYear = intval($endYear) + 1;
        } else {
            $startYear = $currentYear;
            $endYear = $currentYear + 1;
        }

        $newAcademicYear = "{$startYear}-{$endYear}";

        $alreadyExists = AcademicPeriod::where('academic_year', $newAcademicYear)->count() >= 2 &&
                         AcademicPeriod::where('academic_year', $startYear)->where('semester', 'Summer')->exists();

        if (!$alreadyExists) {
            AcademicPeriod::insert([
                [
                    'academic_year' => $newAcademicYear,
                    'semester' => '1st',
                    'created_by' => Auth::id(),
                    'updated_by' => Auth::id(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'academic_year' => $newAcademicYear,
                    'semester' => '2nd',
                    'created_by' => Auth::id(),
                    'updated_by' => Auth::id(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'academic_year' => $startYear,
                    'semester' => 'Summer',
                    'created_by' => Auth::id(),
                    'updated_by' => Auth::id(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        }

        return redirect()->route('admin.academicPeriods')->with('success', 'New academic periods generated successfully.');
    }
}
