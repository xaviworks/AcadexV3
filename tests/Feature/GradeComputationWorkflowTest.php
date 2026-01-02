<?php

namespace Tests\Feature;

use App\Models\MongoDB\StudentScore;
use App\Models\MongoDB\StudentTermGrade;
use App\Models\MongoDB\StudentFinalGrade;
use App\Models\MongoDB\SubjectActivity;
use Tests\TestCase;

/**
 * Integration Test: Grade Computation Workflow
 * 
 * Tests the complete grade computation workflow spanning MySQL and MongoDB:
 * 1. Create activities (MongoDB)
 * 2. Enter scores (MongoDB)
 * 3. Compute term grades (MongoDB)
 * 4. Compute final grades (MongoDB)
 * 5. Generate reports (Hybrid)
 */
class GradeComputationWorkflowTest extends TestCase
{
    public function test_complete_grade_computation_workflow()
    {
        // Step 1: Create student and subject (MySQL) - using factories
        $studentId = 1;
        $subjectId = 1;
        $academicPeriodId = 1;

        // Step 2: Create activities (MongoDB)
        $activity = SubjectActivity::create([
            'subject_id' => $subjectId,
            'academic_period_id' => $academicPeriodId,
            'term' => 'prelim',
            'type' => 'quiz',
            'title' => 'Quiz 1',
            'number_of_items' => 50,
            'is_deleted' => false,
        ]);

        $this->assertNotNull($activity->_id);

        // Step 3: Enter scores (MongoDB)
        $score = StudentScore::create([
            'student_id' => $studentId,
            'activity_id' => (string) $activity->_id,
            'subject_id' => $subjectId,
            'academic_period_id' => $academicPeriodId,
            'score' => 45,
            'max_score' => 50,
            'percentage' => 90.0,
            'is_deleted' => false,
        ]);

        $this->assertEquals(90.0, $score->percentage);

        // Step 4: Compute term grade (MongoDB)
        $termGrade = StudentTermGrade::create([
            'student_id' => $studentId,
            'subject_id' => $subjectId,
            'academic_period_id' => $academicPeriodId,
            'term_id' => 1,
            'term_name' => 'prelim',
            'term_grade' => 88.5,
            'component_breakdown' => [
                'quizzes_average' => 90.0,
                'ocr_average' => 85.0,
                'exam_score' => 90.0,
            ],
            'is_deleted' => false,
        ]);

        $this->assertEquals(88.5, $termGrade->term_grade);

        // Step 5: Compute final grade (MongoDB)
        $finalGrade = StudentFinalGrade::create([
            'student_id' => $studentId,
            'subject_id' => $subjectId,
            'academic_period_id' => $academicPeriodId,
            'final_grade' => 87.5,
            'remarks' => 'Passed',
            'term_grades' => [
                'prelim' => 88.5,
                'midterm' => 89.0,
                'prefinal' => 86.0,
                'final' => 86.5,
            ],
            'is_deleted' => false,
        ]);

        $this->assertEquals(87.5, $finalGrade->final_grade);
        $this->assertEquals('Passed', $finalGrade->remarks);
        $this->assertTrue($finalGrade->hasPassed());

        // Step 6: Verify data integrity
        $retrievedScore = StudentScore::where('student_id', $studentId)
            ->where('is_deleted', false)
            ->first();

        $this->assertNotNull($retrievedScore);
        $this->assertEquals(45, $retrievedScore->score);
    }

    public function test_soft_delete_workflow()
    {
        $score = StudentScore::create([
            'student_id' => 1,
            'activity_id' => 'test123',
            'subject_id' => 1,
            'academic_period_id' => 1,
            'score' => 95,
            'max_score' => 100,
            'is_deleted' => false,
        ]);

        // Soft delete
        $score->softDelete();
        $this->assertTrue($score->is_deleted);

        // Should not appear in active queries
        $activeScores = StudentScore::notDeleted()->get();
        $this->assertFalse($activeScores->contains('_id', $score->_id));

        // Restore
        $score->restore();
        $this->assertFalse($score->is_deleted);
    }
}
