<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * User notification preferences model.
 * 
 * @property int $id
 * @property int $user_id
 * @property array|null $enabled_types
 * @property bool $in_app_enabled
 * @property bool $email_enabled
 * @property bool $push_enabled
 * @property string|null $quiet_start
 * @property string|null $quiet_end
 * @property-read User $user
 */
class NotificationPreference extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'enabled_types',
        'in_app_enabled',
        'email_enabled',
        'push_enabled',
        'quiet_start',
        'quiet_end',
    ];

    protected $casts = [
        'enabled_types' => 'array',
        'in_app_enabled' => 'boolean',
        'email_enabled' => 'boolean',
        'push_enabled' => 'boolean',
        'quiet_start' => 'datetime:H:i',
        'quiet_end' => 'datetime:H:i',
    ];

    /**
     * Get the user that owns these preferences.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if a specific notification type is enabled.
     */
    public function isTypeEnabled(string $type): bool
    {
        if ($this->enabled_types === null) {
            return true; // All types enabled by default
        }

        return in_array($type, $this->enabled_types, true);
    }

    /**
     * Check if currently in quiet hours.
     */
    public function isInQuietHours(): bool
    {
        if (!$this->quiet_start || !$this->quiet_end) {
            return false;
        }

        $now = now()->format('H:i:s');
        $start = $this->quiet_start;
        $end = $this->quiet_end;

        // Handle overnight quiet hours (e.g., 22:00 to 06:00)
        if ($start > $end) {
            return $now >= $start || $now <= $end;
        }

        return $now >= $start && $now <= $end;
    }

    /**
     * Get default preferences for a new user.
     */
    public static function getDefaults(): array
    {
        return [
            'enabled_types' => null, // All types enabled
            'in_app_enabled' => true,
            'email_enabled' => false,
            'push_enabled' => true,
            'quiet_start' => null,
            'quiet_end' => null,
        ];
    }
}
