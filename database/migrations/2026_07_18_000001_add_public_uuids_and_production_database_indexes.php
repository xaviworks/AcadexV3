<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * @var array<int, string>
     */
    private array $uuidTables = [
        'users',
        'departments',
        'courses',
        'academic_periods',
        'students',
        'subjects',
        'activities',
        'course_outcomes',
        'program_learning_outcomes',
        'program_learning_outcome_mappings',
        'course_outcome_attainments',
        'subject_attainment_levels',
        'curriculums',
        'curriculum_subjects',
        'student_subjects',
        'scores',
        'term_grades',
        'final_grades',
        'grades_formula',
        'grades_formula_weights',
        'announcements',
        'announcement_views',
        'announcement_templates',
        'help_guides',
        'help_guide_attachments',
        'structure_templates',
        'structure_template_requests',
        'ge_subject_requests',
        'g_e_subject_requests',
        'grade_notifications',
        'notification_preferences',
        'user_logs',
        'audit_logs',
        'backups',
        'settings',
        'review_students',
        'unverified_users',
        'user_devices',
    ];

    public function up(): void
    {
        foreach ($this->uuidTables as $table) {
            $this->addUuid($table);
        }

        $this->addIndexes();
        $this->addUniqueConstraints();
    }

    public function down(): void
    {
        $this->dropUniqueConstraints();
        $this->dropIndexes();

        foreach (array_reverse($this->uuidTables) as $table) {
            $this->dropUuid($table);
        }
    }

    private function addUuid(string $table): void
    {
        if (! Schema::hasTable($table) || Schema::hasColumn($table, 'uuid')) {
            return;
        }

        Schema::table($table, function (Blueprint $table): void {
            $table->uuid('uuid')->nullable()->after('id');
        });

        DB::table($table)
            ->whereNull('uuid')
            ->orderBy('id')
            ->select(['id'])
            ->chunkById(500, function ($rows) use ($table): void {
                foreach ($rows as $row) {
                    DB::table($table)
                        ->where('id', $row->id)
                        ->update(['uuid' => (string) Str::uuid()]);
                }
            });

        $this->addUnique($table, ['uuid'], "{$table}_uuid_unique");
    }

    private function dropUuid(string $table): void
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'uuid')) {
            return;
        }

        $this->dropIndex($table, "{$table}_uuid_unique");

        Schema::table($table, function (Blueprint $table): void {
            $table->dropColumn('uuid');
        });
    }

    private function addIndexes(): void
    {
        $this->addIndex('users', ['role', 'is_active'], 'users_role_active_idx');
        $this->addIndex('users', ['department_id', 'role', 'is_active'], 'users_dept_role_active_idx');
        $this->addIndex('users', ['course_id', 'role', 'is_active'], 'users_course_role_active_idx');

        $this->addIndex('departments', ['is_deleted', 'department_description'], 'departments_deleted_desc_idx');
        $this->addIndex('courses', ['department_id', 'is_deleted', 'course_code'], 'courses_dept_deleted_code_idx');
        $this->addIndex('academic_periods', ['is_deleted', 'academic_year', 'semester'], 'periods_deleted_year_sem_idx');

        $this->addIndex('students', ['department_id', 'course_id', 'academic_period_id', 'is_deleted'], 'students_scope_deleted_idx');
        $this->addIndex('students', ['academic_period_id', 'is_deleted'], 'students_period_deleted_idx');
        $this->addIndex('students', ['last_name', 'first_name'], 'students_name_idx');

        $this->addIndex('subjects', ['academic_period_id', 'course_id', 'department_id', 'is_deleted'], 'subjects_period_scope_idx');
        $this->addIndex('subjects', ['instructor_id', 'academic_period_id', 'is_deleted'], 'subjects_instructor_period_idx');
        $this->addIndex('subjects', ['department_id', 'academic_period_id', 'is_deleted'], 'subjects_dept_period_idx');
        $this->addIndex('subjects', ['course_id', 'academic_period_id', 'is_deleted'], 'subjects_course_period_idx');

        $this->addIndex('instructor_subject', ['instructor_id', 'subject_id'], 'inst_subject_instructor_subject_idx');
        $this->addIndex('instructor_subject', ['subject_id', 'instructor_id'], 'inst_subject_subject_instructor_idx');

        $this->addIndex('student_subjects', ['subject_id', 'is_deleted'], 'student_subjects_subject_deleted_idx');
        $this->addIndex('student_subjects', ['student_id', 'is_deleted'], 'student_subjects_student_deleted_idx');

        $this->addIndex('activities', ['subject_id', 'term', 'is_deleted'], 'activities_subject_term_deleted_idx');
        $this->addIndex('activities', ['course_outcome_id', 'is_deleted'], 'activities_co_deleted_idx');

        $this->addIndex('scores', ['activity_id', 'student_id', 'is_deleted'], 'scores_activity_student_deleted_idx');
        $this->addIndex('scores', ['student_id', 'activity_id'], 'scores_student_activity_idx');

        $this->addIndex('term_grades', ['subject_id', 'academic_period_id', 'term_id', 'is_deleted'], 'term_grades_subject_period_term_idx');
        $this->addIndex('term_grades', ['student_id', 'subject_id', 'term_id'], 'term_grades_student_subject_term_idx');

        $this->addIndex('final_grades', ['subject_id', 'academic_period_id', 'is_deleted'], 'final_grades_subject_period_idx');
        $this->addIndex('final_grades', ['student_id', 'subject_id', 'academic_period_id'], 'final_grades_student_subject_idx');

        $this->addIndex('course_outcomes', ['subject_id', 'academic_period_id', 'is_deleted'], 'course_outcomes_subject_period_idx');
        $this->addIndex('course_outcome_attainments', ['subject_id', 'academic_period_id', 'term', 'is_deleted'], 'co_attainments_subject_period_idx');
        $this->addIndex('course_outcome_attainments', ['course_outcome_id', 'academic_period_id'], 'co_attainments_co_period_idx');
        $this->addIndex('program_learning_outcomes', ['course_id', 'is_deleted'], 'plos_course_deleted_idx');
        $this->addIndex('program_learning_outcome_mappings', ['course_id', 'program_learning_outcome_id'], 'plo_maps_course_plo_idx');
        $this->addIndex('program_learning_outcome_mappings', ['course_outcome_id'], 'plo_maps_co_idx');

        $this->addIndex('grades_formula', ['scope_level', 'department_id', 'course_id', 'subject_id'], 'formula_scope_owner_idx');
        $this->addIndex('grades_formula', ['department_id', 'academic_period_id', 'semester'], 'formula_dept_period_sem_idx');
        $this->addIndex('grades_formula_weights', ['grades_formula_id', 'term'], 'formula_weights_formula_term_idx');

        $this->addIndex('announcements', ['is_active', 'start_date', 'end_date'], 'announcements_active_window_idx');
        $this->addIndex('announcements', ['priority', 'created_at'], 'announcements_priority_created_idx');
        $this->addIndex('announcement_views', ['user_id', 'viewed_at'], 'announcement_views_user_viewed_idx');

        $this->addIndex('help_guides', ['is_active', 'category', 'sort_order'], 'help_guides_active_category_idx');
        $this->addIndex('structure_template_requests', ['status', 'created_at'], 'template_requests_status_created_idx');
        $this->addIndex('ge_subject_requests', ['status', 'requested_by', 'created_at'], 'ge_requests_status_requester_idx');
        $this->addIndex('g_e_subject_requests', ['status', 'requested_by', 'created_at'], 'g_e_requests_status_requester_idx');
        $this->addIndex('user_logs', ['user_id', 'created_at'], 'user_logs_user_created_idx');
    }

    private function dropIndexes(): void
    {
        foreach ([
            ['users', 'users_role_active_idx'],
            ['users', 'users_dept_role_active_idx'],
            ['users', 'users_course_role_active_idx'],
            ['departments', 'departments_deleted_desc_idx'],
            ['courses', 'courses_dept_deleted_code_idx'],
            ['academic_periods', 'periods_deleted_year_sem_idx'],
            ['students', 'students_scope_deleted_idx'],
            ['students', 'students_period_deleted_idx'],
            ['students', 'students_name_idx'],
            ['subjects', 'subjects_period_scope_idx'],
            ['subjects', 'subjects_instructor_period_idx'],
            ['subjects', 'subjects_dept_period_idx'],
            ['subjects', 'subjects_course_period_idx'],
            ['instructor_subject', 'inst_subject_instructor_subject_idx'],
            ['instructor_subject', 'inst_subject_subject_instructor_idx'],
            ['student_subjects', 'student_subjects_subject_deleted_idx'],
            ['student_subjects', 'student_subjects_student_deleted_idx'],
            ['activities', 'activities_subject_term_deleted_idx'],
            ['activities', 'activities_co_deleted_idx'],
            ['scores', 'scores_activity_student_deleted_idx'],
            ['scores', 'scores_student_activity_idx'],
            ['term_grades', 'term_grades_subject_period_term_idx'],
            ['term_grades', 'term_grades_student_subject_term_idx'],
            ['final_grades', 'final_grades_subject_period_idx'],
            ['final_grades', 'final_grades_student_subject_idx'],
            ['course_outcomes', 'course_outcomes_subject_period_idx'],
            ['course_outcome_attainments', 'co_attainments_subject_period_idx'],
            ['course_outcome_attainments', 'co_attainments_co_period_idx'],
            ['program_learning_outcomes', 'plos_course_deleted_idx'],
            ['program_learning_outcome_mappings', 'plo_maps_course_plo_idx'],
            ['program_learning_outcome_mappings', 'plo_maps_co_idx'],
            ['grades_formula', 'formula_scope_owner_idx'],
            ['grades_formula', 'formula_dept_period_sem_idx'],
            ['grades_formula_weights', 'formula_weights_formula_term_idx'],
            ['announcements', 'announcements_active_window_idx'],
            ['announcements', 'announcements_priority_created_idx'],
            ['announcement_views', 'announcement_views_user_viewed_idx'],
            ['help_guides', 'help_guides_active_category_idx'],
            ['structure_template_requests', 'template_requests_status_created_idx'],
            ['ge_subject_requests', 'ge_requests_status_requester_idx'],
            ['g_e_subject_requests', 'g_e_requests_status_requester_idx'],
            ['user_logs', 'user_logs_user_created_idx'],
        ] as [$table, $index]) {
            $this->dropIndex($table, $index);
        }
    }

    private function addUniqueConstraints(): void
    {
        $this->addUniqueIfClean('student_subjects', ['student_id', 'subject_id'], 'student_subjects_student_subject_unique');
        $this->addUniqueIfClean('scores', ['activity_id', 'student_id'], 'scores_activity_student_unique');
        $this->addUniqueIfClean('term_grades', ['student_id', 'subject_id', 'academic_period_id', 'term_id'], 'term_grades_student_subject_period_unique');
        $this->addUniqueIfClean('final_grades', ['student_id', 'subject_id', 'academic_period_id'], 'final_grades_student_subject_period_unique');
    }

    private function dropUniqueConstraints(): void
    {
        $this->dropIndex('student_subjects', 'student_subjects_student_subject_unique');
        $this->dropIndex('scores', 'scores_activity_student_unique');
        $this->dropIndex('term_grades', 'term_grades_student_subject_period_unique');
        $this->dropIndex('final_grades', 'final_grades_student_subject_period_unique');
    }

    /**
     * @param array<int, string> $columns
     */
    private function addIndex(string $table, array $columns, string $name): void
    {
        if (! $this->tableHasColumns($table, $columns) || $this->indexExists($table, $name)) {
            return;
        }

        Schema::table($table, function (Blueprint $table) use ($columns, $name): void {
            $table->index($columns, $name);
        });
    }

    /**
     * @param array<int, string> $columns
     */
    private function addUnique(string $table, array $columns, string $name): void
    {
        if (! $this->tableHasColumns($table, $columns) || $this->indexExists($table, $name)) {
            return;
        }

        Schema::table($table, function (Blueprint $table) use ($columns, $name): void {
            $table->unique($columns, $name);
        });
    }

    /**
     * @param array<int, string> $columns
     */
    private function addUniqueIfClean(string $table, array $columns, string $name): void
    {
        if (! $this->tableHasColumns($table, $columns) || $this->indexExists($table, $name)) {
            return;
        }

        $duplicates = DB::table($table)
            ->select($columns)
            ->selectRaw('COUNT(*) as duplicate_count')
            ->groupBy($columns)
            ->havingRaw('COUNT(*) > 1')
            ->limit(1)
            ->exists();

        if ($duplicates) {
            throw new RuntimeException("Cannot add {$name}; duplicate rows already exist in {$table}.");
        }

        $this->addUnique($table, $columns, $name);
    }

    /**
     * @param array<int, string> $columns
     */
    private function tableHasColumns(string $table, array $columns): bool
    {
        if (! Schema::hasTable($table)) {
            return false;
        }

        foreach ($columns as $column) {
            if (! Schema::hasColumn($table, $column)) {
                return false;
            }
        }

        return true;
    }

    private function indexExists(string $table, string $name): bool
    {
        if (! Schema::hasTable($table)) {
            return false;
        }

        return collect(Schema::getIndexes($table))->contains(
            fn (array $index): bool => ($index['name'] ?? null) === $name
        );
    }

    private function dropIndex(string $table, string $name): void
    {
        if (! Schema::hasTable($table) || ! $this->indexExists($table, $name)) {
            return;
        }

        Schema::table($table, function (Blueprint $table) use ($name): void {
            $table->dropIndex($name);
        });
    }
};
