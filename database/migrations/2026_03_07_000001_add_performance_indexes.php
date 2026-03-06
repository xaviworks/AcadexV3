<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Performance-index migration.
 *
 * Adds composite indexes to the highest-traffic tables to eliminate
 * full-table scans in score lookups, term-grade aggregations,
 * activity queries, and user-log admin charts.
 */
return new class extends Migration
{
    public function up(): void
    {
        // scores: (student_id, activity_id) is the canonical lookup pair used
        // in every Score::updateOrCreate / Score::where on the grading pages.
        Schema::table('scores', function (Blueprint $table) {
            $table->index(['student_id', 'activity_id'], 'scores_student_activity_idx');
        });

        // term_grades: two hot-path patterns:
        //   (subject_id, term_id) – used in COUNT DISTINCT for grade-status checks
        //   (student_id, subject_id, term_id) – used in updateOrCreate
        Schema::table('term_grades', function (Blueprint $table) {
            $table->index(['subject_id', 'term_id'], 'tg_subject_term_idx');
            $table->index(['student_id', 'subject_id', 'term_id'], 'tg_student_subject_term_idx');
        });

        // activities: every grading page filters on (subject_id, term)
        Schema::table('activities', function (Blueprint $table) {
            $table->index(['subject_id', 'term'], 'activities_subject_term_idx');
        });

        // student_subjects: pivot lookups drive whereHas('subjects') /
        // whereHas('students') across instructor and chairperson views.
        Schema::table('student_subjects', function (Blueprint $table) {
            $table->index(['student_id', 'subject_id'], 'ss_student_subject_idx');
            $table->index(['subject_id', 'student_id'], 'ss_subject_student_idx');
        });

        // user_logs: admin dashboard fires GROUP BY HOUR / GROUP BY MONTH
        // queries filtered on event_type + created_at.
        Schema::table('user_logs', function (Blueprint $table) {
            $table->index(['event_type', 'created_at'], 'ul_event_created_idx');
        });

        // subjects: the most common multi-column filter in instructor /
        // chairperson / GE-coordinator views.
        Schema::table('subjects', function (Blueprint $table) {
            $table->index(
                ['academic_period_id', 'instructor_id', 'is_deleted'],
                'subjects_period_instructor_idx'
            );
            $table->index(
                ['academic_period_id', 'department_id', 'is_deleted'],
                'subjects_period_dept_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::table('scores', function (Blueprint $table) {
            $table->dropIndex('scores_student_activity_idx');
        });

        Schema::table('term_grades', function (Blueprint $table) {
            $table->dropIndex('tg_subject_term_idx');
            $table->dropIndex('tg_student_subject_term_idx');
        });

        Schema::table('activities', function (Blueprint $table) {
            $table->dropIndex('activities_subject_term_idx');
        });

        Schema::table('student_subjects', function (Blueprint $table) {
            $table->dropIndex('ss_student_subject_idx');
            $table->dropIndex('ss_subject_student_idx');
        });

        Schema::table('user_logs', function (Blueprint $table) {
            $table->dropIndex('ul_event_created_idx');
        });

        Schema::table('subjects', function (Blueprint $table) {
            $table->dropIndex('subjects_period_instructor_idx');
            $table->dropIndex('subjects_period_dept_idx');
        });
    }
};
