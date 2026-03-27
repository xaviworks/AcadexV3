<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubjectAttainmentLevel extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject_id',
        'level_3',
        'level_2',
        'level_1',
    ];

    protected $casts = [
        'level_3' => 'float',
        'level_2' => 'float',
        'level_1' => 'float',
    ];

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }
}
