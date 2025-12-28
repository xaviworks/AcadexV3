# Database Query Optimization Audit

**Date:** December 22, 2025  
**Status:** Optimization recommendations identified

## Overview

This document outlines database query optimization opportunities identified in the ACADEX codebase.

## Critical Optimizations Needed

### 1. InstructorController::manageStudents() - N+1 Query Issue

**File:** `app/Http/Controllers/InstructorController.php` (lines 50-64)

**Current Code:**
```php
foreach ($subjects as $subject) {
    $totalStudents = $subject->students_count;

    $graded = TermGrade::where('subject_id', $subject->id)
        ->where('term', $term)
        ->distinct('student_id')
        ->count('student_id');

    $subject->grade_status = match (true) {
        $graded === 0 => 'not_started',
        $graded < $totalStudents => 'pending',
        default => 'completed'
    };
}
```

**Problem:** Executes a separate query for each subject to count graded students (N+1 query).

**Optimized Solution:**
```php
// Pre-fetch graded counts for all subjects at once
$gradedCounts = TermGrade::whereIn('subject_id', $subjects->pluck('id'))
    ->where('term', $term)
    ->select('subject_id', DB::raw('COUNT(DISTINCT student_id) as graded_count'))
    ->groupBy('subject_id')
    ->pluck('graded_count', 'subject_id');

foreach ($subjects as $subject) {
    $totalStudents = $subject->students_count;
    $graded = $gradedCounts[$subject->id] ?? 0;

    $subject->grade_status = match (true) {
        $graded === 0 => 'not_started',
        $graded < $totalStudents => 'pending',
        default => 'completed'
    };
}
```

**Impact:** Reduces N+1 queries to 2 queries total (1 for subjects + 1 for all graded counts).

---

### 2. Caching Opportunities

#### 2.1 Department & Course Lists

**Files:**
- `AdminController.php` (line 3438-3439)
- `InstructorController.php` (line 66)
- `CourseOutcomesController.php` (line 139-140)
- `RegisteredUserController.php` (line 23)

**Current:** Queries database every time

**Optimization:**
```php
use Illuminate\Support\Facades\Cache;

// In controllers:
$departments = Cache::remember('departments:all', 3600, fn() => Department::all());
$courses = Cache::remember('courses:all', 3600, fn() => Course::all());
$periods = Cache::remember('academic_periods:all', 1800, fn() => AcademicPeriod::all());
```

**Cache Invalidation:**
```php
// In Department/Course/AcademicPeriod models, add:

protected static function booted()
{
    static::saved(function () {
        Cache::forget('departments:all');
        // or Cache::forget('courses:all');
        // or Cache::forget('academic_periods:all');
    });

    static::deleted(function () {
        Cache::forget('departments:all');
    });
}
```

**Impact:**
- Reduces database load significantly
- Departments/Courses rarely change (safe to cache for 1 hour)
- AcademicPeriods change occasionally (cache for 30 minutes)

---

### 3. Missing Eager Loading Opportunities

#### 3.1 Check for relationships accessed in views

**Audit needed for:**
- Subject views that access `$subject->department`, `$subject->course`
- Student views that access `$student->subject`, `$student->department`
- Grade calculations that access nested relationships

**Example Fix:**
```php
// Instead of:
$subjects = $instructor->subjects;

// Use:
$subjects = $instructor->subjects()->with(['department', 'course', 'academicPeriod'])->get();
```

---

## Medium Priority Optimizations

### 4. Index Optimization

**Tables that likely need indexes:**

```sql
-- scores table (frequently queried by student_id + activity_id)
ALTER TABLE scores ADD INDEX idx_student_activity (student_id, activity_id);
ALTER TABLE scores ADD INDEX idx_created_at (created_at);

-- students table
ALTER TABLE students ADD INDEX idx_subject_id (subject_id);
ALTER TABLE students ADD INDEX idx_student_number (student_number);

-- term_grades table
ALTER TABLE term_grades ADD INDEX idx_subject_term (subject_id, term);
ALTER TABLE term_grades ADD INDEX idx_student_subject (student_id, subject_id);

-- activities table
ALTER TABLE activities ADD INDEX idx_subject_id (subject_id);

-- course_outcomes table
ALTER TABLE course_outcomes ADD INDEX idx_subject_id (subject_id);
```

**Create migration:**
```bash
php artisan make:migration add_performance_indexes
```

---

### 5. Query Result Pagination

**Files to review:**
- Any controller returning large datasets without pagination
- Admin user management
- Grade listings

**Example:**
```php
// Instead of:
$users = User::all();

// Use:
$users = User::paginate(50);
// or
$users = User::simplePaginate(50);
```

---

## Performance Monitoring

### Tools Installed
- ✅ Laravel Debugbar (installed)

### Recommended Additional Tools
- Laravel Telescope (comprehensive monitoring)
- Query logging in development

**Install Telescope:**
```bash
composer require laravel/telescope --dev
php artisan telescope:install
php artisan migrate
```

**Enable Query Logging (app/Providers/AppServiceProvider.php):**
```php
use Illuminate\Support\Facades\DB;

public function boot()
{
    if (config('app.debug')) {
        DB::listen(function($query) {
            if ($query->time > 100) { // Log slow queries (>100ms)
                Log::warning('Slow Query', [
                    'sql' => $query->sql,
                    'bindings' => $query->bindings,
                    'time' => $query->time
                ]);
            }
        });
    }
}
```

---

## Implementation Priority

### Week 1 (High Impact)
1. ✅ Install Laravel Debugbar
2. ⚠️ Fix InstructorController N+1 query (Critical)
3. ⚠️ Add caching for Department/Course/AcademicPeriod lists

### Week 2 (Medium Impact)
4. Add database indexes (migration)
5. Audit and add eager loading where needed
6. Install Telescope for ongoing monitoring

### Week 3 (Optimization)
7. Add pagination to large dataset queries
8. Profile slow queries with Debugbar
9. Optimize identified bottlenecks

---

## Testing Queries

**Use Debugbar to verify optimization:**
1. Load page before optimization
2. Check query count in Debugbar
3. Apply optimization
4. Reload page
5. Verify query count decreased

**Target Metrics:**
- Admin dashboard: < 15 queries
- Instructor grade page: < 20 queries
- Subject listing: < 10 queries

---

## Notes

- All optimizations must maintain existing functionality
- Test thoroughly after implementing caching
- Consider cache warming strategy for critical pages
- Monitor query performance in Debugbar during development
