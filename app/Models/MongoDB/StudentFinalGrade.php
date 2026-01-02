<?php

namespace App\Models\MongoDB;

/**
 * Student Final Grade Model (MongoDB)
 * 
 * Stores final grades with term breakdowns and audit trails.
 * 
 * Schema Version History:
 * - v1: Initial schema
 * - v2: Added term_grades embedded document and letter_grade
 * - v3: Enhanced audit_trail with computation details
 */
class StudentFinalGrade extends VersionedMongoModel
{
    protected $collection = 'student_final_grades';
    
    /**
     * Current schema version
     */
    protected int $currentSchemaVersion = 3;

    /**
     * Fillable attributes
     */
    protected $fillable = [
        'student_id',
        'subject_id',
        'academic_period_id',
        'final_grade',
        'letter_grade',
        'remarks',
        'notes',
        'term_grades',
        'is_deleted',
        'created_by',
        'updated_by',
        'audit_trail',
    ];

    /**
     * Attribute casting
     */
    protected $casts = [
        'student_id' => 'integer',
        'subject_id' => 'integer',
        'academic_period_id' => 'integer',
        'final_grade' => 'decimal:2',
        'term_grades' => 'array',
        'is_deleted' => 'boolean',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'audit_trail' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Schema transformations
     */
    protected function getSchemaTransformations(): array
    {
        return [
            // Version 2: Add term_grades and letter_grade
            2 => function ($model) {
                if (!isset($model->term_grades)) {
                    $model->term_grades = [
                        'prelim' => 0,
                        'midterm' => 0,
                        'prefinal' => 0,
                        'final' => 0,
                    ];
                }

                if (!isset($model->letter_grade) && isset($model->final_grade)) {
                    // Calculate letter grade inline to avoid scope issues
                    $grade = $model->final_grade;
                    if ($grade >= 97) $model->letter_grade = 'A+';
                    elseif ($grade >= 93) $model->letter_grade = 'A';
                    elseif ($grade >= 90) $model->letter_grade = 'A-';
                    elseif ($grade >= 87) $model->letter_grade = 'B+';
                    elseif ($grade >= 83) $model->letter_grade = 'B';
                    elseif ($grade >= 80) $model->letter_grade = 'B-';
                    elseif ($grade >= 77) $model->letter_grade = 'C+';
                    elseif ($grade >= 75) $model->letter_grade = 'C';
                    else $model->letter_grade = 'F';
                }
            },

            // Version 3: Enhanced audit trail
            3 => function ($model) {
                if (!isset($model->audit_trail)) {
                    $model->audit_trail = [];
                }

                // Add initial computation entry if not exists
                if (empty($model->audit_trail)) {
                    $model->audit_trail[] = [
                        'action' => 'computed',
                        'user_id' => $model->created_by,
                        'timestamp' => $model->created_at ?? now(),
                        'computation_formula' => 'weighted_average',
                        'grade_value' => $model->final_grade,
                    ];
                }
            },
        ];
    }

    /**
     * Calculate letter grade from numeric grade
     */
    protected function calculateLetterGrade(?float $grade): string
    {
        if ($grade === null) return 'N/A';
        
        if ($grade >= 97) return 'A+';
        if ($grade >= 93) return 'A';
        if ($grade >= 90) return 'A-';
        if ($grade >= 87) return 'B+';
        if ($grade >= 83) return 'B';
        if ($grade >= 80) return 'B-';
        if ($grade >= 77) return 'C+';
        if ($grade >= 75) return 'C';
        return 'F';
    }

    /**
     * Update final grade with term breakdown
     */
    public function updateFinalGrade(
        float $finalGrade,
        array $termGrades,
        string $remarks,
        string $computationFormula = 'weighted_average'
    ): void {
        $oldGrade = $this->final_grade;
        
        $this->final_grade = $finalGrade;
        $this->letter_grade = $this->calculateLetterGrade($finalGrade);
        $this->term_grades = $termGrades;
        $this->remarks = $remarks;
        $this->updated_by = auth()->id();

        $this->addAuditTrail('grade_computed', $oldGrade, $finalGrade);
        
        // Add computation details to audit
        $auditTrail = $this->audit_trail ?? [];
        $auditTrail[count($auditTrail) - 1]['computation_formula'] = $computationFormula;
        $auditTrail[count($auditTrail) - 1]['term_grades'] = $termGrades;
        $this->audit_trail = $auditTrail;
    }

    /**
     * Check if student passed
     */
    public function hasPassed(): bool
    {
        return $this->remarks === 'Passed';
    }

    /**
     * Get grade summary
     */
    public function getSummary(): array
    {
        return [
            'final_grade' => $this->final_grade,
            'letter_grade' => $this->letter_grade,
            'remarks' => $this->remarks,
            'term_breakdown' => $this->term_grades,
            'passed' => $this->hasPassed(),
        ];
    }
}
