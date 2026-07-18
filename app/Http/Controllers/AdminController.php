<?php

namespace App\Http\Controllers;

use App\Models\AcademicPeriod;
use App\Models\UserLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function academicPeriods()
    {
        Gate::authorize('admin');

        $periods = AcademicPeriod::orderBy('academic_year', 'desc')->orderBy('semester')->get();

        return view('admin.academic-periods', compact('periods'));
    }

    public function viewUserLogs(Request $request)
    {
        Gate::authorize('admin');

        $dateToday = now()->timezone(config('app.timezone'))->format('Y-m-d');
        $selectedDate = $request->input('date', $dateToday);

        $userLogs = UserLog::whereDate('created_at', $selectedDate)->get();

        return view('admin.user-logs', compact('userLogs', 'dateToday', 'selectedDate'));
    }
}
