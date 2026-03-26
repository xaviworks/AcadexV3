<?php

namespace Tests\Unit;

use App\Support\Grades\FormulaStructure;
use App\Traits\ActivityManagementTrait;
use PHPUnit\Framework\TestCase;

class ActivityAlignmentStatusTest extends TestCase
{
    public function test_quiz_and_ocr_require_three_minimum_when_weighted(): void
    {
        $helper = new class {
            use ActivityManagementTrait;

            public function minRequired(array $component): int
            {
                return $this->determineMinimumRequiredAssessments($component);
            }

            public function status(int $actualCount, int $minRequired, ?int $maxAllowed): string
            {
                return $this->determineAlignmentStatus($actualCount, $minRequired, $maxAllowed);
            }
        };

        $quizComponent = [
            'activity_type' => 'quiz',
            'base_type' => FormulaStructure::baseActivityType('quiz'),
            'relative_weight_percent' => 40,
            'max_assessments' => 5,
        ];

        $ocrComponent = [
            'activity_type' => 'ocr',
            'base_type' => FormulaStructure::baseActivityType('ocr'),
            'relative_weight_percent' => 20,
            'max_assessments' => 5,
        ];

        $this->assertSame(3, $helper->minRequired($quizComponent));
        $this->assertSame('missing', $helper->status(2, $helper->minRequired($quizComponent), 5));

        $this->assertSame(3, $helper->minRequired($ocrComponent));
        $this->assertSame('missing', $helper->status(0, $helper->minRequired($ocrComponent), 5));
    }

    public function test_exam_with_one_count_is_ok_not_warning(): void
    {
        $helper = new class {
            use ActivityManagementTrait;

            public function minRequired(array $component): int
            {
                return $this->determineMinimumRequiredAssessments($component);
            }

            public function status(int $actualCount, int $minRequired, ?int $maxAllowed): string
            {
                return $this->determineAlignmentStatus($actualCount, $minRequired, $maxAllowed);
            }
        };

        $examComponent = [
            'activity_type' => 'exam',
            'base_type' => FormulaStructure::baseActivityType('exam'),
            'relative_weight_percent' => 40,
            'max_assessments' => 1,
        ];

        $minRequired = $helper->minRequired($examComponent);

        $this->assertSame(1, $minRequired);
        $this->assertSame('ok', $helper->status(1, $minRequired, 1));
    }

    public function test_status_is_exceeds_when_actual_count_is_above_max(): void
    {
        $helper = new class {
            use ActivityManagementTrait;

            public function status(int $actualCount, int $minRequired, ?int $maxAllowed): string
            {
                return $this->determineAlignmentStatus($actualCount, $minRequired, $maxAllowed);
            }
        };

        $this->assertSame('exceeds', $helper->status(6, 3, 5));
    }
}
