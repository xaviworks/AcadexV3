<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Announcement extends Model
{
    protected $fillable = [
        'title',
        'message',
        'type',
        'priority',
        'target_roles',
        'start_date',
        'end_date',
        'is_active',
        'is_dismissible',
        'show_once',
        'created_by',
    ];

    protected $casts = [
        'target_roles' => 'array',
        'is_active' => 'boolean',
        'is_dismissible' => 'boolean',
        'show_once' => 'boolean',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    /**
     * Get the user who created this announcement
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Users who have viewed this announcement
     */
    public function viewedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'announcement_views')
            ->withTimestamps()
            ->withPivot('viewed_at');
    }

    /**
     * Check if announcement is currently active based on dates
     */
    public function isCurrentlyActive(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $now = now();

        if ($this->start_date && $now->lt($this->start_date)) {
            return false;
        }

        if ($this->end_date && $now->gt($this->end_date)) {
            return false;
        }

        return true;
    }

    /**
     * Check if announcement should be shown to a specific user
     */
    public function shouldShowToUser(User $user): bool
    {
        if (!$this->isCurrentlyActive()) {
            return false;
        }

        // Don't show announcements to the admin who created them
        if ($user->role === 3 && $this->created_by === $user->id) {
            return false;
        }

        // Check if user has already viewed it (for show_once announcements)
        if ($this->show_once && $this->viewedBy()->where('user_id', $user->id)->exists()) {
            return false;
        }

        // Check role targeting
        if ($this->target_roles !== null && !in_array($user->role, $this->target_roles)) {
            return false;
        }

        return true;
    }

    /**
     * Mark announcement as viewed by user
     */
    public function markAsViewedBy(User $user): void
    {
        if (!$this->viewedBy()->where('user_id', $user->id)->exists()) {
            $this->viewedBy()->attach($user->id, ['viewed_at' => now()]);
        }
    }

    /**
     * Scope: Active announcements
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Current announcements (within date range)
     */
    public function scopeCurrent($query)
    {
        $now = now();
        return $query->where('is_active', true)
            ->where(function ($q) use ($now) {
                $q->whereNull('start_date')->orWhere('start_date', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('end_date')->orWhere('end_date', '>=', $now);
            });
    }

    /**
     * Scope: For specific role
     */
    public function scopeForRole($query, int $role)
    {
        return $query->where(function ($q) use ($role) {
            $q->whereNull('target_roles')
              ->orWhereJsonContains('target_roles', $role);
        });
    }

    /**
     * Scope: Order by priority (urgent > high > normal > low)
     */
    public function scopeOrderByPriority($query, string $direction = 'desc')
    {
        // FIELD returns position: urgent=1, high=2, normal=3, low=4
        // For DESC (urgent first), we want ASC on FIELD
        // For ASC (low first), we want DESC on FIELD
        $order = $direction === 'desc' ? 'ASC' : 'DESC';
        return $query->orderByRaw("FIELD(priority, 'urgent', 'high', 'normal', 'low') {$order}");
    }

    /**
     * Get priority as numeric value for sorting
     */
    public function getPriorityOrderAttribute(): int
    {
        return match($this->priority) {
            'urgent' => 4,
            'high' => 3,
            'normal' => 2,
            'low' => 1,
            default => 0,
        };
    }
}
