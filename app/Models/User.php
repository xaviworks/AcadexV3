<?php

namespace App\Models;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Crypt;

/**
 * @property int $id
 * @property string $first_name
 * @property string|null $middle_name
 * @property string $last_name
 * @property string $email
 * @property string|null $google_id
 * @property int $role
 * @property int|null $department_id
 * @property int|null $course_id
 * @property string $password
 * @property bool $is_active
 * @property-read Department|null $department
 * @property-read Course|null $course
 * @property-read NotificationPreference|null $notificationPreferences
 * @property-read string $full_name
 * @property-read string $name
 */
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'first_name',
        'middle_name',
        'last_name',
        'email',
        'google_id',
        'password',
        'role',
        'is_active',
        'can_teach_ge',
        'department_id',
        'course_id',
        'disabled_until',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_confirmed_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
        'can_teach_ge' => 'boolean',
        'disabled_until' => 'datetime',
        'two_factor_confirmed_at' => 'datetime',
    ];

    protected function twoFactorSecret(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->decryptSecretIfNeeded($value),
            set: fn ($value) => $this->encryptSecretIfNeeded($value),
        );
    }

    public function devices()
    {
        return $this->hasMany(UserDevice::class);
    }


    /**
     * Accessor to get the full name of the user.
     * Example: Juan Pedro Santos
     */
    public function getFullNameAttribute(): string
    {
        $names = [$this->first_name];
        if ($this->middle_name) {
            $names[] = $this->middle_name;
        }
        $names[] = $this->last_name;

        return implode(' ', $names);
    }

    /**
     * Accessor to get a virtual `name` attribute for compatibility.
     * Example: Juan Pedro Santos
     */
    public function getNameAttribute(): string
    {
        return $this->full_name;
    }

    /**
     * Relationships
     */

    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'instructor_subject', 'instructor_id', 'subject_id')
            ->withTimestamps();
    }

    public function createdStudents()
    {
        return $this->hasMany(Student::class, 'created_by');
    }

    public function createdSubjects()
    {
        return $this->hasMany(Subject::class, 'created_by');
    }

    public function createdActivities()
    {
        return $this->hasMany(Activity::class, 'created_by');
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function notificationPreferences()
    {
        return $this->hasOne(NotificationPreference::class);
    }

    /**
     * Check if the user is an instructor
     */
    public function isInstructor(): bool
    {
        return $this->role === 0;
    }

    /**
     * Check if the user is a Dean
     */
    public function isDean(): bool
    {
        return $this->role === 2;
    }

    public function geSubjectRequests()
    {
        return $this->hasMany(GESubjectRequest::class, 'instructor_id');
    }

    /**
     * Check if the user is an admin
     */
    public function isAdmin(): bool
    {
        return $this->role === 3;
    }

    /**
     * Check if the user is a Chairperson
     */
    public function isChairperson(): bool
    {
        return $this->role === 1;
    }

    /**
     * Check if the user is a GE Coordinator
     */
    public function isGECoordinator(): bool
    {
        return $this->role === 4;
    }

    /**
     * Check if the user is a VPAA
     */
    public function isVPAA(): bool
    {
        return $this->role === 5;
    }

    private function decryptSecretIfNeeded(mixed $value): ?string
    {
        if (! is_string($value) || $value === '') {
            return null;
        }

        try {
            return Crypt::decryptString($value);
        } catch (DecryptException) {
            return $value;
        }
    }

    private function encryptSecretIfNeeded(mixed $value): ?string
    {
        if (! is_string($value) || $value === '') {
            return null;
        }

        if ($this->looksEncrypted($value)) {
            return $value;
        }

        return Crypt::encryptString($value);
    }

    private function looksEncrypted(string $value): bool
    {
        $decoded = base64_decode($value, true);

        if ($decoded === false) {
            return false;
        }

        $payload = json_decode($decoded, true);

        return is_array($payload)
            && array_key_exists('iv', $payload)
            && array_key_exists('value', $payload)
            && array_key_exists('mac', $payload);
    }
}
