<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReviewStudent extends Model
{
    use HasFactory;

    protected $fillable = [
        'instructor_id',
        'list_name',
        'first_name',
        'middle_name',
        'last_name',
        'year_level',
        'course_id',
        'subject_id',
        'is_confirmed',
    ];

    protected $casts = [
        'is_confirmed' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * Instructor who uploaded this review entry.
     */
    public function instructor()
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    /**
     * Related course.
     */
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Related subject.
     */
    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    /**
     * Get full name of the student.
     */
    public function getFullNameAttribute(): string
    {
        $middle = $this->middle_name ? " {$this->middle_name}" : '';

        return trim("{$this->first_name}{$middle} {$this->last_name}");
    }

    /**
     * Get formatted year level (e.g., "1st Year").
     */
    public function getFormattedYearLevelAttribute(): string
    {
        return match ($this->year_level) {
            1 => '1st Year',
            2 => '2nd Year',
            3 => '3rd Year',
            4 => '4th Year',
            5 => '5th Year',
            default => 'N/A',
        };
    }
}
