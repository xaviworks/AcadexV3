<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseOutcomeTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'template_name',
        'description',
        'created_by',
        'course_id',
        'is_universal',
        'is_active',
        'is_deleted',
    ];

    protected $casts = [
        'is_universal' => 'boolean',
        'is_active' => 'boolean',
        'is_deleted' => 'boolean',
    ];

    /**
     * Relationships
     */
    public function items()
    {
        return $this->hasMany(CourseOutcomeTemplateItem::class, 'template_id')->orderBy('order');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function batchDrafts()
    {
        return $this->hasMany(BatchDraft::class, 'co_template_id');
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
        return $query->where(function ($q) use ($courseId) {
            $q->where('course_id', $courseId)
              ->orWhere('is_universal', true);
        });
    }

    public function scopeUniversal($query)
    {
        return $query->where('is_universal', true);
    }
}
