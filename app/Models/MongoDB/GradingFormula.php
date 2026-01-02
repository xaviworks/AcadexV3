<?php

namespace App\Models\MongoDB;

/**
 * Grading Formula Model (MongoDB)
 * 
 * Stores grading computation formulas with flexible structure.
 */
class GradingFormula extends VersionedMongoModel
{
    protected $collection = 'grading_formulas';
    
    protected int $currentSchemaVersion = 1;

    protected $fillable = [
        'scope',
        'department_id',
        'course_id',
        'subject_id',
        'semester',
        'academic_period_id',
        'structure',
        'final_computation',
        'version',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'department_id' => 'integer',
        'course_id' => 'integer',
        'subject_id' => 'integer',
        'academic_period_id' => 'integer',
        'structure' => 'array',
        'final_computation' => 'array',
        'version' => 'integer',
        'is_active' => 'boolean',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
