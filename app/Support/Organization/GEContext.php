<?php

namespace App\Support\Organization;

use App\Models\Course;
use App\Models\Department;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class GEContext
{
    public const GE_DEPARTMENT_CODE = 'GE';
    public const ASE_DEPARTMENT_CODE = 'ASE';
    public const GE_COURSE_CODE = 'GE';

    private static bool $geDepartmentResolved = false;
    private static ?int $geDepartmentId = null;

    private static bool $aseDepartmentResolved = false;
    private static ?int $aseDepartmentId = null;

    private static bool $geCourseResolved = false;
    private static ?int $geCourseId = null;

    public static function forgetResolvedIds(): void
    {
        self::$geDepartmentResolved = false;
        self::$geDepartmentId = null;

        self::$aseDepartmentResolved = false;
        self::$aseDepartmentId = null;

        self::$geCourseResolved = false;
        self::$geCourseId = null;
    }

    public static function geDepartmentId(): ?int
    {
        if (!self::$geDepartmentResolved) {
            self::$geDepartmentResolved = true;
            self::$geDepartmentId = Department::query()
                ->where('department_code', self::GE_DEPARTMENT_CODE)
                ->where('is_deleted', false)
                ->value('id');
        }

        return self::$geDepartmentId;
    }

    public static function aseDepartmentId(): ?int
    {
        if (!self::$aseDepartmentResolved) {
            self::$aseDepartmentResolved = true;
            self::$aseDepartmentId = Department::query()
                ->where('department_code', self::ASE_DEPARTMENT_CODE)
                ->where('is_deleted', false)
                ->value('id');
        }

        return self::$aseDepartmentId;
    }

    public static function geCourseId(): ?int
    {
        if (!self::$geCourseResolved) {
            self::$geCourseResolved = true;

            $courseId = Course::query()
                ->where('course_code', self::GE_COURSE_CODE)
                ->where('is_deleted', false)
                ->value('id');

            if (!$courseId) {
                $courseId = Course::query()
                    ->where('course_description', 'General Education')
                    ->where('is_deleted', false)
                    ->value('id');
            }

            self::$geCourseId = $courseId;
        }

        return self::$geCourseId;
    }

    public static function isGESubject(Subject $subject): bool
    {
        if ((bool) ($subject->is_universal ?? false)) {
            return true;
        }

        $geDepartmentId = self::geDepartmentId();
        if ($geDepartmentId !== null && (int) $subject->department_id === (int) $geDepartmentId) {
            return true;
        }

        $geCourseId = self::geCourseId();
        return $geCourseId !== null && (int) $subject->course_id === (int) $geCourseId;
    }

    public static function applyManagedSubjectFilter(Builder $query): Builder
    {
        $geDepartmentId = self::geDepartmentId();
        $geCourseId = self::geCourseId();

        return $query->where(function (Builder $subjectQuery) use ($geDepartmentId, $geCourseId): void {
            $subjectQuery->where('is_universal', true);

            if ($geDepartmentId !== null) {
                $subjectQuery->orWhere('department_id', $geDepartmentId);
            }

            if ($geCourseId !== null) {
                $subjectQuery->orWhere('course_id', $geCourseId);
            }
        });
    }

    public static function applyNonManagedSubjectFilter(Builder $query): Builder
    {
        $geDepartmentId = self::geDepartmentId();
        $geCourseId = self::geCourseId();

        return $query
            ->where('is_universal', false)
            ->when($geDepartmentId !== null, function (Builder $subjectQuery) use ($geDepartmentId): void {
                $subjectQuery->where('department_id', '!=', $geDepartmentId);
            })
            ->when($geCourseId !== null, function (Builder $subjectQuery) use ($geCourseId): void {
                $subjectQuery->where('course_id', '!=', $geCourseId);
            });
    }

    public static function geRegistrationDepartmentId(): ?int
    {
        return self::aseDepartmentId() ?? self::geDepartmentId();
    }

    public static function isGERegistrationTarget(?int $departmentId, ?int $courseId): bool
    {
        $resolvedCourseId = $courseId !== null ? (int) $courseId : null;
        $geCourseId = self::geCourseId();
        return $geCourseId !== null && $resolvedCourseId === (int) $geCourseId;
    }

    public static function applyGERegistrationTargetFilter(
        Builder $query,
        string $departmentColumn = 'department_id',
        string $courseColumn = 'course_id'
    ): Builder {
        $geCourseId = self::geCourseId();

        if ($geCourseId !== null) {
            return $query->where($courseColumn, $geCourseId);
        }

        return $query->whereRaw('1 = 0');
    }

    public static function applyNonGERegistrationTargetFilter(
        Builder $query,
        string $departmentColumn = 'department_id',
        string $courseColumn = 'course_id'
    ): Builder {
        $geCourseId = self::geCourseId();

        if ($geCourseId !== null) {
            return $query->where($courseColumn, '!=', $geCourseId);
        }

        return $query;
    }

    public static function geCoordinatorsQuery(): Builder
    {
        return User::query()
            ->where('role', 4)
            ->where('is_active', true);
    }
}
