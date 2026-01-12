<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BatchDraft extends Model
{
    use HasFactory;

    protected $fillable = [
        'batch_name',
        'description',
        'academic_period_id',
        'course_id',
        'year_level',
        'co_template_id',
        'created_by',
        'is_active',
        'is_deleted',
    ];

    protected $casts = [
        'year_level' => 'integer',
        'is_active' => 'boolean',
        'is_deleted' => 'boolean',
    ];

    /**
     * Relationships
     */
    public function academicPeriod()
    {
        return $this->belongsTo(AcademicPeriod::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function coTemplate()
    {
        return $this->belongsTo(CourseOutcomeTemplate::class, 'co_template_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function students()
    {
        return $this->hasMany(BatchDraftStudent::class);
    }

    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'batch_draft_subjects')
                    ->withPivot('configuration_applied')
                    ->withTimestamps();
    }

    public function batchDraftSubjects()
    {
        return $this->hasMany(BatchDraftSubject::class);
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->where('is_deleted', false);
    }

    public function scopeForCourse($query, $courseId)
    {
        return $query->where('course_id', $courseId);
    }

    public function scopeForAcademicPeriod($query, $academicPeriodId)
    {
        return $query->where('academic_period_id', $academicPeriodId);
    }
}
