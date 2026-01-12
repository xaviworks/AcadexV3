<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BatchDraftStudent extends Model
{
    use HasFactory;

    protected $fillable = [
        'batch_draft_id',
        'student_id',
        'first_name',
        'middle_name',
        'last_name',
        'year_level',
        'course_id',
    ];

    protected $casts = [
        'year_level' => 'integer',
    ];

    /**
     * Relationships
     */
    public function batchDraft()
    {
        return $this->belongsTo(BatchDraft::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Get full name
     */
    public function getFullNameAttribute()
    {
        $parts = array_filter([$this->first_name, $this->middle_name, $this->last_name]);
        return implode(' ', $parts);
    }
}
