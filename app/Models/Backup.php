<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $name
 * @property string $type
 * @property string $filename
 * @property string $path
 * @property int $size
 * @property array|null $tables
 * @property string $status
 * @property string|null $notes
 * @property string|null $error_message
 * @property int $created_by
 * @property \Carbon\Carbon|null $completed_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read User $creator
 * @property-read string $size_formatted
 * @property-read string $status_badge
 */
class Backup extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'filename',
        'path',
        'size',
        'tables',
        'status',
        'notes',
        'error_message',
        'created_by',
        'completed_at',
    ];

    protected $casts = [
        'tables' => 'array',
        'size' => 'integer',
        'completed_at' => 'datetime',
    ];

    // Status constants
    public const STATUS_PENDING = 'pending';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';

    // Type constants
    public const TYPE_FULL = 'full';
    public const TYPE_SELECTIVE = 'selective';
    public const TYPE_CONFIG = 'config';

    /**
     * Get the user who created this backup.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get human-readable file size.
     */
    public function getSizeFormattedAttribute(): string
    {
        $bytes = $this->size;
        
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        }
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        }
        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }
        
        return $bytes . ' bytes';
    }

    /**
     * Get status badge HTML class.
     */
    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_COMPLETED => 'bg-success',
            self::STATUS_PENDING => 'bg-warning',
            self::STATUS_FAILED => 'bg-danger',
            default => 'bg-secondary',
        };
    }

    /**
     * Get type label.
     */
    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            self::TYPE_FULL => 'Full Database',
            self::TYPE_SELECTIVE => 'Selective Tables',
            self::TYPE_CONFIG => 'Configuration Only',
            default => ucfirst($this->type),
        };
    }

    /**
     * Scope for completed backups.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Scope for pending backups.
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Check if the backup file exists on disk.
     */
    public function fileExists(): bool
    {
        return file_exists(storage_path($this->path));
    }

    /**
     * Get the full path to the backup file.
     */
    public function getFullPath(): string
    {
        return storage_path($this->path);
    }
}
