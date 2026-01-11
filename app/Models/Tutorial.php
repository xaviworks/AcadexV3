<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Tutorial extends Model
{
    protected $fillable = [
        'role',
        'page_identifier',
        'title',
        'description',
        'priority',
        'created_by',
    ];

    protected $casts = [
        'priority' => 'integer',
    ];

    /**
     * Get the user who created this tutorial
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get all steps for this tutorial
     */
    public function steps(): HasMany
    {
        return $this->hasMany(TutorialStep::class)->orderBy('step_order');
    }

    /**
     * Get data check configuration for this tutorial
     */
    public function dataCheck(): HasOne
    {
        return $this->hasOne(TutorialDataCheck::class);
    }

    /**
     * Scope query to active tutorials only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope query to specific role
     */
    public function scopeForRole($query, string $role)
    {
        return $query->where('role', $role);
    }

    /**
     * Scope query to specific page
     */
    public function scopeForPage($query, string $pageIdentifier)
    {
        return $query->where('page_identifier', $pageIdentifier);
    }

    /**
     * Convert tutorial to JavaScript-compatible format
     */
    public function toJavaScriptFormat(): array
    {
        $data = [
            'id' => $this->page_identifier,
            'title' => $this->title,
            'description' => $this->description,
            'steps' => $this->steps->map(function ($step) {
                return [
                    'target' => $step->target_selector,
                    'title' => $step->title,
                    'content' => $step->content,
                    'position' => $step->position,
                    'optional' => $step->is_optional,
                    'requiresData' => $step->requires_data,
                ];
            })->toArray(),
        ];

        // Add data check if exists
        if ($this->dataCheck) {
            $data['tableDataCheck'] = [
                'selector' => $this->dataCheck->selector,
                'emptySelectors' => $this->dataCheck->empty_selectors,
                'entityName' => $this->dataCheck->entity_name,
                'addButtonSelector' => $this->dataCheck->add_button_selector,
                'noAddButton' => $this->dataCheck->no_add_button,
            ];
        }

        return $data;
    }
}
