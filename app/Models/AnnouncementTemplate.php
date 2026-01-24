<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnnouncementTemplate extends Model
{
    protected $fillable = [
        'name',
        'key',
        'title',
        'message',
        'type',
        'priority',
        'icon',
        'description',
        'is_active',
        'order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer',
    ];

    /**
     * Scope: Active templates only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Ordered templates
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order')->orderBy('name');
    }
}
