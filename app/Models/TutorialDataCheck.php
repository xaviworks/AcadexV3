<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TutorialDataCheck extends Model
{
    protected $fillable = [
        'tutorial_id',
        'selector',
        'empty_selectors',
        'entity_name',
        'add_button_selector',
        'no_add_button',
    ];

    protected $casts = [
        'empty_selectors' => 'array',
        'no_add_button' => 'boolean',
    ];

    /**
     * Get the tutorial that owns this data check
     */
    public function tutorial(): BelongsTo
    {
        return $this->belongsTo(Tutorial::class);
    }
}
