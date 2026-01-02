<?php

namespace App\Models\MongoDB;

/**
 * Student Score Model (MongoDB)
 * 
 * Stores individual activity scores for students with flexible schema.
 * 
 * Schema Version History:
 * - v1: Initial schema with basic fields
 * - v2: Added percentage field and metadata object
 * - v3: Added max_score field for better score tracking
 */
class StudentScore extends VersionedMongoModel
{
    protected $collection = 'student_scores';
    
    /**
     * Current schema version
     */
    protected int $currentSchemaVersion = 3;

    /**
     * Fillable attributes
     */
    protected $fillable = [
        'student_id',
        'activity_id',
        'subject_id',
        'academic_period_id',
        'score',
        'max_score',
        'percentage',
        'is_deleted',
        'created_by',
        'updated_by',
        'metadata',
    ];

    /**
     * Attribute casting
     */
    protected $casts = [
        'student_id' => 'integer',
        'subject_id' => 'integer',
        'academic_period_id' => 'integer',
        'score' => 'decimal:2',
        'max_score' => 'integer',
        'percentage' => 'decimal:2',
        'is_deleted' => 'boolean',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Schema transformations for version migrations
     */
    protected function getSchemaTransformations(): array
    {
        return [
            // Version 2: Add percentage and metadata
            2 => function ($model) {
                // Calculate percentage if score exists but percentage doesn't
                if (isset($model->score) && !isset($model->percentage)) {
                    $maxScore = $model->max_score ?? 100;
                    $model->percentage = $maxScore > 0 ? ($model->score / $maxScore) * 100 : 0;
                }

                // Initialize metadata if not exists
                if (!isset($model->metadata)) {
                    $model->metadata = [
                        'submitted_late' => false,
                        'excuse_reason' => null,
                        'ip_address' => null,
                    ];
                }
            },

            // Version 3: Add max_score field
            3 => function ($model) {
                // If max_score doesn't exist, try to get from activity or default to 100
                if (!isset($model->max_score)) {
                    $activity = SubjectActivity::find($model->activity_id);
                    $model->max_score = $activity?->number_of_items ?? 100;
                }

                // Recalculate percentage with correct max_score
                if (isset($model->score) && isset($model->max_score) && $model->max_score > 0) {
                    $model->percentage = ($model->score / $model->max_score) * 100;
                }
            },
        ];
    }

    /**
     * Update score and automatically calculate percentage
     */
    public function updateScore(float $score, ?float $maxScore = null): void
    {
        $oldScore = $this->score;
        
        $this->score = $score;
        
        if ($maxScore !== null) {
            $this->max_score = $maxScore;
        }

        // Prevent division by zero
        if ($this->max_score > 0) {
            $this->percentage = ($this->score / $this->max_score) * 100;
        } else {
            $this->percentage = 0;
        }
        
        $this->updated_by = auth()->id();

        $this->addAuditTrail('score_updated', $oldScore, $score);
    }

    /**
     * Mark as late submission
     */
    public function markLateSubmission(string $reason = null): void
    {
        $this->addMetadata('submitted_late', true);
        if ($reason) {
            $this->addMetadata('excuse_reason', $reason);
        }
        $this->addAuditTrail('marked_late');
    }

    /**
     * Get formatted score display
     */
    public function getFormattedScore(): string
    {
        return "{$this->score}/{$this->max_score} ({$this->percentage}%)";
    }
}
