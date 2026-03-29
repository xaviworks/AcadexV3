<?php

namespace Tests\Feature;

use App\Models\AcademicPeriod;
use App\Models\Course;
use App\Models\Department;
use App\Models\GradesFormula;
use App\Models\Subject;
use App\Models\User;
use App\Support\Grades\FormulaStructure;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminGradesFormulaGEUiTest extends TestCase
{
    use RefreshDatabase;

    public function test_wildcards_page_shows_ge_as_general_education_with_separate_formula_guidance(): void
    {
        [$admin, $period, $geDepartment, , , $nursingDepartment] = $this->seedGeFormulaUiContext();

        $response = $this->actingAs($admin)->get(route('admin.gradesFormula', [
            'academic_period_id' => $period->id,
            'view' => 'formulas',
        ]));

        $response->assertOk();
        $response->assertSeeText('General Education');
        $response->assertSeeText('GE requires a separate baseline formula because it serves multiple programs.');
        $response->assertSeeText('Custom: General Education Baseline Formula');
        $response->assertSeeText($nursingDepartment->department_description);

        $response->assertDontSeeText('School of General Education');
        $response->assertDontSeeText('School of General Education Baseline Formula');
    }

    public function test_department_course_and_subject_formula_pages_normalize_legacy_ge_label_text(): void
    {
        [$admin, $period, $geDepartment, $geCourse, $geSubject] = $this->seedGeFormulaUiContext();

        $departmentResponse = $this->actingAs($admin)->get(route('admin.gradesFormula.department', [
            'department' => $geDepartment->id,
            'academic_period_id' => $period->id,
        ]));

        $departmentResponse->assertOk();
        $departmentResponse->assertSeeText('General Education');
        $departmentResponse->assertSeeText('Baseline: General Education Baseline Formula');
        $departmentResponse->assertDontSeeText('School of General Education');

        $editDepartmentResponse = $this->actingAs($admin)->get(route('admin.gradesFormula.edit.department', [
            'department' => $geDepartment->id,
            'academic_period_id' => $period->id,
        ]));

        $editDepartmentResponse->assertOk();
        $editDepartmentResponse->assertSeeText('GE - General Education');
        $editDepartmentResponse->assertDontSeeText('School of General Education');

        $courseResponse = $this->actingAs($admin)->get(route('admin.gradesFormula.course', [
            'department' => $geDepartment->id,
            'course' => $geCourse->id,
            'academic_period_id' => $period->id,
        ]));

        $courseResponse->assertOk();
        $courseResponse->assertSeeText('General Education Baseline Formula');
        $courseResponse->assertDontSeeText('School of General Education Baseline Formula');

        $subjectResponse = $this->actingAs($admin)->get(route('admin.gradesFormula.subject', [
            'subject' => $geSubject->id,
            'academic_period_id' => $period->id,
        ]));

        $subjectResponse->assertOk();
        $subjectResponse->assertSeeText('General Education Baseline Formula');
        $subjectResponse->assertDontSeeText('School of General Education Baseline Formula');
    }

    /**
     * @return array{0: User, 1: AcademicPeriod, 2: Department, 3: Course, 4: Subject, 5: Department}
     */
    private function seedGeFormulaUiContext(): array
    {
        $admin = User::factory()->create([
            'role' => 3,
            'is_active' => true,
        ]);

        $period = AcademicPeriod::create([
            'academic_year' => '2025-2026',
            'semester' => '1st',
            'is_deleted' => false,
        ]);

        $geDepartment = Department::updateOrCreate(
            ['department_code' => 'GE'],
            [
                'department_description' => 'School of General Education',
                'is_deleted' => false,
            ]
        );

        $nursingDepartment = Department::updateOrCreate(
            ['department_code' => 'NURSING'],
            [
                'department_description' => 'School of Nursing',
                'is_deleted' => false,
            ]
        );

        $geCourse = Course::create([
            'course_code' => 'GEUI',
            'course_description' => 'General Education Program',
            'department_id' => $geDepartment->id,
            'is_deleted' => false,
        ]);

        $geSubject = Subject::create([
            'subject_code' => 'GEUI101',
            'subject_description' => 'GE UI Test Subject',
            'is_universal' => true,
            'department_id' => $geDepartment->id,
            'course_id' => $geCourse->id,
            'academic_period_id' => $period->id,
            'is_deleted' => false,
        ]);

        GradesFormula::create([
            'name' => 'ge_baseline_formula',
            'label' => 'School of General Education Baseline Formula',
            'scope_level' => 'department',
            'department_id' => $geDepartment->id,
            'semester' => null,
            'academic_period_id' => null,
            'base_score' => 40,
            'scale_multiplier' => 60,
            'passing_grade' => 75,
            'structure_type' => 'lecture_only',
            'structure_config' => FormulaStructure::default('lecture_only'),
            'is_department_fallback' => true,
        ]);

        return [$admin, $period, $geDepartment, $geCourse, $geSubject, $nursingDepartment];
    }
}
