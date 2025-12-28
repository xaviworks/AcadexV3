<?php

namespace App\Models;

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
}
