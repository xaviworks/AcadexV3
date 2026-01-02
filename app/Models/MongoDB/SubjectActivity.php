<?php

namespace App\Models\MongoDB;

/**
 * Subject Activity Model (MongoDB)
 * 
 * Stores activities (quizzes, exams, etc.) for subjects.
 */
class SubjectActivity extends VersionedMongoModel
{
    protected $collection = 'subject_activities';
    
    protected int $currentSchemaVersion = 1;

    protected $fillable = [
        'subject_id',
        'academic_period_id',
        'term',
        'type',
        'title',
        'number_of_items',
        'course_outcome_id',
        'weight',
        'is_deleted',
        'created_by',
        'updated_by',
        'metadata',
    ];

    protected $casts = [
        'subject_id' => 'integer',
        'academic_period_id' => 'integer',
        'number_of_items' => 'integer',
        'course_outcome_id' => 'integer',
        'weight' => 'decimal:2',
        'is_deleted' => 'boolean',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
