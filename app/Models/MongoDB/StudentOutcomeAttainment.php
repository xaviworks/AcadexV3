<?php

namespace App\Models\MongoDB;

/**
 * Student Outcome Attainment Model (MongoDB)
 * 
 * Stores course outcome assessments per student.
 */
class StudentOutcomeAttainment extends VersionedMongoModel
{
    protected $collection = 'student_outcome_attainments';
    
    protected int $currentSchemaVersion = 1;

    protected $fillable = [
        'student_id',
        'subject_id',
        'academic_period_id',
        'term',
        'course_outcome_id',
        'course_outcome_code',
        'score',
        'max_score',
        'percentage',
        'semester_total',
    ];

    protected $casts = [
        'student_id' => 'integer',
        'subject_id' => 'integer',
        'academic_period_id' => 'integer',
        'course_outcome_id' => 'integer',
        'score' => 'integer',
        'max_score' => 'integer',
        'percentage' => 'decimal:2',
        'semester_total' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
