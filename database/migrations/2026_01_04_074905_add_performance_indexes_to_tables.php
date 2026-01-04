<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Performance indexes migration for ACADEX database optimization.
 * 
 * This migration adds composite indexes to improve query performance
 * for frequently accessed tables and common query patterns.
 * 
 * Safe rollback: All indexes can be dropped without data loss.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Scores table - frequently queried by student+activity combo
        Schema::table('scores', function (Blueprint $table) {
            // Composite index for student-activity lookups (most common query)
            if (!$this->indexExists('scores', 'idx_scores_student_activity')) {
                $table->index(['student_id', 'activity_id'], 'idx_scores_student_activity');
            }
            
            // Index for filtering by deletion status
            if (!$this->indexExists('scores', 'idx_scores_is_deleted')) {
                $table->index('is_deleted', 'idx_scores_is_deleted');
            }
        });

        // Term grades table - queried by subject+term combinations
        Schema::table('term_grades', function (Blueprint $table) {
            // Composite index for subject-term queries
            if (!$this->indexExists('term_grades', 'idx_term_grades_subject_term')) {
                $table->index(['subject_id', 'term_id'], 'idx_term_grades_subject_term');
            }
            
            // Composite index for student-subject lookups
            if (!$this->indexExists('term_grades', 'idx_term_grades_student_subject')) {
                $table->index(['student_id', 'subject_id'], 'idx_term_grades_student_subject');
            }
            
            // Index for filtering by deletion status
            if (!$this->indexExists('term_grades', 'idx_term_grades_is_deleted')) {
                $table->index('is_deleted', 'idx_term_grades_is_deleted');
            }
        });

        // Final grades table - queried by student+subject combinations
        Schema::table('final_grades', function (Blueprint $table) {
            // Composite index for student-subject lookups
            if (!$this->indexExists('final_grades', 'idx_final_grades_student_subject')) {
                $table->index(['student_id', 'subject_id'], 'idx_final_grades_student_subject');
            }
            
            // Index for academic period filtering
            if (!$this->indexExists('final_grades', 'idx_final_grades_academic_period')) {
                $table->index('academic_period_id', 'idx_final_grades_academic_period');
            }
            
            // Index for filtering by deletion status
            if (!$this->indexExists('final_grades', 'idx_final_grades_is_deleted')) {
                $table->index('is_deleted', 'idx_final_grades_is_deleted');
            }
        });

        // Activities table - frequently filtered by subject and term
        Schema::table('activities', function (Blueprint $table) {
            // Composite index for subject-term queries
            if (!$this->indexExists('activities', 'idx_activities_subject_term')) {
                $table->index(['subject_id', 'term'], 'idx_activities_subject_term');
            }
            
            // Composite index for subject-type queries
            if (!$this->indexExists('activities', 'idx_activities_subject_type')) {
                $table->index(['subject_id', 'type'], 'idx_activities_subject_type');
            }
            
            // Index for filtering by deletion status
            if (!$this->indexExists('activities', 'idx_activities_is_deleted')) {
                $table->index('is_deleted', 'idx_activities_is_deleted');
            }
        });

        // Students table - filtered by course, department, year
        Schema::table('students', function (Blueprint $table) {
            // Composite index for course-year queries
            if (!$this->indexExists('students', 'idx_students_course_year')) {
                $table->index(['course_id', 'year_level'], 'idx_students_course_year');
            }
            
            // Index for filtering by deletion status
            if (!$this->indexExists('students', 'idx_students_is_deleted')) {
                $table->index('is_deleted', 'idx_students_is_deleted');
            }
            
            // Index for name searches (last_name is most commonly searched)
            if (!$this->indexExists('students', 'idx_students_last_name')) {
                $table->index('last_name', 'idx_students_last_name');
            }
        });

        // Student subjects - pivot table frequently joined
        Schema::table('student_subjects', function (Blueprint $table) {
            // Composite unique index for the relationship
            if (!$this->indexExists('student_subjects', 'idx_student_subjects_unique')) {
                $table->unique(['student_id', 'subject_id'], 'idx_student_subjects_unique');
            }
            
            // Index for filtering by deletion status
            if (!$this->indexExists('student_subjects', 'idx_student_subjects_is_deleted')) {
                $table->index('is_deleted', 'idx_student_subjects_is_deleted');
            }
        });

        // User logs - queried by user and event type
        Schema::table('user_logs', function (Blueprint $table) {
            // Composite index for user-event queries
            if (!$this->indexExists('user_logs', 'idx_user_logs_user_event')) {
                $table->index(['user_id', 'event_type'], 'idx_user_logs_user_event');
            }
            
            // Index for timestamp-based queries
            if (!$this->indexExists('user_logs', 'idx_user_logs_created_at')) {
                $table->index('created_at', 'idx_user_logs_created_at');
            }
        });

        // Course outcomes - frequently filtered by subject
        Schema::table('course_outcomes', function (Blueprint $table) {
            // Composite index for subject-period queries
            if (!$this->indexExists('course_outcomes', 'idx_course_outcomes_subject_period')) {
                $table->index(['subject_id', 'academic_period_id'], 'idx_course_outcomes_subject_period');
            }
            
            // Index for filtering by deletion status
            if (!$this->indexExists('course_outcomes', 'idx_course_outcomes_is_deleted')) {
                $table->index('is_deleted', 'idx_course_outcomes_is_deleted');
            }
        });

        // Course outcome attainments - queried by student and term
        Schema::table('course_outcome_attainments', function (Blueprint $table) {
            // Composite index for student-term queries
            if (!$this->indexExists('course_outcome_attainments', 'idx_coa_student_term')) {
                $table->index(['student_id', 'term'], 'idx_coa_student_term');
            }
            
            // Composite index for CO lookups (column is course_outcome_id, not co_id)
            if (!$this->indexExists('course_outcome_attainments', 'idx_coa_co_term')) {
                $table->index(['course_outcome_id', 'term'], 'idx_coa_co_term');
            }
            
            // Index for subject lookups
            if (!$this->indexExists('course_outcome_attainments', 'idx_coa_subject')) {
                $table->index('subject_id', 'idx_coa_subject');
            }
        });

        // Subjects table - filtered by instructor and period
        Schema::table('subjects', function (Blueprint $table) {
            // Composite index for instructor-period queries
            if (!$this->indexExists('subjects', 'idx_subjects_instructor_period')) {
                $table->index(['instructor_id', 'academic_period_id'], 'idx_subjects_instructor_period');
            }
            
            // Composite index for course-period queries
            if (!$this->indexExists('subjects', 'idx_subjects_course_period')) {
                $table->index(['course_id', 'academic_period_id'], 'idx_subjects_course_period');
            }
            
            // Index for filtering by deletion status
            if (!$this->indexExists('subjects', 'idx_subjects_is_deleted')) {
                $table->index('is_deleted', 'idx_subjects_is_deleted');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop scores indexes
        Schema::table('scores', function (Blueprint $table) {
            $this->dropIndexIfExists($table, 'idx_scores_student_activity');
            $this->dropIndexIfExists($table, 'idx_scores_is_deleted');
        });

        // Drop term_grades indexes
        Schema::table('term_grades', function (Blueprint $table) {
            $this->dropIndexIfExists($table, 'idx_term_grades_subject_term');
            $this->dropIndexIfExists($table, 'idx_term_grades_student_subject');
            $this->dropIndexIfExists($table, 'idx_term_grades_is_deleted');
        });

        // Drop final_grades indexes
        Schema::table('final_grades', function (Blueprint $table) {
            $this->dropIndexIfExists($table, 'idx_final_grades_student_subject');
            $this->dropIndexIfExists($table, 'idx_final_grades_academic_period');
            $this->dropIndexIfExists($table, 'idx_final_grades_is_deleted');
        });

        // Drop activities indexes
        Schema::table('activities', function (Blueprint $table) {
            $this->dropIndexIfExists($table, 'idx_activities_subject_term');
            $this->dropIndexIfExists($table, 'idx_activities_subject_type');
            $this->dropIndexIfExists($table, 'idx_activities_is_deleted');
        });

        // Drop students indexes
        Schema::table('students', function (Blueprint $table) {
            $this->dropIndexIfExists($table, 'idx_students_course_year');
            $this->dropIndexIfExists($table, 'idx_students_is_deleted');
            $this->dropIndexIfExists($table, 'idx_students_last_name');
        });

        // Drop student_subjects indexes
        Schema::table('student_subjects', function (Blueprint $table) {
            $this->dropIndexIfExists($table, 'idx_student_subjects_unique');
            $this->dropIndexIfExists($table, 'idx_student_subjects_is_deleted');
        });

        // Drop user_logs indexes
        Schema::table('user_logs', function (Blueprint $table) {
            $this->dropIndexIfExists($table, 'idx_user_logs_user_event');
            $this->dropIndexIfExists($table, 'idx_user_logs_created_at');
        });

        // Drop course_outcomes indexes
        Schema::table('course_outcomes', function (Blueprint $table) {
            $this->dropIndexIfExists($table, 'idx_course_outcomes_subject_period');
            $this->dropIndexIfExists($table, 'idx_course_outcomes_is_deleted');
        });

        // Drop course_outcome_attainments indexes
        Schema::table('course_outcome_attainments', function (Blueprint $table) {
            $this->dropIndexIfExists($table, 'idx_coa_student_term');
            $this->dropIndexIfExists($table, 'idx_coa_co_term');
            $this->dropIndexIfExists($table, 'idx_coa_subject');
        });

        // Drop subjects indexes
        Schema::table('subjects', function (Blueprint $table) {
            $this->dropIndexIfExists($table, 'idx_subjects_instructor_period');
            $this->dropIndexIfExists($table, 'idx_subjects_course_period');
            $this->dropIndexIfExists($table, 'idx_subjects_is_deleted');
        });
    }

    /**
     * Check if an index exists on a table.
     */
    private function indexExists(string $table, string $indexName): bool
    {
        $indexes = DB::select("SHOW INDEX FROM {$table} WHERE Key_name = ?", [$indexName]);
        return count($indexes) > 0;
    }

    /**
     * Drop an index if it exists.
     */
    private function dropIndexIfExists(Blueprint $table, string $indexName): void
    {
        try {
            $table->dropIndex($indexName);
        } catch (\Exception $e) {
            // Index doesn't exist, ignore
        }
    }
};
