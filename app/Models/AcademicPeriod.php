<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class AcademicPeriod extends Model
{
    use HasFactory;

    protected $fillable = [
        'academic_year', 'semester', 'is_deleted', 'created_by', 'updated_by'
    ];

    protected static function booted()
    {
        static::saved(function () {
            Cache::forget('academic_periods:all');
        });

        static::deleted(function () {
            Cache::forget('academic_periods:all');
        });
    }

    /**
     * Resolve the academic period that matches today's date-based AY/semester rules.
     */
    public static function resolveCurrentByDate(?CarbonInterface $date = null): ?self
    {
        [$academicYear, $semester] = self::deriveAcademicYearAndSemester($date);

        return self::query()
            ->where('is_deleted', false)
            ->where('academic_year', $academicYear)
            ->where('semester', $semester)
            ->first();
    }

    /**
     * Derive academic year and semester from a calendar date.
     */
    public static function deriveAcademicYearAndSemester(?CarbonInterface $date = null): array
    {
        $date = $date ?? now();
        $year = (int) $date->year;
        $month = (int) $date->month;

        if ($month >= 8) {
            return [sprintf('%d-%d', $year, $year + 1), '1st'];
        }

        if ($month >= 6) {
            return [sprintf('%d-%d', $year - 1, $year), 'Summer'];
        }

        return [sprintf('%d-%d', $year - 1, $year), '2nd'];
    }
}
