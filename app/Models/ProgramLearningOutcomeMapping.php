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
        'co_code',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function programLearningOutcome()
    {
        return $this->belongsTo(ProgramLearningOutcome::class);
    }
}
