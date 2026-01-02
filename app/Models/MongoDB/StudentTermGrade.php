<?php

namespace App\Models\MongoDB;

/**
 * Student Term Grade Model (MongoDB)
 * 
 * Stores term-level grades with component breakdowns.
 * 
 * Schema Version History:
 * - v1: Initial schema
 * - v2: Added component_breakdown and audit_trail
 */
class StudentTermGrade extends VersionedMongoModel
{
    protected $collection = 'student_term_grades';
    
    /**
     * Current schema version
     */
    protected int $currentSchemaVersion = 2;

    /**
     * Fillable attributes
     */
    protected $fillable = [
        'student_id',
        'subject_id',
        'academic_period_id',
        'term_id',
        'term_name',
        'term_grade',
        'component_breakdown',
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
        'term_id' => 'integer',
        'term_grade' => 'decimal:2',
        'component_breakdown' => 'array',
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
            // Version 2: Add component_breakdown and audit_trail
            2 => function ($model) {
                if (!isset($model->component_breakdown)) {
                    $model->component_breakdown = [
                        'quizzes_average' => 0,
                        'ocr_average' => 0,
                        'exam_score' => 0,
                    ];
                }

                if (!isset($model->audit_trail)) {
                    $model->audit_trail = [];
                }
            },
        ];
    }

    /**
     * Update term grade with component breakdown
     */
    public function updateGrade(
        float $grade,
        array $breakdown = []
    ): void {
        $oldGrade = $this->term_grade;
        
        $this->term_grade = $grade;
        $this->component_breakdown = array_merge(
            $this->component_breakdown ?? [],
            $breakdown
        );
        $this->updated_by = auth()->id();

        $this->addAuditTrail('grade_updated', $oldGrade, $grade);
    }
}
