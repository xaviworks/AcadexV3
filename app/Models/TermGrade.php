<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TermGrade extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id', 'subject_id', 'academic_period_id', 'term_id',
        'term_grade', 'is_deleted', 'created_by', 'updated_by',
    ];
}
