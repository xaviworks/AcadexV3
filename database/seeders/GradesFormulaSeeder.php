<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\GradesFormula;
use App\Support\Grades\FormulaDefaults;

class GradesFormulaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $formula = GradesFormula::updateOrCreate(
            ['name' => 'asbme_default'],
            [
                'label' => FormulaDefaults::GLOBAL_FALLBACK_LABEL,
                'scope_level' => 'global',
                'department_id' => null,
                'course_id' => null,
                'subject_id' => null,
                'base_score' => 50,
                'scale_multiplier' => 50,
                'passing_grade' => 75,
            ]
        );

        GradesFormula::query()
            ->where('scope_level', 'global')
            ->where('label', 'ASBME Default')
            ->update(['label' => FormulaDefaults::GLOBAL_FALLBACK_LABEL]);

        $formula->weights()->delete();
        $formula->weights()->createMany([
            ['activity_type' => 'Quiz', 'weight' => 0.40],
            ['activity_type' => 'OCR', 'weight' => 0.20],
            ['activity_type' => 'Exam', 'weight' => 0.40],
        ]);
    }
}
