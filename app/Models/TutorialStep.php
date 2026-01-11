<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TutorialStep extends Model
{
    protected $fillable = [
        'tutorial_id',
        'step_order',
        'title',
        'content',
        'target_selector',
        'position',
        'is_optional',
        'requires_data',
        'screenshot',
    ];

    protected $casts = [
        'step_order' => 'integer',
        'is_optional' => 'boolean',
        'requires_data' => 'boolean',
    ];

    /**
     * Get the tutorial that owns this step
     */
    public function tutorial(): BelongsTo
    {
        return $this->belongsTo(Tutorial::class);
    }
}
