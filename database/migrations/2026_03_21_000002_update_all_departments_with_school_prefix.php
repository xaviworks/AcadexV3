<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Update GE department
        DB::table('departments')
            ->where('department_code', 'GE')
            ->where('department_description', 'General Education')
            ->update([
                'department_description' => 'General Education',
                'updated_at' => now(),
            ]);

        // Update ASE department if not already updated
        DB::table('departments')
            ->where('department_code', 'ASE')
            ->where('department_description', 'Arts and Science and Education')
            ->update([
                'department_description' => 'School of Arts and Science and Education',
                'updated_at' => now(),
            ]);

        // Update ALLIED department
        DB::table('departments')
            ->where('department_code', 'ALLIED')
            ->where('department_description', 'Allied Health')
            ->update([
                'department_description' => 'School of Allied Health',
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        DB::table('departments')
            ->where('department_code', 'GE')
            ->where('department_description', 'General Education')
            ->update([
                'department_description' => 'General Education',
                'updated_at' => now(),
            ]);

        DB::table('departments')
            ->where('department_code', 'ASE')
            ->where('department_description', 'School of Arts and Science and Education')
            ->update([
                'department_description' => 'Arts and Science and Education',
                'updated_at' => now(),
            ]);

        DB::table('departments')
            ->where('department_code', 'ALLIED')
            ->where('department_description', 'School of Allied Health')
            ->update([
                'department_description' => 'Allied Health',
                'updated_at' => now(),
            ]);
    }
};
