<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const ASE_DEPARTMENT_CODE = 'ASE';
    private const GE_DEPARTMENT_CODE = 'GE';
    private const GE_COURSE_CODE = 'GE';

    private const ASE_DEPARTMENT_DESCRIPTION = 'School of Arts and Science and Education';
    private const GE_DEPARTMENT_DESCRIPTION = 'School of General Education';
    private const GE_COURSE_DESCRIPTION = 'General Education';

    public function up(): void
    {
        DB::transaction(function (): void {
            $now = now();

            $aseDepartment = DB::table('departments')
                ->where('department_code', self::ASE_DEPARTMENT_CODE)
                ->first();

            if (!$aseDepartment) {
                $aseDepartmentId = DB::table('departments')->insertGetId([
                    'department_code' => self::ASE_DEPARTMENT_CODE,
                    'department_description' => self::ASE_DEPARTMENT_DESCRIPTION,
                    'is_deleted' => false,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            } else {
                $aseDepartmentId = (int) $aseDepartment->id;

                if ((bool) $aseDepartment->is_deleted) {
                    DB::table('departments')
                        ->where('id', $aseDepartmentId)
                        ->update([
                            'is_deleted' => false,
                            'updated_at' => $now,
                        ]);
                }
            }

            $geDepartment = DB::table('departments')
                ->where('department_code', self::GE_DEPARTMENT_CODE)
                ->first();

            if (!$geDepartment) {
                DB::table('departments')->insert([
                    'department_code' => self::GE_DEPARTMENT_CODE,
                    'department_description' => self::GE_DEPARTMENT_DESCRIPTION,
                    'is_deleted' => false,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            } elseif ((bool) $geDepartment->is_deleted) {
                DB::table('departments')
                    ->where('id', (int) $geDepartment->id)
                    ->update([
                        'is_deleted' => false,
                        'updated_at' => $now,
                    ]);
            }

            $geCourse = DB::table('courses')
                ->where('course_code', self::GE_COURSE_CODE)
                ->first();

            if (!$geCourse) {
                $geCourse = DB::table('courses')
                    ->where('course_description', self::GE_COURSE_DESCRIPTION)
                    ->orderBy('id')
                    ->first();
            }

            if (!$geCourse) {
                DB::table('courses')->insert([
                    'course_code' => self::GE_COURSE_CODE,
                    'course_description' => self::GE_COURSE_DESCRIPTION,
                    'department_id' => $aseDepartmentId,
                    'is_deleted' => false,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                return;
            }

            $courseUpdates = [];
            if ((int) $geCourse->department_id !== $aseDepartmentId) {
                $courseUpdates['department_id'] = $aseDepartmentId;
            }

            if ((bool) $geCourse->is_deleted) {
                $courseUpdates['is_deleted'] = false;
            }

            if ((string) $geCourse->course_description !== self::GE_COURSE_DESCRIPTION) {
                $courseUpdates['course_description'] = self::GE_COURSE_DESCRIPTION;
            }

            if ($courseUpdates !== []) {
                $courseUpdates['updated_at'] = $now;

                DB::table('courses')
                    ->where('id', (int) $geCourse->id)
                    ->update($courseUpdates);
            }
        });
    }

    public function down(): void
    {
        DB::transaction(function (): void {
            $geDepartment = DB::table('departments')
                ->where('department_code', self::GE_DEPARTMENT_CODE)
                ->where('is_deleted', false)
                ->first();

            if (!$geDepartment) {
                return;
            }

            $geCourse = DB::table('courses')
                ->where('course_code', self::GE_COURSE_CODE)
                ->first();

            if (!$geCourse) {
                $geCourse = DB::table('courses')
                    ->where('course_description', self::GE_COURSE_DESCRIPTION)
                    ->orderBy('id')
                    ->first();
            }

            if (!$geCourse) {
                return;
            }

            DB::table('courses')
                ->where('id', (int) $geCourse->id)
                ->update([
                    'department_id' => (int) $geDepartment->id,
                    'updated_at' => now(),
                ]);
        });
    }
};
