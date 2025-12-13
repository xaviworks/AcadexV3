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

    /**
     * Display the course outcome attainment results for a subject.
     * Optimized for performance - all data is pre-computed in the controller.
     */
    public function subject($subjectId)
    {
        // Get the selected subject with course and academicPeriod relationships
        $selectedSubject = \App\Models\Subject::with(['course', 'academicPeriod'])->findOrFail($subjectId);

        // Get students enrolled in the subject - order by last_name, first_name for consistent display
        $students = \App\Models\Student::whereHas('subjects', function($q) use ($subjectId) {
            $q->where('subject_id', $subjectId);
        })
        ->where('is_deleted', false)
        ->orderBy('last_name')
        ->orderBy('first_name')
        ->get();

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
                ->where('is_deleted', false)
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

        // ============================================================
        // PRE-COMPUTE ALL DATA FOR VIEW (Eliminates N+1 queries)
        // ============================================================
        
        // Pre-compute max scores per CO per term
        $maxScoresByTermCo = [];
        foreach ($terms as $term) {
            $termActivities = $activitiesByTerm->get($term, collect());
            foreach ($coColumnsByTerm[$term] ?? [] as $coId) {
                $max = $termActivities
                    ->where('course_outcome_id', $coId)
                    ->sum('number_of_items');
                $maxScoresByTermCo[$term][$coId] = $max;
            }
        }
        
        // Pre-compute total max scores per CO (across all terms)
        $totalMaxByCoId = [];
        foreach ($finalCOs as $coId) {
            $total = 0;
            foreach ($terms as $term) {
                $total += $maxScoresByTermCo[$term][$coId] ?? 0;
            }
            $totalMaxByCoId[$coId] = $total;
        }
        
        // Pre-compute student scores per term per CO (for individual term views)
        $studentTermCoScores = [];
        foreach ($students as $student) {
            foreach ($terms as $term) {
                $termActivities = $activitiesByTerm->get($term, collect());
                foreach ($coColumnsByTerm[$term] ?? [] as $coId) {
                    $rawScore = 0;
                    $maxScore = 0;
                    
                    foreach ($termActivities->where('course_outcome_id', $coId) as $activity) {
                        $scoreRecord = optional(optional($scoresByStudentAndActivity->get($student->id))?->get($activity->id))->first();
                        $rawScore += $scoreRecord->score ?? 0;
                        $maxScore += $activity->number_of_items;
                    }
                    
                    $percent = $maxScore > 0 ? ($rawScore / $maxScore) * 100 : 0;
                    
                    $studentTermCoScores[$student->id][$term][$coId] = [
                        'raw' => $rawScore,
                        'max' => $maxScore,
                        'percent' => ceil($percent),
                    ];
                }
            }
        }
        
        // Pre-compute summary statistics per CO (all terms combined)
        $coSummaryStats = [];
        foreach ($finalCOs as $coId) {
            $attempted = 0;
            $passed = 0;
            $threshold = 75;
            
            foreach ($students as $student) {
                $raw = $coResults[$student->id]['semester_raw'][$coId] ?? null;
                $max = $coResults[$student->id]['semester_max'][$coId] ?? null;
                $percent = ($max > 0 && $raw !== null) ? ($raw / $max) * 100 : null;
                
                if ($percent !== null) {
                    $attempted++;
                    if ($percent > $threshold) {
                        $passed++;
                    }
                }
            }
            
            $coSummaryStats[$coId] = [
                'attempted' => $attempted,
                'passed' => $passed,
                'failed' => $attempted - $passed,
                'pass_percentage' => $attempted > 0 ? round(($passed / $attempted) * 100, 2) : 0,
                'fail_percentage' => $attempted > 0 ? round((($attempted - $passed) / $attempted) * 100, 1) : 0,
            ];
        }
        
        // Pre-compute summary statistics per term per CO
        $termCoSummaryStats = [];
        foreach ($terms as $term) {
            foreach ($coColumnsByTerm[$term] ?? [] as $coId) {
                $attempted = 0;
                $passed = 0;
                $threshold = 75;
                
                foreach ($students as $student) {
                    $data = $studentTermCoScores[$student->id][$term][$coId] ?? null;
                    if ($data && $data['max'] > 0) {
                        $attempted++;
                        if ($data['percent'] >= $threshold) {
                            $passed++;
                        }
                    }
                }
                
                $termCoSummaryStats[$term][$coId] = [
                    'attempted' => $attempted,
                    'passed' => $passed,
                    'failed' => $attempted - $passed,
                    'pass_percentage' => $attempted > 0 ? round(($passed / $attempted) * 100, 1) : 0,
                    'fail_percentage' => $attempted > 0 ? round((($attempted - $passed) / $attempted) * 100, 1) : 0,
                ];
            }
        }

        // Pre-compute incomplete COs in the controller (avoid N+1 queries in Blade)
        $incompleteCOs = [];
        $totalStudents = $students->count();
        
        foreach ($terms as $term) {
            $termCOs = $coColumnsByTerm[$term] ?? [];
            foreach ($termCOs as $coId) {
                // Get activities for this CO and term from already-loaded data
                $termActivities = $activitiesByTerm->get($term, collect())
                    ->filter(fn($a) => $a->course_outcome_id == $coId);
                
                if ($termActivities->isEmpty()) continue;
                
                $totalMissingScores = 0;
                $activityCount = $termActivities->count();
                
                foreach ($students as $student) {
                    foreach ($termActivities as $activity) {
                        // Check score from already-loaded data (O(1) lookup)
                        $scoreRecord = optional(optional($scoresByStudentAndActivity->get($student->id))?->get($activity->id))->first();
                        
                        // Flag as incomplete if no score record exists or score is null
                        if (!$scoreRecord || $scoreRecord->score === null) {
                            $totalMissingScores++;
                        }
                    }
                }
                
                if ($totalMissingScores > 0) {
                    $coDetail = $coDetails->get($coId);
                    $totalPossible = $totalStudents * $activityCount;
                    
                    $incompleteCOs[] = [
                        'co_id' => $coId,
                        'co_code' => $coDetail ? $coDetail->co_code : 'CO' . $coId,
                        'term' => $term,
                        'missing_scores' => $totalMissingScores,
                        'total_possible' => $totalPossible,
                        'percentage_incomplete' => $totalPossible > 0 ? round(($totalMissingScores / $totalPossible) * 100, 1) : 0
                    ];
                }
            }
        }

        return view('instructor.scores.course-outcome-results', [
            'students' => $students,
            'coResults' => $coResults,
            'coColumnsByTerm' => $coColumnsByTerm,
            'coDetails' => $coDetails,
            'finalCOs' => $finalCOs,
            'terms' => $terms,
            'subjectId' => $subjectId,
            'selectedSubject' => $selectedSubject,
            'incompleteCOs' => $incompleteCOs,
            // Pre-computed data for performance
            'maxScoresByTermCo' => $maxScoresByTermCo,
            'totalMaxByCoId' => $totalMaxByCoId,
            'studentTermCoScores' => $studentTermCoScores,
            'coSummaryStats' => $coSummaryStats,
            'termCoSummaryStats' => $termCoSummaryStats,
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
