<?php

namespace App\Traits;

use App\Models\Activity;
use App\Models\FinalGrade;
use App\Models\Score;
use App\Models\Student;
use App\Models\Subject;
use App\Models\TermGrade;
use App\Services\CacheService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Trait for optimized database queries in controllers.
 * 
 * This trait provides reusable methods for:
 * - Eager loading relationships to prevent N+1 queries
 * - Bulk data fetching with proper indexing usage
 * - Cached reference data access
 * 
 * Usage:
 *   use QueryOptimizationTrait;
 */
trait QueryOptimizationTrait
{
    // =========================================================================
    // Cached Reference Data
    // =========================================================================

    /**
     * Get all departments (cached).
     */
    protected function getCachedDepartments(bool $activeOnly = true): Collection
    {
        return CacheService::departments($activeOnly);
    }

    /**
     * Get all courses (cached).
     */
    protected function getCachedCourses(bool $activeOnly = true, ?int $departmentId = null): Collection
    {
        return CacheService::courses($activeOnly, $departmentId);
    }

    /**
     * Get all academic periods (cached).
     */
    protected function getCachedAcademicPeriods(bool $activeOnly = true): Collection
    {
        return CacheService::academicPeriods($activeOnly);
    }

    /**
     * Get term ID by name (cached).
     */
    protected function getCachedTermId(string $termName): ?int
    {
        return CacheService::termId($termName);
    }

    // =========================================================================
    // Subject Queries with Eager Loading
    // =========================================================================

    /**
     * Get subjects for an instructor with eager-loaded relationships.
     * 
     * @param int $instructorId
     * @param int|null $academicPeriodId
     * @param array $withRelations Additional relationships to load
     * @return Collection<Subject>
     */
    protected function getInstructorSubjects(
        int $instructorId, 
        ?int $academicPeriodId = null,
        array $withRelations = ['department', 'course']
    ): Collection {
        return Subject::query()
            ->where(function ($query) use ($instructorId) {
                $query->where('instructor_id', $instructorId)
                    ->orWhereHas('instructors', fn($q) => $q->where('instructor_id', $instructorId));
            })
            ->when($academicPeriodId, fn($q) => $q->where('academic_period_id', $academicPeriodId))
            ->where('is_deleted', false)
            ->with($withRelations)
            ->withCount('students')
            ->get();
    }

    /**
     * Get subjects with grade status computed efficiently.
     * Replaces N+1 pattern of counting graded students per subject.
     * 
     * @param Collection<Subject> $subjects
     * @return Collection<Subject>
     */
    protected function attachGradeStatus(Collection $subjects): Collection
    {
        if ($subjects->isEmpty()) {
            return $subjects;
        }

        $subjectIds = $subjects->pluck('id');
        $terms = ['prelim', 'midterm', 'prefinal', 'final'];
        
        // Get graded counts for all subjects and terms in one query per term
        $gradedByTerm = [];
        foreach ($terms as $term) {
            $termId = $this->getCachedTermId($term);
            if ($termId) {
                $gradedByTerm[$term] = TermGrade::getGradedCountsBySubject($subjectIds, $termId);
            }
        }

        // Attach status to each subject
        foreach ($subjects as $subject) {
            $totalStudents = $subject->students_count ?? 0;
            $completedTerms = 0;

            foreach ($terms as $term) {
                $gradedCount = $gradedByTerm[$term][$subject->id] ?? 0;
                if ($totalStudents > 0 && $gradedCount >= $totalStudents) {
                    $completedTerms++;
                }
            }

            $subject->grade_status = match (true) {
                $totalStudents === 0 => 'not_started',
                $completedTerms === 0 => 'pending',
                $completedTerms < count($terms) => 'pending',
                default => 'completed',
            };
        }

        return $subjects;
    }

    // =========================================================================
    // Student Queries with Eager Loading
    // =========================================================================

    /**
     * Get students for a subject with optimized eager loading.
     * 
     * @param int $subjectId
     * @param array $withRelations
     * @return Collection<Student>
     */
    protected function getStudentsForSubject(int $subjectId, array $withRelations = []): Collection
    {
        return Student::query()
            ->whereHas('subjects', fn($q) => $q->where('subject_id', $subjectId))
            ->where('is_deleted', false)
            ->when(!empty($withRelations), fn($q) => $q->with($withRelations))
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();
    }

    // =========================================================================
    // Score & Grade Bulk Loading
    // =========================================================================

    /**
     * Get scores for students and activities in bulk.
     * Returns nested collection: [student_id => [activity_id => Score]]
     * 
     * @param Collection<Student>|array $students
     * @param Collection<Activity>|array $activities
     * @return Collection
     */
    protected function getBulkScores(Collection|array $students, Collection|array $activities): Collection
    {
        $studentIds = $students instanceof Collection 
            ? $students->pluck('id') 
            : collect($students)->pluck('id');
            
        $activityIds = $activities instanceof Collection 
            ? $activities->pluck('id') 
            : collect($activities)->pluck('id');

        return Score::getBulkScores($studentIds, $activityIds);
    }

    /**
     * Get term grades for a subject grouped by term.
     * Returns: [term_id => [student_id => TermGrade]]
     * 
     * @param int $subjectId
     * @return Collection
     */
    protected function getTermGradesForSubject(int $subjectId): Collection
    {
        return TermGrade::getAllTermsForSubject($subjectId);
    }

    /**
     * Get all term grades for specific students in a subject.
     * Optimized for final grade calculation.
     * 
     * @param int $subjectId
     * @param array $termNames ['prelim', 'midterm', 'prefinal', 'final']
     * @return array [term_name => Collection<TermGrade> keyed by student_id]
     */
    protected function getTermGradesByTermName(int $subjectId, array $termNames = ['prelim', 'midterm', 'prefinal', 'final']): array
    {
        $result = [];
        
        foreach ($termNames as $term) {
            $termId = $this->getCachedTermId($term);
            if ($termId) {
                $result[$term] = TermGrade::getForSubjectTerm($subjectId, $termId);
            } else {
                $result[$term] = collect();
            }
        }

        return $result;
    }

    // =========================================================================
    // Activity Queries
    // =========================================================================

    /**
     * Get activities for a subject and term with eager loading.
     * 
     * @param int $subjectId
     * @param string $term
     * @param array $withRelations
     * @return Collection<Activity>
     */
    protected function getActivitiesForSubjectTerm(
        int $subjectId, 
        string $term, 
        array $withRelations = ['courseOutcome']
    ): Collection {
        return Activity::query()
            ->where('subject_id', $subjectId)
            ->where('term', $term)
            ->where('is_deleted', false)
            ->when(!empty($withRelations), fn($q) => $q->with($withRelations))
            ->orderBy('type')
            ->orderBy('created_at')
            ->get();
    }

    // =========================================================================
    // Final Grade Queries
    // =========================================================================

    /**
     * Get final grades for a subject keyed by student.
     * 
     * @param int $subjectId
     * @return Collection keyed by student_id
     */
    protected function getFinalGradesForSubject(int $subjectId): Collection
    {
        return FinalGrade::getForSubjectByStudent($subjectId);
    }

    /**
     * Get final grade statistics for subjects.
     * 
     * @param Collection|array $subjectIds
     * @param int|null $academicPeriodId
     * @return Collection
     */
    protected function getFinalGradeStats(Collection|array $subjectIds, ?int $academicPeriodId = null): Collection
    {
        return FinalGrade::getStatsBySubject($subjectIds, $academicPeriodId);
    }
}
