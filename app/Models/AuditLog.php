<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property int $id
 * @property int|null $user_id
 * @property string $auditable_type
 * @property int $auditable_id
 * @property string $event
 * @property array|null $old_values
 * @property array|null $new_values
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property array|null $metadata
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read User|null $user
 * @property-read Model $auditable
 */
class AuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'auditable_type',
        'auditable_id',
        'event',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'metadata',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'metadata' => 'array',
    ];

    // Event constants
    public const EVENT_CREATED = 'created';
    public const EVENT_UPDATED = 'updated';
    public const EVENT_DELETED = 'deleted';
    public const EVENT_RESTORED = 'restored';
    public const EVENT_BULK_UPDATED = 'bulk_updated';
    public const EVENT_BULK_DELETED = 'bulk_deleted';

    /**
     * Get the user who performed the action.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the auditable model.
     */
    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get a human-readable model name.
     */
    public function getModelNameAttribute(): string
    {
        $class = class_basename($this->auditable_type);
        return preg_replace('/(?<!^)[A-Z]/', ' $0', $class);
    }

    /**
     * Get the event badge class.
     */
    public function getEventBadgeAttribute(): string
    {
        return match ($this->event) {
            self::EVENT_CREATED => 'bg-success',
            self::EVENT_UPDATED, self::EVENT_BULK_UPDATED => 'bg-info',
            self::EVENT_DELETED, self::EVENT_BULK_DELETED => 'bg-danger',
            self::EVENT_RESTORED => 'bg-primary',
            default => 'bg-secondary',
        };
    }

    /**
     * Get the event icon.
     */
    public function getEventIconAttribute(): string
    {
        return match ($this->event) {
            self::EVENT_CREATED => 'bi-plus-circle',
            self::EVENT_UPDATED, self::EVENT_BULK_UPDATED => 'bi-pencil',
            self::EVENT_DELETED, self::EVENT_BULK_DELETED => 'bi-trash',
            self::EVENT_RESTORED => 'bi-arrow-counterclockwise',
            default => 'bi-circle',
        };
    }

    /**
     * Get the changed fields between old and new values.
     */
    public function getChangedFieldsAttribute(): array
    {
        if (! $this->old_values || ! $this->new_values) {
            return [];
        }

        $changed = [];
        foreach ($this->new_values as $key => $newValue) {
            $oldValue = $this->old_values[$key] ?? null;
            if ($oldValue !== $newValue) {
                $changed[$key] = [
                    'old' => $oldValue,
                    'new' => $newValue,
                ];
            }
        }

        return $changed;
    }

    /**
     * Scope for a specific model type.
     */
    public function scopeForModel($query, string $modelClass)
    {
        return $query->where('auditable_type', $modelClass);
    }

    /**
     * Scope for a specific model instance.
     */
    public function scopeForInstance($query, string $modelClass, int $modelId)
    {
        return $query->where('auditable_type', $modelClass)
                     ->where('auditable_id', $modelId);
    }

    /**
     * Scope for a specific user.
     */
    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope for a specific event type.
     */
    public function scopeForEvent($query, string $event)
    {
        return $query->where('event', $event);
    }

    /**
     * Scope for date range.
     */
    public function scopeDateRange($query, $from = null, $to = null)
    {
        if ($from) {
            $query->where('created_at', '>=', $from);
        }
        if ($to) {
            $query->where('created_at', '<=', $to);
        }
        return $query;
    }

    /**
     * Create an audit log entry.
     */
    public static function log(
        Model $model,
        string $event,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?array $metadata = null
    ): self {
        return self::create([
            'user_id' => auth()->id(),
            'auditable_type' => get_class($model),
            'auditable_id' => $model->getKey(),
            'event' => $event,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'metadata' => $metadata,
        ]);
    }
}
