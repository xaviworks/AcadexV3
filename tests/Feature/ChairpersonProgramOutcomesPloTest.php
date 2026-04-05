<?php

namespace Tests\Feature;

use App\Models\AcademicPeriod;
use App\Models\Activity;
use App\Models\Course;
use App\Models\CourseOutcomes;
use App\Models\Department;
use App\Models\ProgramLearningOutcome;
use App\Models\ProgramLearningOutcomeMapping;
use App\Models\Score;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ChairpersonProgramOutcomesPloTest extends TestCase
{
    use RefreshDatabase;

    public function test_first_visit_creates_default_plos_for_chairperson_program_report(): void
    {
        [$chairperson, $period, $course] = $this->createChairpersonContext();

        $response = $this->actingAs($chairperson)
            ->withSession(['active_academic_period_id' => $period->id])
            ->get(route('chairperson.reports.co-program'));

        $response->assertOk();
        $response->assertSee('Program');
        $response->assertSee('IT01');
        $response->assertSee('IT13');
        $response->assertSee('Program Outcome Reports');
        $response->assertSee('PLO Definitions');
        $response->assertSee('CO to PLO Mapping');
        $response->assertDontSee('<i class="bi bi-mortarboard text-primary me-2"></i>Program', false);
        $response->assertSee('data-ui="co-plo-page-tabs"', false);
        $response->assertSee('id="plo-reports-tab"', false);
        $response->assertDontSee('Configure PLOs');
        $response->assertDontSee('configurePloModal');

        $this->assertDatabaseCount('program_learning_outcomes', 13);
        $this->assertDatabaseHas('program_learning_outcomes', [
            'course_id' => $course->id,
            'plo_code' => 'IT01',
            'title' => 'Apply knowledge of computing, science, and mathematics appropriate to the discipline',
            'is_active' => true,
            'is_deleted' => false,
        ]);
    }

    public function test_co_program_defaults_to_reports_tab_when_no_tab_session_value(): void
    {
        [$chairperson, $period] = $this->createChairpersonContext();

        $response = $this->actingAs($chairperson)
            ->withSession(['active_academic_period_id' => $period->id])
            ->get(route('chairperson.reports.co-program'));

        $response->assertOk();

        $html = (string) $response->getContent();

        $this->assertTagHasClass($html, 'plo-reports-tab', 'active');
        $this->assertTagLacksClass($html, 'plo-definitions-tab', 'active');
        $this->assertTagLacksClass($html, 'plo-mapping-tab', 'active');
        $this->assertTagHasClass($html, 'plo-reports-panel', 'show');
        $this->assertTagHasClass($html, 'plo-reports-panel', 'active');
    }

    public function test_co_program_honors_mapping_tab_session_value_and_invalid_falls_back_to_reports(): void
    {
        [$chairperson, $period] = $this->createChairpersonContext();

        $mappingResponse = $this->actingAs($chairperson)
            ->withSession([
                'active_academic_period_id' => $period->id,
                'ploTab' => 'mapping',
            ])
            ->get(route('chairperson.reports.co-program'));

        $mappingResponse->assertOk();

        $mappingHtml = (string) $mappingResponse->getContent();

        $this->assertTagHasClass($mappingHtml, 'plo-mapping-tab', 'active');
        $this->assertTagHasClass($mappingHtml, 'plo-mapping-panel', 'show');
        $this->assertTagHasClass($mappingHtml, 'plo-mapping-panel', 'active');
        $this->assertTagLacksClass($mappingHtml, 'plo-reports-tab', 'active');

        $fallbackResponse = $this->actingAs($chairperson)
            ->withSession([
                'active_academic_period_id' => $period->id,
                'ploTab' => 'unsupported-tab',
            ])
            ->get(route('chairperson.reports.co-program'));

        $fallbackResponse->assertOk();

        $fallbackHtml = (string) $fallbackResponse->getContent();

        $this->assertTagHasClass($fallbackHtml, 'plo-reports-tab', 'active');
        $this->assertTagHasClass($fallbackHtml, 'plo-reports-panel', 'show');
        $this->assertTagHasClass($fallbackHtml, 'plo-reports-panel', 'active');
    }

    public function test_co_program_starts_clean_with_disabled_save_actions_until_edits(): void
    {
        [$chairperson, $period, $course, $department] = $this->createChairpersonContext();

        $subject = Subject::create([
            'subject_code' => 'IT101',
            'subject_description' => 'Introduction to IT',
            'academic_period_id' => $period->id,
            'department_id' => $department->id,
            'course_id' => $course->id,
            'is_deleted' => false,
        ]);

        CourseOutcomes::create([
            'subject_id' => $subject->id,
            'academic_period_id' => $period->id,
            'co_code' => 'CO1',
            'co_identifier' => 'IT101.1',
            'description' => 'Explain fundamental IT concepts',
            'target_percentage' => 75,
            'created_by' => $chairperson->id,
            'updated_by' => $chairperson->id,
            'is_deleted' => false,
        ]);

        $response = $this->actingAs($chairperson)
            ->withSession(['active_academic_period_id' => $period->id])
            ->get(route('chairperson.reports.co-program'));

        $response->assertOk();

        $html = (string) $response->getContent();

        $response->assertDontSee('id="ploDefinitionsDirtyIndicator"', false);
        $response->assertDontSee('id="ploMappingDirtyIndicator"', false);
        $response->assertDontSee('id="ploDefinitionsTabDirtyDot"', false);
        $response->assertDontSee('id="ploMappingTabDirtyDot"', false);

        $this->assertTagHasAttribute($html, 'ploDefinitionsSaveButton', 'disabled');
        $this->assertTagHasAttribute($html, 'poMatrixSaveTop', 'disabled');
        $this->assertTagHasAttribute($html, 'poMatrixSaveBottom', 'disabled');

        $this->assertTagHasAttribute($html, 'ploDefinitionsSaveButton', 'aria-disabled', 'true');
        $this->assertTagHasAttribute($html, 'poMatrixSaveTop', 'aria-disabled', 'true');
        $this->assertTagHasAttribute($html, 'poMatrixSaveBottom', 'aria-disabled', 'true');
    }

    public function test_co_program_uses_filled_delete_button_with_label_for_plo_definitions(): void
    {
        [$chairperson, $period] = $this->createChairpersonContext();

        $response = $this->actingAs($chairperson)
            ->withSession(['active_academic_period_id' => $period->id])
            ->get(route('chairperson.reports.co-program'));

        $response->assertOk();
        $response->assertSee('class="btn btn-danger btn-sm rounded-pill remove-plo-row"', false);
        $response->assertSee('<i class="bi bi-trash3 me-1"></i>Remove', false);
    }

    public function test_definitions_save_returns_to_definitions_tab(): void
    {
        [$chairperson, $period, $course] = $this->createChairpersonContext();

        $this->actingAs($chairperson)
            ->withSession(['active_academic_period_id' => $period->id])
            ->get(route('chairperson.reports.co-program'))
            ->assertOk();

        $payloadRows = ProgramLearningOutcome::where('course_id', $course->id)
            ->where('is_deleted', false)
            ->orderBy('display_order')
            ->get()
            ->values()
            ->map(function (ProgramLearningOutcome $plo) {
                return [
                    'id' => $plo->id,
                    'code' => $plo->plo_code,
                    'title' => $plo->title,
                    'is_active' => $plo->is_active ? '1' : '0',
                    'delete' => '0',
                ];
            })
            ->all();

        $response = $this->actingAs($chairperson)
            ->withSession(['active_academic_period_id' => $period->id])
            ->post(route('chairperson.reports.co-program.plos.save'), [
                'plos' => $payloadRows,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $response->assertSessionHas('ploTab', 'definitions');
        $response->assertSessionMissing('openPloModal');
    }

    public function test_mapping_save_persists_course_outcome_row_links_for_program_outcomes(): void
    {
        [$chairperson, $period, $course, $department] = $this->createChairpersonContext();

        $subject = Subject::create([
            'subject_code' => 'IT101',
            'subject_description' => 'Introduction to IT',
            'academic_period_id' => $period->id,
            'department_id' => $department->id,
            'course_id' => $course->id,
            'is_deleted' => false,
        ]);

        $courseOutcome = CourseOutcomes::create([
            'subject_id' => $subject->id,
            'academic_period_id' => $period->id,
            'co_code' => 'CO1',
            'co_identifier' => 'IT101.1',
            'description' => 'Explain fundamental IT concepts',
            'target_percentage' => 75,
            'created_by' => $chairperson->id,
            'updated_by' => $chairperson->id,
            'is_deleted' => false,
        ]);

        $pageResponse = $this->actingAs($chairperson)
            ->withSession(['active_academic_period_id' => $period->id])
            ->get(route('chairperson.reports.co-program'));

        $pageResponse->assertOk();
        $pageResponse->assertSee('data-ui="co-plo-matrix-wrap"', false);
        $pageResponse->assertSee('data-ui="co-plo-save-top"', false);
        $pageResponse->assertSee('data-ui="co-plo-expand-toggle"', false);
        $pageResponse->assertSee('data-default-label="Expand table"', false);
        $pageResponse->assertSee('data-expanded-label="Exit expanded table view"', false);
        $pageResponse->assertSee('data-ui="co-plo-subject-jump"', false);
        $pageResponse->assertSee('po-matrix-subject-jump-count', false);
        $pageResponse->assertSee('data-ui="co-plo-column-header"', false);
        $pageResponse->assertSee('po-matrix-outcome-head', false);
        $pageResponse->assertSee('data-ui="co-plo-resize-control"', false);
        $pageResponse->assertSee('id="poMatrixResizeHandle"', false);
        $pageResponse->assertSee('id="poMatrixResizeValue"', false);
        $pageResponse->assertSee('data-ui="co-plo-page-tabs"', false);

        $programOutcome = ProgramLearningOutcome::where('course_id', $course->id)
            ->where('plo_code', 'IT01')
            ->firstOrFail();

        $response = $this->actingAs($chairperson)
            ->withSession(['active_academic_period_id' => $period->id])
            ->post(route('chairperson.reports.co-program.plos.mappings.save'), [
                'mappings' => [
                    $programOutcome->id => [$courseOutcome->id],
                ],
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $response->assertSessionHas('ploTab', 'mapping');
        $response->assertSessionMissing('openPloModal');

        $this->assertDatabaseHas('program_learning_outcome_mappings', [
            'course_id' => $course->id,
            'program_learning_outcome_id' => $programOutcome->id,
            'course_outcome_id' => $courseOutcome->id,
            'co_code' => 'CO1',
        ]);
    }

    public function test_program_report_displays_mapped_plo_average_and_dash_for_unmapped_plo(): void
    {
        [$chairperson, $period, $course, $department] = $this->createChairpersonContext();

        $plo1 = ProgramLearningOutcome::create([
            'course_id' => $course->id,
            'plo_code' => 'PLO1',
            'title' => 'Engineering Knowledge',
            'display_order' => 1,
            'is_active' => true,
            'is_deleted' => false,
        ]);

        $plo2 = ProgramLearningOutcome::create([
            'course_id' => $course->id,
            'plo_code' => 'PLO2',
            'title' => 'Problem Analysis',
            'display_order' => 2,
            'is_active' => true,
            'is_deleted' => false,
        ]);

        ProgramLearningOutcomeMapping::create([
            'course_id' => $course->id,
            'program_learning_outcome_id' => $plo1->id,
            'co_code' => 'CO1',
        ]);

        ProgramLearningOutcomeMapping::create([
            'course_id' => $course->id,
            'program_learning_outcome_id' => $plo1->id,
            'co_code' => 'CO2',
        ]);

        $subject = Subject::create([
            'subject_code' => 'BSIT-101',
            'subject_description' => 'Introduction to Programming',
            'academic_period_id' => $period->id,
            'department_id' => $department->id,
            'course_id' => $course->id,
            'is_deleted' => false,
        ]);

        $co1 = CourseOutcomes::create([
            'subject_id' => $subject->id,
            'academic_period_id' => $period->id,
            'co_code' => 'CO1',
            'co_identifier' => 'BSIT101.1',
            'description' => 'Understand concepts',
            'target_percentage' => 80,
            'created_by' => $chairperson->id,
            'updated_by' => $chairperson->id,
            'is_deleted' => false,
        ]);

        $co2 = CourseOutcomes::create([
            'subject_id' => $subject->id,
            'academic_period_id' => $period->id,
            'co_code' => 'CO2',
            'co_identifier' => 'BSIT101.2',
            'description' => 'Build simple programs',
            'target_percentage' => 70,
            'created_by' => $chairperson->id,
            'updated_by' => $chairperson->id,
            'is_deleted' => false,
        ]);

        $student = Student::create([
            'first_name' => 'Juan',
            'last_name' => 'Dela Cruz',
            'department_id' => $department->id,
            'course_id' => $course->id,
            'academic_period_id' => $period->id,
            'year_level' => 1,
            'is_deleted' => false,
        ]);

        DB::table('student_subjects')->insert([
            'student_id' => $student->id,
            'subject_id' => $subject->id,
            'section' => 'A',
            'is_deleted' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $activity1 = Activity::create([
            'subject_id' => $subject->id,
            'term' => 'prelim',
            'type' => 'quiz',
            'title' => 'Quiz 1',
            'course_outcome_id' => $co1->id,
            'number_of_items' => 100,
            'is_deleted' => false,
        ]);

        $activity2 = Activity::create([
            'subject_id' => $subject->id,
            'term' => 'midterm',
            'type' => 'quiz',
            'title' => 'Quiz 2',
            'course_outcome_id' => $co2->id,
            'number_of_items' => 100,
            'is_deleted' => false,
        ]);

        Score::create([
            'activity_id' => $activity1->id,
            'student_id' => $student->id,
            'score' => 75,
            'is_deleted' => false,
        ]);

        Score::create([
            'activity_id' => $activity2->id,
            'student_id' => $student->id,
            'score' => 80,
            'is_deleted' => false,
        ]);

        $response = $this->actingAs($chairperson)
            ->withSession(['active_academic_period_id' => $period->id])
            ->get(route('chairperson.reports.co-program'));

        $response->assertOk();
        $response->assertSee('77.50%');
        $response->assertSee('Target 75.00%');
        $response->assertSee('CO1');
        $response->assertSee('CO2');
        $response->assertSee('Engineering Knowledge');
        $response->assertSee('Problem Analysis');
        $response->assertSee('—');
    }

    public function test_chairperson_cannot_save_more_than_twenty_plos(): void
    {
        [$chairperson, $period] = $this->createChairpersonContext();

        $payload = ['plos' => []];
        for ($i = 1; $i <= 21; $i++) {
            $payload['plos'][] = [
                'code' => 'PLO' . $i,
                'title' => 'Program Learning Outcome ' . $i,
                'display_order' => $i,
                'is_active' => '1',
                'delete' => '0',
            ];
        }

        $response = $this->actingAs($chairperson)
            ->withSession(['active_academic_period_id' => $period->id])
            ->post(route('chairperson.reports.co-program.plos.save'), $payload);

        $response->assertSessionHasErrors('plos');
    }

    public function test_program_co_rollup_only_counts_students_enrolled_in_each_subject(): void
    {
        [$chairperson, $period, $course, $department] = $this->createChairpersonContext();

        $plo = ProgramLearningOutcome::create([
            'course_id' => $course->id,
            'plo_code' => 'PLO1',
            'title' => 'Engineering Knowledge',
            'display_order' => 1,
            'is_active' => true,
            'is_deleted' => false,
        ]);

        ProgramLearningOutcomeMapping::create([
            'course_id' => $course->id,
            'program_learning_outcome_id' => $plo->id,
            'co_code' => 'CO1',
        ]);

        $subjectOne = Subject::create([
            'subject_code' => 'IT101',
            'subject_description' => 'Intro to IT',
            'academic_period_id' => $period->id,
            'department_id' => $department->id,
            'course_id' => $course->id,
            'is_deleted' => false,
        ]);

        $subjectTwo = Subject::create([
            'subject_code' => 'IT102',
            'subject_description' => 'Programming 1',
            'academic_period_id' => $period->id,
            'department_id' => $department->id,
            'course_id' => $course->id,
            'is_deleted' => false,
        ]);

        $coSubjectOne = CourseOutcomes::create([
            'subject_id' => $subjectOne->id,
            'academic_period_id' => $period->id,
            'co_code' => 'CO1',
            'co_identifier' => 'IT101.1',
            'description' => 'Understand concepts',
            'target_percentage' => 80,
            'created_by' => $chairperson->id,
            'updated_by' => $chairperson->id,
            'is_deleted' => false,
        ]);

        $coSubjectTwo = CourseOutcomes::create([
            'subject_id' => $subjectTwo->id,
            'academic_period_id' => $period->id,
            'co_code' => 'CO1',
            'co_identifier' => 'IT102.1',
            'description' => 'Apply concepts',
            'target_percentage' => 80,
            'created_by' => $chairperson->id,
            'updated_by' => $chairperson->id,
            'is_deleted' => false,
        ]);

        $studentOne = Student::create([
            'first_name' => 'Ana',
            'last_name' => 'Lopez',
            'department_id' => $department->id,
            'course_id' => $course->id,
            'academic_period_id' => $period->id,
            'year_level' => 1,
            'is_deleted' => false,
        ]);

        $studentTwo = Student::create([
            'first_name' => 'Ben',
            'last_name' => 'Reyes',
            'department_id' => $department->id,
            'course_id' => $course->id,
            'academic_period_id' => $period->id,
            'year_level' => 1,
            'is_deleted' => false,
        ]);

        DB::table('student_subjects')->insert([
            [
                'student_id' => $studentOne->id,
                'subject_id' => $subjectOne->id,
                'section' => 'A',
                'is_deleted' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'student_id' => $studentTwo->id,
                'subject_id' => $subjectTwo->id,
                'section' => 'A',
                'is_deleted' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $activityOne = Activity::create([
            'subject_id' => $subjectOne->id,
            'term' => 'prelim',
            'type' => 'quiz',
            'title' => 'IT101 Quiz',
            'course_outcome_id' => $coSubjectOne->id,
            'number_of_items' => 100,
            'is_deleted' => false,
        ]);

        $activityTwo = Activity::create([
            'subject_id' => $subjectTwo->id,
            'term' => 'prelim',
            'type' => 'quiz',
            'title' => 'IT102 Quiz',
            'course_outcome_id' => $coSubjectTwo->id,
            'number_of_items' => 100,
            'is_deleted' => false,
        ]);

        Score::create([
            'activity_id' => $activityOne->id,
            'student_id' => $studentOne->id,
            'score' => 100,
            'is_deleted' => false,
        ]);

        Score::create([
            'activity_id' => $activityTwo->id,
            'student_id' => $studentTwo->id,
            'score' => 50,
            'is_deleted' => false,
        ]);

        $response = $this->actingAs($chairperson)
            ->withSession(['active_academic_period_id' => $period->id])
            ->get(route('chairperson.reports.co-program'));

        $response->assertOk();
        $response->assertSee('75.00%');
        $response->assertSee('Target 80.00%');
        $response->assertSee('Met Expected Outcome');
    }

    public function test_program_outcome_rollup_uses_row_level_mapping_when_multiple_subjects_share_same_co_code(): void
    {
        [$chairperson, $period, $course, $department] = $this->createChairpersonContext();

        $it01 = ProgramLearningOutcome::create([
            'course_id' => $course->id,
            'plo_code' => 'IT01',
            'title' => 'Apply knowledge of computing, science, and mathematics appropriate to the discipline',
            'display_order' => 1,
            'is_active' => true,
            'is_deleted' => false,
        ]);

        $subjectOne = Subject::create([
            'subject_code' => 'IT101',
            'subject_description' => 'Intro to IT',
            'academic_period_id' => $period->id,
            'department_id' => $department->id,
            'course_id' => $course->id,
            'is_deleted' => false,
        ]);

        $subjectTwo = Subject::create([
            'subject_code' => 'IT102',
            'subject_description' => 'Programming 1',
            'academic_period_id' => $period->id,
            'department_id' => $department->id,
            'course_id' => $course->id,
            'is_deleted' => false,
        ]);

        $coSubjectOne = CourseOutcomes::create([
            'subject_id' => $subjectOne->id,
            'academic_period_id' => $period->id,
            'co_code' => 'CO1',
            'co_identifier' => 'IT101.1',
            'description' => 'Understand concepts',
            'target_percentage' => 80,
            'created_by' => $chairperson->id,
            'updated_by' => $chairperson->id,
            'is_deleted' => false,
        ]);

        $coSubjectTwo = CourseOutcomes::create([
            'subject_id' => $subjectTwo->id,
            'academic_period_id' => $period->id,
            'co_code' => 'CO1',
            'co_identifier' => 'IT102.1',
            'description' => 'Apply concepts',
            'target_percentage' => 80,
            'created_by' => $chairperson->id,
            'updated_by' => $chairperson->id,
            'is_deleted' => false,
        ]);

        ProgramLearningOutcomeMapping::create([
            'course_id' => $course->id,
            'program_learning_outcome_id' => $it01->id,
            'course_outcome_id' => $coSubjectOne->id,
            'co_code' => 'CO1',
        ]);

        $studentOne = Student::create([
            'first_name' => 'Ana',
            'last_name' => 'Lopez',
            'department_id' => $department->id,
            'course_id' => $course->id,
            'academic_period_id' => $period->id,
            'year_level' => 1,
            'is_deleted' => false,
        ]);

        $studentTwo = Student::create([
            'first_name' => 'Ben',
            'last_name' => 'Reyes',
            'department_id' => $department->id,
            'course_id' => $course->id,
            'academic_period_id' => $period->id,
            'year_level' => 1,
            'is_deleted' => false,
        ]);

        DB::table('student_subjects')->insert([
            [
                'student_id' => $studentOne->id,
                'subject_id' => $subjectOne->id,
                'section' => 'A',
                'is_deleted' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'student_id' => $studentTwo->id,
                'subject_id' => $subjectTwo->id,
                'section' => 'A',
                'is_deleted' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $activityOne = Activity::create([
            'subject_id' => $subjectOne->id,
            'term' => 'prelim',
            'type' => 'quiz',
            'title' => 'IT101 Quiz',
            'course_outcome_id' => $coSubjectOne->id,
            'number_of_items' => 100,
            'is_deleted' => false,
        ]);

        $activityTwo = Activity::create([
            'subject_id' => $subjectTwo->id,
            'term' => 'prelim',
            'type' => 'quiz',
            'title' => 'IT102 Quiz',
            'course_outcome_id' => $coSubjectTwo->id,
            'number_of_items' => 100,
            'is_deleted' => false,
        ]);

        Score::create([
            'activity_id' => $activityOne->id,
            'student_id' => $studentOne->id,
            'score' => 90,
            'is_deleted' => false,
        ]);

        Score::create([
            'activity_id' => $activityTwo->id,
            'student_id' => $studentTwo->id,
            'score' => 40,
            'is_deleted' => false,
        ]);

        $response = $this->actingAs($chairperson)
            ->withSession(['active_academic_period_id' => $period->id])
            ->get(route('chairperson.reports.co-program'));

        $response->assertOk();
        $response->assertSee('90.00%');
        $response->assertSee('IT101.1');
        $response->assertDontSee('65.00%');
    }

    private function assertTagHasClass(string $html, string $id, string $expectedClass): void
    {
        $classList = $this->extractClassListById($html, $id);

        $this->assertContains(
            $expectedClass,
            $classList,
            sprintf('Expected element #%s to include class "%s".', $id, $expectedClass)
        );
    }

    private function assertTagLacksClass(string $html, string $id, string $unexpectedClass): void
    {
        $classList = $this->extractClassListById($html, $id);

        $this->assertNotContains(
            $unexpectedClass,
            $classList,
            sprintf('Expected element #%s to exclude class "%s".', $id, $unexpectedClass)
        );
    }

    private function assertTagHasAttribute(string $html, string $id, string $attribute, ?string $expectedValue = null): void
    {
        $tag = $this->extractTagById($html, $id);

        if ($expectedValue === null) {
            $attributePattern = '/\\b' . preg_quote($attribute, '/') . '(?:\\s*=\\s*("[^"]*"|\'[^\']*\'))?/i';

            $this->assertMatchesRegularExpression(
                $attributePattern,
                $tag,
                sprintf('Expected element #%s to include attribute "%s".', $id, $attribute)
            );

            return;
        }

        $attributePattern = '/\\b' . preg_quote($attribute, '/') . '="' . preg_quote($expectedValue, '/') . '"/i';

        $this->assertMatchesRegularExpression(
            $attributePattern,
            $tag,
            sprintf(
                'Expected element #%s to include attribute "%s" with value "%s".',
                $id,
                $attribute,
                $expectedValue
            )
        );
    }

    private function extractTagById(string $html, string $id): string
    {
        $idPattern = '/<[^>]*\\bid="' . preg_quote($id, '/') . '"[^>]*>/i';
        $found = preg_match($idPattern, $html, $tagMatches);

        if ($found !== 1) {
            $this->fail(sprintf('Unable to find element with id "%s" in response HTML.', $id));
        }

        return (string) ($tagMatches[0] ?? '');
    }

    private function extractClassListById(string $html, string $id): array
    {
        $tag = $this->extractTagById($html, $id);

        $classFound = preg_match('/\bclass="([^"]*)"/i', $tag, $classMatches);

        if ($classFound !== 1) {
            $this->fail(sprintf('Element with id "%s" does not include a class attribute.', $id));
        }

        $classList = preg_split('/\s+/', trim((string) ($classMatches[1] ?? '')));

        return array_values(array_filter($classList, fn ($className) => $className !== ''));
    }

    private function createChairpersonContext(): array
    {
        $department = Department::create([
            'department_code' => 'SBISM',
            'department_description' => 'School of Business, Information Science and Management',
            'is_deleted' => false,
        ]);

        $course = Course::create([
            'course_code' => 'BSIT',
            'course_description' => 'Bachelor of Science in Information Technology',
            'department_id' => $department->id,
            'is_deleted' => false,
        ]);

        $chairperson = User::factory()->create([
            'role' => 1,
            'department_id' => $department->id,
            'course_id' => $course->id,
            'is_active' => true,
        ]);

        $period = AcademicPeriod::create([
            'academic_year' => '2025-2026',
            'semester' => '1st',
            'is_deleted' => false,
        ]);

        return [$chairperson, $period, $course, $department];
    }
}
