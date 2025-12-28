<?php

namespace App\Models;

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
        });

        static::deleted(function () {
            Cache::forget('departments:all');
        });
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
