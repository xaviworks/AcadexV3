<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProgramLearningOutcomeMapping extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'program_learning_outcome_id',
        'course_outcome_id',
        'co_code',
    ];

    protected $casts = [
        'course_outcome_id' => 'integer',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function programLearningOutcome()
    {
        return $this->belongsTo(ProgramLearningOutcome::class);
    }

    public function courseOutcome()
    {
        return $this->belongsTo(CourseOutcomes::class, 'course_outcome_id');
    }
}
