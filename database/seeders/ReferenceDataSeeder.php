<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ReferenceDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            DepartmentsTableSeeder::class,
            CoursesTableSeeder::class,
            AcademicPeriodsTableSeeder::class,
            TermsTableSeeder::class,
            CurriculumSeeder::class,
            GradesFormulaSeeder::class,
            DepartmentBaselineFormulaSeeder::class,
            AnnouncementTemplateSeeder::class,
        ]);
    }
}
