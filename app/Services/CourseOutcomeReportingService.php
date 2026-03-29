<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\Course;
use App\Models\CourseOutcomes;
use App\Models\ProgramLearningOutcome;
use App\Models\ProgramLearningOutcomeMapping;
use App\Models\Score;
use App\Models\Student;
use App\Models\Subject;
use App\Support\Organization\GEContext;
use Illuminate\Support\Collection;
use App\Traits\CourseOutcomeTrait;

/**
 * Aggregates Course Outcome (CO) attainment across different scopes.
 *
 * Contract
 * - Inputs: subject IDs, course ID, or department ID with active academic period
 * - Output: associative arrays keyed by CO code number (1..6) with raw, max, percent, target_percentage
 * - Missing scores count as 0 while the max still counts (consistent with computeCoAttainment)
 */
class CourseOutcomeReportingService
{
    use CourseOutcomeTrait;

    public const DEFAULT_PLO_COUNT = 5;
    public const MAX_PLO_COUNT = 20;
    /**
     * Compute CO attainment for a single student in a specific subject across terms.
     * Returns structure compatible with computeCoAttainment plus term/column helpers.
     */
    public function computeStudentSubject(int $studentId, int $subjectId): array
    {
        $terms = ['prelim', 'midterm', 'prefinal', 'final'];

        // Activities grouped by term with COs
        $activitiesByTerm = [];
        $coColumnsByTerm = [];
        foreach ($terms as $term) {
            $activities = Activity::where('subject_id', $subjectId)
                ->where('term', $term)
                ->where('is_deleted', false)
                ->whereNotNull('course_outcome_id')
                ->get();
            $activitiesByTerm[$term] = $activities;

            $coIds = $activities->pluck('course_outcome_id')->unique()->toArray();
            if (!empty($coIds)) {
                $sortedCos = CourseOutcomes::whereIn('id', $coIds)
                    ->where('is_deleted', false)
                    ->orderBy('co_code')
                    ->pluck('id')
                    ->toArray();
                $coColumnsByTerm[$term] = $sortedCos;
            } else {
                $coColumnsByTerm[$term] = [];
            }
        }

        // Map activity->CO per term
        $activityCoMap = [];
        foreach ($activitiesByTerm as $term => $activities) {
            foreach ($activities as $activity) {
                $activityCoMap[$term][$activity->id] = $activity->course_outcome_id;
            }
        }

        // Build scores for this student only
        $studentScores = [];
        foreach ($activitiesByTerm as $term => $activities) {
            foreach ($activities as $activity) {
                $score = Score::where('student_id', $studentId)
                    ->where('activity_id', $activity->id)
                    ->where('is_deleted', false)
                    ->first();
                $studentScores[$term][$activity->id] = [
                    'score' => $score ? (int)$score->score : 0,
                    'max' => (int)$activity->number_of_items,
                ];
            }
        }

        $coAttainment = $this->computeCoAttainment($studentScores, $activityCoMap);

        // CO details for display - get unique CO IDs across all terms
        $finalCOs = array_unique(array_merge(...array_values($coColumnsByTerm ?: [[]])));
        $coDetails = empty($finalCOs)
            ? collect()
            : CourseOutcomes::whereIn('id', $finalCOs)->where('is_deleted', false)->get()->keyBy('id');

        // Sort final COs by numeric code order and map IDs to codes
        usort($finalCOs, function($a, $b) use ($coDetails) {
            $codeA = $coDetails[$a]->co_code ?? '';
            $codeB = $coDetails[$b]->co_code ?? '';
            $numA = (int)preg_replace('/[^0-9]/', '', $codeA);
            $numB = (int)preg_replace('/[^0-9]/', '', $codeB);
            return $numA <=> $numB;
        });
        
        // Map CO IDs to CO codes for display
        $coCodesForHeader = [];
        foreach ($finalCOs as $coId) {
            if (isset($coDetails[$coId])) {
                $coCodesForHeader[] = $coDetails[$coId]->co_code;
            }
        }

        // Transform coResults to use CO codes instead of IDs
        // Structure: [term][coCode] => ['raw' => int, 'max' => int, 'percent' => float, 'target_percentage' => int]
        $coResultsByCode = [];
        foreach ($terms as $term) {
            foreach ($finalCOs as $coId) {
                if (isset($coDetails[$coId])) {
                    $coCode = $coDetails[$coId]->co_code;
                    $percent = $coAttainment['per_term'][$term][$coId] ?? 0;
                    
                    // Get raw scores from the original data
                    $rawScore = 0;
                    $maxScore = 0;
                    if (isset($studentScores[$term])) {
                        foreach ($studentScores[$term] as $activityId => $scoreData) {
                            if (($activityCoMap[$term][$activityId] ?? null) == $coId) {
                                $rawScore += $scoreData['score'];
                                $maxScore += $scoreData['max'];
                            }
                        }
                    }
                    
                    if ($maxScore > 0) {
                        $coResultsByCode[$term][$coCode] = [
                            'raw' => $rawScore,
                            'max' => $maxScore,
                            'percent' => $percent,
                            'target_percentage' => (int) ($coDetails[$coId]->target_percentage ?? 75),
                        ];
                    }
                }
            }
        }
        
        // Add final/overall row with semester totals
        $finalCOs_data = [];
        foreach ($finalCOs as $coId) {
            if (isset($coDetails[$coId])) {
                $coCode = $coDetails[$coId]->co_code;
                $finalCOs_data[$coCode] = [
                    'raw' => $coAttainment['semester_raw'][$coId] ?? 0,
                    'max' => $coAttainment['semester_max'][$coId] ?? 0,
                    'percent' => $coAttainment['semester_total'][$coId] ?? 0,
                    'target_percentage' => (int) ($coDetails[$coId]->target_percentage ?? 75),
                ];
            }
        }

        return [
            'terms' => $terms,
            'coColumnsByTerm' => $coCodesForHeader,
            'coDetails' => $coDetails,
            'finalCOs' => $finalCOs_data,
            'coResults' => $coResultsByCode,
        ];
    }
    /**
     * Compute aggregated CO attainment for a single subject across all enrolled students and terms.
    * Returns: [co_code_number => ['raw' => int, 'max' => int, 'percent' => float, 'target_percentage' => int]]
     */
    public function aggregateSubject(int $subjectId): array
    {
        $subject = Subject::with('course')->findOrFail($subjectId);

        // Enrolled students for the subject (pivot student_subjects)
        $students = Student::whereHas('subjects', function ($q) use ($subjectId) {
            $q->where('subject_id', $subjectId)->where('student_subjects.is_deleted', false);
        })->where('students.is_deleted', false)->get();

        $studentCount = $students->count();
        if ($studentCount === 0) {
            return [];
        }

        $coTotals = [];
        $coTargets = [];

        // Get all activities for this subject mapped to COs
        $activities = Activity::where('subject_id', $subjectId)
            ->where('is_deleted', false)
            ->whereNotNull('course_outcome_id')
            ->get();

        if ($activities->isEmpty()) {
            return [];
        }

        // Prefetch scores for all involved activities and students to minimize queries
        $activityIds = $activities->pluck('id')->all();
        $scores = Score::whereIn('activity_id', $activityIds)
            ->whereIn('student_id', $students->pluck('id'))
            ->get()
            ->groupBy(function ($s) { return $s->activity_id.'::'.$s->student_id; });

        foreach ($activities as $activity) {
            // Determine CO number from the course_outcomes.co_code (e.g., CO1 -> 1)
            $coId = $activity->course_outcome_id;
            $co = CourseOutcomes::find($coId);
            if (!$co || $co->is_deleted) {
                continue;
            }
            $coNum = (int)preg_replace('/[^0-9]/', '', (string)$co->co_code);
            if ($coNum <= 0) {
                // Fallback by position if code missing
                $coNum = 0;
            }

            if (!isset($coTotals[$coNum])) {
                $coTotals[$coNum] = ['raw' => 0, 'max' => 0];
            }

            if (!isset($coTargets[$coNum])) {
                $coTargets[$coNum] = (int) ($co->target_percentage ?? 75);
            }

            // For each enrolled student, add score (0 if missing) and add max (number_of_items)
            foreach ($students as $student) {
                $key = $activity->id.'::'.$student->id;
                $score = $scores->get($key)?->first();
                $coTotals[$coNum]['raw'] += $score ? (int)$score->score : 0;
                $coTotals[$coNum]['max'] += (int)$activity->number_of_items;
            }
        }

        // Compute final percentages
        $result = [];
        foreach ($coTotals as $num => $totals) {
            $percent = $totals['max'] > 0 ? round(($totals['raw'] / $totals['max']) * 100, 2) : 0.0;
            $result[$num] = [
                'raw' => $totals['raw'],
                'max' => $totals['max'],
                'percent' => $percent,
                'target_percentage' => $coTargets[$num] ?? 75,
            ];
        }

        ksort($result);
        return $result;
    }

    /**
     * Aggregate CO attainment for a course (program) across all its subjects in a given academic period.
     * Returns same structure as aggregateSubject but merged across subjects.
     * 
     * @param int $courseId The course ID to aggregate
     * @param int|null $academicPeriodId Optional academic period filter
     * @param bool $excludeGE If true, excludes GE subjects (department_id = 1)
     */
    public function aggregateCourse(int $courseId, ?int $academicPeriodId = null, bool $excludeGE = false): array
    {
        $subjects = Subject::where('course_id', $courseId)
            ->where('is_deleted', false)
            ->when($excludeGE, fn ($q) => $q->notManagedByGE())
            ->when($academicPeriodId, function ($q) use ($academicPeriodId) {
                $q->where('academic_period_id', $academicPeriodId);
            })
            ->pluck('id')
            ->all();

        return $this->aggregateSubjects($subjects);
    }

    /**
     * Aggregate CO attainment across multiple subjects by merging totals per CO number.
     * Optimized to reduce database queries.
     */
    public function aggregateSubjects(array $subjectIds): array
    {
        if (empty($subjectIds)) {
            return [];
        }

        $merged = [];
        $coTargets = [];

        // Optimized: Batch fetch all data needed for all subjects at once
        // Get all activities with their course outcomes
        $activities = Activity::whereIn('subject_id', $subjectIds)
            ->where('is_deleted', false)
            ->whereNotNull('course_outcome_id')
            ->with(['courseOutcome' => function ($query) {
                $query->select('id', 'co_code', 'is_deleted', 'target_percentage');
            }])
            ->select('id', 'subject_id', 'course_outcome_id', 'number_of_items')
            ->get();

        if ($activities->isEmpty()) {
            return [];
        }

        // Build a per-subject enrollment map so each activity only counts
        // students actually enrolled in that specific subject.
        $subjectStudentMap = \DB::table('student_subjects')
            ->join('students', 'students.id', '=', 'student_subjects.student_id')
            ->whereIn('student_subjects.subject_id', $subjectIds)
            ->where('student_subjects.is_deleted', false)
            ->where('students.is_deleted', false)
            ->select('student_subjects.subject_id', 'student_subjects.student_id')
            ->get()
            ->groupBy('subject_id')
            ->map(fn ($rows) => $rows->pluck('student_id')->unique()->values()->all());

        if ($subjectStudentMap->isEmpty()) {
            return [];
        }

        $studentIds = $subjectStudentMap->flatten()->unique()->values()->all();

        // Prefetch all scores for these activities and students
        $activityIds = $activities->pluck('id')->all();

        $scores = Score::whereIn('activity_id', $activityIds)
            ->whereIn('student_id', $studentIds)
            ->select('activity_id', 'student_id', 'score')
            ->get()
            ->groupBy(function ($s) { return $s->activity_id.'::'.$s->student_id; });

        // Process each activity
        foreach ($activities as $activity) {
            $co = $activity->courseOutcome;
            if (!$co || $co->is_deleted) {
                continue;
            }
            
            $coNum = (int)preg_replace('/[^0-9]/', '', (string)$co->co_code);
            if ($coNum <= 0) {
                $coNum = 0;
            }

            if (!isset($merged[$coNum])) {
                $merged[$coNum] = ['raw' => 0, 'max' => 0];
            }

            if (!isset($coTargets[$coNum])) {
                $coTargets[$coNum] = (int) ($co->target_percentage ?? 75);
            }

            $enrolledStudentIds = $subjectStudentMap->get($activity->subject_id, []);

            // For each student enrolled in this activity's subject, add score and max
            foreach ($enrolledStudentIds as $studentId) {
                $key = $activity->id.'::'.$studentId;
                $score = $scores->get($key)?->first();
                $merged[$coNum]['raw'] += $score ? (int)$score->score : 0;
                $merged[$coNum]['max'] += (int)$activity->number_of_items;
            }
        }

        $result = [];
        foreach ($merged as $num => $totals) {
            $percent = $totals['max'] > 0 ? round(($totals['raw'] / $totals['max']) * 100, 2) : 0.0;
            $result[$num] = [
                'raw' => $totals['raw'],
                'max' => $totals['max'],
                'percent' => $percent,
                'target_percentage' => $coTargets[$num] ?? 75,
            ];
        }

        ksort($result);
        return $result;
    }

    /**
     * Aggregate per course within a department for a given period.
     * Returns: [course_id => ['course' => Course, 'co' => [num => ['raw','max','percent']]]]
     */
    public function aggregateDepartmentByCourse(int $departmentId, ?int $academicPeriodId = null): array
    {
        // Optimized: Eager load courses with their subjects to avoid N+1 queries
        $courses = Course::where('department_id', $departmentId)
            ->where('is_deleted', false)
            ->select('id', 'course_code', 'course_description', 'department_id')
            ->with(['subjects' => function ($query) use ($academicPeriodId) {
                $query->where('is_deleted', false)
                    ->when($academicPeriodId, function ($q) use ($academicPeriodId) {
                        $q->where('academic_period_id', $academicPeriodId);
                    })
                    ->select('id', 'course_id', 'subject_code', 'academic_period_id');
            }])
            ->get();

        $out = [];
        foreach ($courses as $course) {
            // Use the eager-loaded subjects instead of making separate queries
            $subjectIds = $course->subjects->pluck('id')->all();
            $out[$course->id] = [
                'course' => $course,
                'co' => $this->aggregateSubjects($subjectIds),
            ];
        }
        return $out;
    }

    /**
     * Aggregate CO attainment for GE subjects across all courses/programs.
     * For each program, only count GE subjects taken by students from that program.
     * Returns: [course_id => ['course' => Course, 'co' => [num => ['raw','max','percent']]]]
     */
    public function aggregateGESubjectsAcrossCourses(?int $academicPeriodId = null): array
    {
        // Get all courses (programs)
        $courses = Course::where('is_deleted', false)->get();

        // Get all GE-managed subjects for the period.
        $geSubjects = Subject::query()
            ->managedByGE()
            ->where('is_deleted', false)
            ->when($academicPeriodId, function ($q) use ($academicPeriodId) {
                $q->where('academic_period_id', $academicPeriodId);
            })
            ->get();

        $out = [];
        foreach ($courses as $course) {
            // For each course, we need to calculate CO for GE subjects taken by students from this course
            $coTotals = [];

            // Get students from this course/program
            $studentsInCourse = Student::where('course_id', $course->id)
                ->where('is_deleted', false)
                ->pluck('id')
                ->all();

            if (empty($studentsInCourse)) {
                continue; // Skip courses with no students
            }

            // For each GE subject, calculate CO attainment for students from this course
            foreach ($geSubjects as $subject) {
                // Get students from this course who are enrolled in this GE subject
                $enrolledStudents = Student::where('course_id', $course->id)
                    ->where('students.is_deleted', false)
                    ->whereHas('subjects', function ($q) use ($subject) {
                        $q->where('subject_id', $subject->id)
                          ->where('student_subjects.is_deleted', false);
                    })
                    ->pluck('students.id')
                    ->all();

                if (empty($enrolledStudents)) {
                    continue; // No students from this course in this GE subject
                }

                // Get activities for this subject with COs
                $activities = Activity::where('subject_id', $subject->id)
                    ->where('is_deleted', false)
                    ->whereNotNull('course_outcome_id')
                    ->get();

                if ($activities->isEmpty()) {
                    continue;
                }

                // Prefetch scores for these specific students and activities
                $activityIds = $activities->pluck('id')->all();
                $scores = Score::whereIn('activity_id', $activityIds)
                    ->whereIn('student_id', $enrolledStudents)
                    ->where('is_deleted', false)
                    ->get()
                    ->groupBy(function ($s) { return $s->activity_id.'::'.$s->student_id; });

                // Aggregate CO data
                foreach ($activities as $activity) {
                    $coId = $activity->course_outcome_id;
                    $co = CourseOutcomes::find($coId);
                    if (!$co || $co->is_deleted) {
                        continue;
                    }
                    $coNum = (int)preg_replace('/[^0-9]/', '', (string)$co->co_code);
                    if ($coNum <= 0) {
                        continue;
                    }

                    if (!isset($coTotals[$coNum])) {
                        $coTotals[$coNum] = ['raw' => 0, 'max' => 0];
                    }

                    // For each enrolled student from this course, add score and max
                    foreach ($enrolledStudents as $studentId) {
                        $key = $activity->id.'::'.$studentId;
                        $score = $scores->get($key)?->first();
                        $coTotals[$coNum]['raw'] += $score ? (int)$score->score : 0;
                        $coTotals[$coNum]['max'] += (int)$activity->number_of_items;
                    }
                }
            }

            // Compute final percentages for this course
            if (!empty($coTotals)) {
                $result = [];
                foreach ($coTotals as $num => $totals) {
                    $percent = $totals['max'] > 0 ? round(($totals['raw'] / $totals['max']) * 100, 2) : 0.0;
                    $result[$num] = [
                        'raw' => $totals['raw'],
                        'max' => $totals['max'],
                        'percent' => $percent,
                    ];
                }
                ksort($result);

                $out[$course->id] = [
                    'course' => $course,
                    'co' => $result,
                ];
            }
        }

        return $out;
    }

    /**
     * Ensure the program has a default PLO scaffold the first time the report is opened.
     */
    public function ensureDefaultProgramLearningOutcomes(int $courseId): Collection
    {
        $existing = ProgramLearningOutcome::where('course_id', $courseId)
            ->where('is_deleted', false)
            ->orderBy('display_order')
            ->get();

        if ($existing->isNotEmpty()) {
            return $existing;
        }

        $defaults = collect();
        for ($i = 1; $i <= self::DEFAULT_PLO_COUNT; $i++) {
            $defaults->push(ProgramLearningOutcome::create([
                'course_id' => $courseId,
                'plo_code' => 'PLO' . $i,
                'title' => 'Program Learning Outcome ' . $i,
                'display_order' => $i,
                'is_active' => true,
                'is_deleted' => false,
            ]));
        }

        return $defaults;
    }

    /**
     * Get ordered PLO definitions for a program.
     */
    public function getProgramLearningOutcomes(int $courseId, bool $activeOnly = false): Collection
    {
        $query = ProgramLearningOutcome::where('course_id', $courseId)
            ->where('is_deleted', false)
            ->orderBy('display_order')
            ->orderBy('id');

        if ($activeOnly) {
            $query->where('is_active', true);
        }

        return $query->get();
    }

    /**
     * Build PLO report data from existing CO aggregates.
     */
    public function aggregateProgramLearningOutcomes(
        int $courseId,
        ?int $academicPeriodId = null,
        bool $excludeGE = false
    ): array {
        $allPlos = $this->ensureDefaultProgramLearningOutcomes($courseId);
        $activePlos = $allPlos->where('is_active', true)
            ->sortBy('display_order')
            ->values();

        $coAggregates = $this->aggregateCourse($courseId, $academicPeriodId, $excludeGE);
        $mappings = ProgramLearningOutcomeMapping::where('course_id', $courseId)
            ->whereIn('program_learning_outcome_id', $allPlos->pluck('id'))
            ->get()
            ->groupBy('program_learning_outcome_id');

        $results = [];

        foreach ($activePlos as $plo) {
            $mappedCoCodes = $mappings->get($plo->id, collect())
                ->pluck('co_code')
                ->unique()
                ->sortBy(fn ($code) => $this->extractOutcomeNumber($code))
                ->values();

            $mappedMetrics = $mappedCoCodes
                ->map(function ($code) use ($coAggregates) {
                    $coNumber = $this->extractOutcomeNumber($code);
                    return $coAggregates[$coNumber] ?? null;
                })
                ->filter();

            if ($mappedMetrics->isEmpty()) {
                $results[$plo->id] = null;
                continue;
            }

            $metrics = $this->computeProgramLearningOutcomeMetrics($mappedMetrics);
            $metrics['co_codes'] = $mappedCoCodes->all();
            $metrics['level'] = $this->resolveAttainmentLevel($metrics['percent']);
            $results[$plo->id] = $metrics;
        }

        return [
            'definitions' => $allPlos->values(),
            'activeDefinitions' => $activePlos,
            'mappings' => $mappings->map(fn ($items) => $items->pluck('co_code')->unique()->values()->all())->all(),
            'availableCoCodes' => $this->getAvailableCoCodes($courseId, $academicPeriodId, $excludeGE),
            'coAggregates' => $coAggregates,
            'results' => $results,
        ];
    }

    public function getAvailableCoCodes(
        int $courseId,
        ?int $academicPeriodId = null,
        bool $excludeGE = false
    ): array {
        $coCodes = CourseOutcomes::query()
            ->select('course_outcomes.co_code')
            ->join('subjects', 'subjects.id', '=', 'course_outcomes.subject_id')
            ->where('course_outcomes.is_deleted', false)
            ->where('subjects.is_deleted', false)
            ->where('subjects.course_id', $courseId)
            ->when($excludeGE, function ($query) {
                $geDepartmentId = GEContext::geDepartmentId();
                $geCourseId = GEContext::geCourseId();

                $query->where('subjects.is_universal', false);

                if ($geDepartmentId !== null) {
                    $query->where('subjects.department_id', '!=', $geDepartmentId);
                }

                if ($geCourseId !== null) {
                    $query->where('subjects.course_id', '!=', $geCourseId);
                }
            })
            ->when($academicPeriodId, function ($query) use ($academicPeriodId) {
                $query->where('subjects.academic_period_id', $academicPeriodId);
            })
            ->pluck('course_outcomes.co_code')
            ->filter()
            ->unique()
            ->sortBy(fn ($code) => $this->extractOutcomeNumber($code))
            ->values()
            ->all();

        return $coCodes;
    }

    private function computeProgramLearningOutcomeMetrics(Collection $mappedMetrics): array
    {
        return [
            'percent' => round($mappedMetrics->avg('percent'), 2),
            'target_percentage' => round($mappedMetrics->avg('target_percentage'), 2),
        ];
    }

    private function resolveAttainmentLevel(float $percent): array
    {
        if ($percent >= 85) {
            return [
                'label' => 'Exceeded Expected Outcome',
                'tone' => 'success',
            ];
        }

        if ($percent >= 70) {
            return [
                'label' => 'Met Expected Outcome',
                'tone' => 'warning',
            ];
        }

        return [
            'label' => 'Needs Improvement',
            'tone' => 'danger',
        ];
    }

    private function extractOutcomeNumber(string $code): int
    {
        return (int) preg_replace('/[^0-9]/', '', $code);
    }
}
