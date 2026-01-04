<?php

namespace App\Services;

use App\Models\AcademicPeriod;
use App\Models\Course;
use App\Models\Department;
use App\Models\Term;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Centralized caching service for reference data.
 * 
 * This service provides cached access to frequently-used reference data
 * that rarely changes, reducing database load significantly.
 * 
 * Cache invalidation is handled automatically via model events
 * in Department, Course, and AcademicPeriod models.
 */
class CacheService
{
    /**
     * Cache TTL constants (in seconds)
     */
    private const TTL_DEPARTMENTS = 3600;      // 1 hour
    private const TTL_COURSES = 3600;          // 1 hour
    private const TTL_ACADEMIC_PERIODS = 1800; // 30 minutes
    private const TTL_TERMS = 86400;           // 24 hours (rarely change)

    /**
     * Get all departments (cached).
     * 
     * @param bool $activeOnly Whether to filter out deleted departments
     * @return Collection<Department>
     */
    public static function departments(bool $activeOnly = true): Collection
    {
        $key = $activeOnly ? 'departments:active' : 'departments:all';

        return Cache::remember($key, self::TTL_DEPARTMENTS, function () use ($activeOnly) {
            $query = Department::query()->orderBy('department_code');
            
            if ($activeOnly) {
                $query->where('is_deleted', false);
            }
            
            return $query->get();
        });
    }

    /**
     * Get all courses (cached).
     * 
     * @param bool $activeOnly Whether to filter out deleted courses
     * @param int|null $departmentId Filter by department
     * @return Collection<Course>
     */
    public static function courses(bool $activeOnly = true, ?int $departmentId = null): Collection
    {
        $key = 'courses:' . ($activeOnly ? 'active' : 'all');
        
        if ($departmentId) {
            $key .= ":dept_{$departmentId}";
        }

        return Cache::remember($key, self::TTL_COURSES, function () use ($activeOnly, $departmentId) {
            $query = Course::query()
                ->with('department')
                ->orderBy('course_code');
            
            if ($activeOnly) {
                $query->where('is_deleted', false);
            }
            
            if ($departmentId) {
                $query->where('department_id', $departmentId);
            }
            
            return $query->get();
        });
    }

    /**
     * Get all academic periods (cached).
     * 
     * @param bool $activeOnly Whether to filter out deleted periods
     * @return Collection<AcademicPeriod>
     */
    public static function academicPeriods(bool $activeOnly = true): Collection
    {
        $key = $activeOnly ? 'academic_periods:active' : 'academic_periods:all';

        return Cache::remember($key, self::TTL_ACADEMIC_PERIODS, function () use ($activeOnly) {
            $query = AcademicPeriod::query()
                ->orderByDesc('academic_year')
                ->orderByRaw("FIELD(semester, '1st', '2nd', 'Summer')");
            
            if ($activeOnly) {
                $query->where('is_deleted', false);
            }
            
            return $query->get();
        });
    }

    /**
     * Get all terms (cached).
     * 
     * @return Collection<Term>
     */
    public static function terms(): Collection
    {
        return Cache::remember('terms:all', self::TTL_TERMS, function () {
            return Term::query()->orderBy('id')->get();
        });
    }

    /**
     * Get a department by ID (cached).
     * 
     * @param int $id
     * @return Department|null
     */
    public static function department(int $id): ?Department
    {
        return Cache::remember("department:{$id}", self::TTL_DEPARTMENTS, function () use ($id) {
            return Department::find($id);
        });
    }

    /**
     * Get a course by ID (cached).
     * 
     * @param int $id
     * @return Course|null
     */
    public static function course(int $id): ?Course
    {
        return Cache::remember("course:{$id}", self::TTL_COURSES, function () use ($id) {
            return Course::with('department')->find($id);
        });
    }

    /**
     * Get an academic period by ID (cached).
     * 
     * @param int $id
     * @return AcademicPeriod|null
     */
    public static function academicPeriod(int $id): ?AcademicPeriod
    {
        return Cache::remember("academic_period:{$id}", self::TTL_ACADEMIC_PERIODS, function () use ($id) {
            return AcademicPeriod::find($id);
        });
    }

    /**
     * Get courses grouped by department (cached).
     * 
     * @param bool $activeOnly
     * @return \Illuminate\Support\Collection
     */
    public static function coursesByDepartment(bool $activeOnly = true): \Illuminate\Support\Collection
    {
        $key = $activeOnly ? 'courses:by_department:active' : 'courses:by_department:all';

        return Cache::remember($key, self::TTL_COURSES, function () use ($activeOnly) {
            return self::courses($activeOnly)->groupBy('department_id');
        });
    }

    /**
     * Get term ID by name.
     * 
     * @param string $termName (prelim, midterm, prefinal, final)
     * @return int|null
     */
    public static function termId(string $termName): ?int
    {
        $termMap = Cache::remember('terms:map', self::TTL_TERMS, function () {
            return self::terms()->pluck('id', 'name')->toArray();
        });

        return $termMap[$termName] ?? null;
    }

    /**
     * Clear all reference data caches.
     * 
     * Use this when doing bulk data operations.
     */
    public static function clearAll(): void
    {
        $keys = [
            'departments:all',
            'departments:active',
            'courses:all',
            'courses:active',
            'courses:by_department:all',
            'courses:by_department:active',
            'academic_periods:all',
            'academic_periods:active',
            'terms:all',
            'terms:map',
        ];

        foreach ($keys as $key) {
            Cache::forget($key);
        }
    }

    /**
     * Clear department-related caches.
     */
    public static function clearDepartments(): void
    {
        Cache::forget('departments:all');
        Cache::forget('departments:active');
        // Also clear courses since they depend on departments
        self::clearCourses();
    }

    /**
     * Clear course-related caches.
     */
    public static function clearCourses(): void
    {
        Cache::forget('courses:all');
        Cache::forget('courses:active');
        Cache::forget('courses:by_department:all');
        Cache::forget('courses:by_department:active');
    }

    /**
     * Clear academic period-related caches.
     */
    public static function clearAcademicPeriods(): void
    {
        Cache::forget('academic_periods:all');
        Cache::forget('academic_periods:active');
    }
}
