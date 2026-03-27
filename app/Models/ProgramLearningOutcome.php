<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProgramLearningOutcome extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'plo_code',
        'title',
        'display_order',
        'is_active',
        'is_deleted',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_deleted' => 'boolean',
        'display_order' => 'integer',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function mappings()
    {
        return $this->hasMany(ProgramLearningOutcomeMapping::class)
            ->orderBy('co_code');
    }
}
