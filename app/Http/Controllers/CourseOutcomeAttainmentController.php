<?php

namespace App\Http\Controllers;

use App\Models\CourseOutcomeAttainment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

use App\Traits\CourseOutcomeTrait;

class CourseOutcomeAttainmentController extends Controller
{
    use CourseOutcomeTrait;

    public function subject($subjectId)
    {
        // Get the selected subject with course and academicPeriod relationships
        $selectedSubject = \App\Models\Subject::with(['course', 'academicPeriod'])->findOrFail($subjectId);

        // Get students enrolled in the subject
        $students = \App\Models\Student::whereHas('subjects', function($q) use ($subjectId) {
            $q->where('subject_id', $subjectId);
        })->get();

        $studentIds = $students->pluck('id');

        // Terms
        $terms = ['prelim', 'midterm', 'prefinal', 'final'];

        // Load all activities once, then group by term for faster lookups
        $activities = \App\Models\Activity::where('subject_id', $subjectId)
            ->where('is_deleted', false)
            ->whereNotNull('course_outcome_id')
            ->get();

        // Load CO details for only the referenced, non-deleted COs
        $coDetails = \App\Models\CourseOutcomes::whereIn('id', $activities->pluck('course_outcome_id')->unique())
            ->where('is_deleted', false)
            ->get()
            ->keyBy('id');

        // Filter activities to those that have a valid CO record (avoids missing keys in the view/sort)
        $activitiesByTerm = $activities
            ->filter(fn($activity) => $coDetails->has($activity->course_outcome_id))
            ->groupBy('term');

        // Build term -> activity collections and CO columns per term
        $coColumnsByTerm = [];
        $activityCoMap = [];
        foreach ($terms as $term) {
            $termActivities = $activitiesByTerm->get($term, collect());
            $coColumnsByTerm[$term] = $termActivities->pluck('course_outcome_id')->unique()->values()->all();

            foreach ($termActivities as $activity) {
                $activityCoMap[$term][$activity->id] = $activity->course_outcome_id;
            }
        }

        $activityIds = $activitiesByTerm->flatten()->pluck('id');

        // Load scores in a single query and group for O(1) access when building the grid
        $scoresByStudentAndActivity = collect();
        if ($studentIds->isNotEmpty() && $activityIds->isNotEmpty()) {
            $scoresByStudentAndActivity = \App\Models\Score::whereIn('student_id', $studentIds)
                ->whereIn('activity_id', $activityIds)
                ->get()
                ->groupBy(['student_id', 'activity_id']);
        }

        // Build studentScores: [student_id => [term => [activity_id => ['score'=>, 'max'=>]]]]
        $studentScores = [];
        foreach ($students as $student) {
            foreach ($terms as $term) {
                foreach ($activitiesByTerm->get($term, collect()) as $activity) {
                    $score = optional(optional($scoresByStudentAndActivity->get($student->id))?->get($activity->id))->first();
                    $studentScores[$student->id][$term][$activity->id] = [
                        'score' => $score->score ?? 0,
                        'max' => $activity->number_of_items,
                    ];
                }
            }
        }

        // Compute CO attainment for each student
        $coResults = [];
        foreach ($students as $student) {
            $coResults[$student->id] = $this->computeCoAttainment($studentScores[$student->id] ?? [], $activityCoMap);
        }

        $allCoIds = collect($coColumnsByTerm)->flatten()->unique()->values();

        // Filter out soft-deleted COs from coColumnsByTerm
        foreach ($coColumnsByTerm as $term => $coIds) {
            $coColumnsByTerm[$term] = array_values(array_filter($coIds, function($coId) use ($coDetails) {
                return isset($coDetails[$coId]);
            }));
        }

        // Create properly sorted finalCOs for the combined table (only non-deleted COs)
        $finalCOs = array_values(array_unique(array_merge(...array_values($coColumnsByTerm))));
        
        // Sort finalCOs by co_code numerically (CO1, CO2, CO3, CO4)
        usort($finalCOs, function($a, $b) use ($coDetails) {
            $codeA = optional($coDetails->get($a))->co_code ?? '';
            $codeB = optional($coDetails->get($b))->co_code ?? '';
            
            // Extract numeric part from CO codes (CO1 -> 1, CO2 -> 2, etc.)
            $numA = (int)preg_replace('/[^0-9]/', '', $codeA);
            $numB = (int)preg_replace('/[^0-9]/', '', $codeB);
            
            return $numA <=> $numB; // Numeric comparison
        });

        // Reindex the array to ensure sequential indices (0, 1, 2, 3...)
        $finalCOs = array_values($finalCOs);

        return view('instructor.scores.course-outcome-results', [
            'students' => $students,
            'coResults' => $coResults,
            'coColumnsByTerm' => $coColumnsByTerm,
            'coDetails' => $coDetails,
            'finalCOs' => $finalCOs,
            'terms' => $terms,
            'subjectId' => $subjectId,
            'selectedSubject' => $selectedSubject,
        ]);
    }

    public function index(Request $request)
    {
        // Automatically use the active academic period from session
        $academicPeriodId = session('active_academic_period_id');
        
        if (!$academicPeriodId) {
            // If no active academic period is set, show empty state
            return view('instructor.scores.course-outcome-results-wildcards', [
                'subjects' => collect(),
                'academicYear' => null,
                'semester' => null,
            ]);
        }

        // Get the active academic period
        $period = \App\Models\AcademicPeriod::find($academicPeriodId);
        
        if (!$period) {
            // If period not found, show empty state
            return view('instructor.scores.course-outcome-results-wildcards', [
                'subjects' => collect(),
                'academicYear' => null,
                'semester' => null,
            ]);
        }

        // Get subjects for the current instructor in the active academic period
        $subjects = \App\Models\Subject::query()
            ->join('academic_periods', 'subjects.academic_period_id', '=', 'academic_periods.id')
            ->where(function($query) {
                $query->where('subjects.instructor_id', Auth::id())
                      ->orWhereHas('instructors', function($q) {
                          $q->where('instructor_id', Auth::id());
                      });
            })
            ->where('subjects.is_deleted', false)
            ->where('subjects.academic_period_id', $academicPeriodId)
            ->select('subjects.*', 'academic_periods.academic_year as academic_year', 'academic_periods.semester as semester')
            ->get();

        return view('instructor.scores.course-outcome-results-wildcards', [
            'subjects' => $subjects,
            'academicYear' => $period->academic_year,
            'semester' => $period->semester,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'student_id' => 'required|exists:students,id',
            'term' => 'required|string',
            'course_outcome_id' => 'required|exists:course_outcomes,id',
            'subject_id' => 'required|exists:subjects,id',
            'score' => 'required|integer',
            'max' => 'required|integer',
            'semester_total' => 'required|numeric',
        ]);
        $attainment = CourseOutcomeAttainment::create($data);
        return response()->json(['status' => 'success', 'attainment' => $attainment]);
    }

    public function show($id)
    {
        $attainment = CourseOutcomeAttainment::with(['student', 'courseOutcome'])->findOrFail($id);
        return response()->json($attainment);
    }

    public function update(Request $request, $id)
    {
        $attainment = CourseOutcomeAttainment::findOrFail($id);
        $data = $request->validate([
            'score' => 'integer',
            'max' => 'integer',
            'semester_total' => 'numeric',
        ]);
        $attainment->update($data);
        return response()->json(['status' => 'success', 'attainment' => $attainment]);
    }

    public function destroy($id)
    {
        $attainment = CourseOutcomeAttainment::findOrFail($id);
        $attainment->delete();
        return response()->json(['status' => 'deleted']);
    }
}
