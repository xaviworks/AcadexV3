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

    // 📘 View all academic periods
    public function index()
    {
        Gate::authorize('admin');

        $periods = AcademicPeriod::orderBy('created_at', 'desc')->get();
        return view('admin.academic-periods.index', compact('periods'));
    }

    // 🔄 Auto-generate next academic year
    public function generate()
    {
        Gate::authorize('admin');

        [$startYear, $endYear] = $this->resolveNextAcademicYearRange();
        $newAcademicYear = "{$startYear}-{$endYear}";
        $targetPeriods = [
            ['academic_year' => $newAcademicYear, 'semester' => '1st'],
            ['academic_year' => $newAcademicYear, 'semester' => '2nd'],
            ['academic_year' => (string) $startYear, 'semester' => 'Summer'],
        ];

        $existingKeys = AcademicPeriod::query()
            ->where(function ($query) use ($newAcademicYear, $startYear) {
                $query->where(function ($nested) use ($newAcademicYear) {
                    $nested->where('academic_year', $newAcademicYear)
                        ->whereIn('semester', ['1st', '2nd']);
                })->orWhere(function ($nested) use ($startYear) {
                    $nested->where('academic_year', (string) $startYear)
                        ->where('semester', 'Summer');
                });
            })
            ->get(['academic_year', 'semester'])
            ->map(fn (AcademicPeriod $period) => $period->academic_year . '|' . $period->semester)
            ->all();

        $missingPeriods = array_values(array_filter($targetPeriods, function (array $period) use ($existingKeys) {
            return ! in_array($period['academic_year'] . '|' . $period['semester'], $existingKeys, true);
        }));

        if (empty($missingPeriods)) {
            return redirect()
                ->route('admin.academicPeriods')
                ->with('warning', "Academic period {$newAcademicYear} already exists.");
        }

        $timestamp = now();

        AcademicPeriod::insert(array_map(function (array $period) use ($timestamp) {
            return $period + [
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];
        }, $missingPeriods));

        $message = count($missingPeriods) === count($targetPeriods)
            ? 'New academic periods generated successfully.'
            : "Academic period {$newAcademicYear} was completed by creating the missing semester entries.";

        return redirect()->route('admin.academicPeriods')->with('success', $message);
    }

    private function resolveNextAcademicYearRange(): array
    {
        $latestFullYear = AcademicPeriod::query()
            ->get(['academic_year'])
            ->map(function (AcademicPeriod $period) {
                if (! preg_match('/^(?<start>\d{4})-(?<end>\d{4})$/', $period->academic_year, $matches)) {
                    return null;
                }

                return [
                    'start' => (int) $matches['start'],
                    'end' => (int) $matches['end'],
                ];
            })
            ->filter()
            ->sortByDesc('start')
            ->first();

        if ($latestFullYear) {
            return [
                $latestFullYear['start'] + 1,
                $latestFullYear['end'] + 1,
            ];
        }

        $latestSummerYear = AcademicPeriod::query()
            ->where('semester', 'Summer')
            ->get(['academic_year'])
            ->map(function (AcademicPeriod $period) {
                return preg_match('/^\d{4}$/', $period->academic_year)
                    ? (int) $period->academic_year
                    : null;
            })
            ->filter()
            ->max();

        if ($latestSummerYear) {
            return [$latestSummerYear, $latestSummerYear + 1];
        }

        $currentYear = now()->year;

        return [$currentYear, $currentYear + 1];
    }
}
