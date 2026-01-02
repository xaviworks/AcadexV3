<?php

namespace App\Models\MongoDB;

/**
 * Grade Notification Model (MongoDB)
 * 
 * Stores notifications about grade updates.
 */
class GradeNotification extends VersionedMongoModel
{
    protected $collection = 'grade_notifications';
    
    protected int $currentSchemaVersion = 1;

    protected $fillable = [
        'user_id',
        'student_id',
        'subject_id',
        'academic_period_id',
        'type',
        'message',
        'is_read',
        'read_at',
        'metadata',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'student_id' => 'integer',
        'subject_id' => 'integer',
        'academic_period_id' => 'integer',
        'is_read' => 'boolean',
        'read_at' => 'datetime',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Mark notification as read
     */
    public function markAsRead(): void
    {
        $this->is_read = true;
        $this->read_at = now();
        $this->save();
    }

    /**
     * Scope for unread notifications
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }
}
