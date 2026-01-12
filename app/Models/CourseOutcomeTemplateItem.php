<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseOutcomeTemplateItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'template_id',
        'co_code',
        'description',
        'order',
    ];

    protected $casts = [
        'order' => 'integer',
    ];

    /**
     * Relationships
     */
    public function template()
    {
        return $this->belongsTo(CourseOutcomeTemplate::class, 'template_id');
    }
}
