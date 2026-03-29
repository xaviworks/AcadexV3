<?php

namespace App\Models;

use App\Support\Organization\GEContext;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * @property int $id
 * @property string $department_code
 * @property string $department_description
 * @property bool $is_deleted
 * @property-read \Illuminate\Database\Eloquent\Collection|Course[] $courses
 * @property-read \Illuminate\Database\Eloquent\Collection|User[] $users
 * @property-read \Illuminate\Database\Eloquent\Collection|Student[] $students
 */
class Department extends Model
{
    use HasFactory;

    protected $fillable = [
        'department_code', 'department_description', 'is_deleted', 'created_by', 'updated_by'
    ];

    protected static function booted()
    {
        static::saved(function () {
            Cache::forget('departments:all');
            GEContext::forgetResolvedIds();
        });

        static::deleted(function () {
            Cache::forget('departments:all');
            GEContext::forgetResolvedIds();
        });
    }

    public static function generalEducation(): ?self
    {
        return self::query()
            ->where('department_code', 'GE')
            ->where('is_deleted', false)
            ->first();
    }

    public static function ase(): ?self
    {
        return self::query()
            ->where('department_code', 'ASE')
            ->where('is_deleted', false)
            ->first();
    }

    public function isGeneralEducation(): bool
    {
        return strtoupper((string) $this->department_code) === 'GE';
    }

    public function formulaDisplayName(): string
    {
        if ($this->isGeneralEducation()) {
            return 'General Education';
        }

        return trim((string) $this->department_description);
    }

    public static function normalizeFormulaDisplayText(?string $text): string
    {
        $value = trim((string) $text);

        if ($value === '') {
            return '';
        }

        return str_ireplace('School of General Education', 'General Education', $value);
    }

    public function courses()
    {
        return $this->hasMany(Course::class, 'department_id');
    }

    public function users()
    {
        return $this->hasMany(User::class, 'department_id');
    }

    public function students()
    {
        return $this->hasMany(Student::class, 'department_id');
    }
}
