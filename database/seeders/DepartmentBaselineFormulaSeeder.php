<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\GradesFormula;
use App\Support\Grades\FormulaStructure;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DepartmentBaselineFormulaSeeder extends Seeder
{
    public const BASELINE_STRUCTURE_MAP = [
        'ALLIED' => 'skills_laboratory',
        'MEDICINE' => 'clinical_laboratory',
        'ASE' => 'lecture_laboratory',
        'NURSING' => 'skills_clinical',
        'GE' => 'lecture_only',
        'SBISM' => 'lecture_only',
    ];

    public function run(): void
    {
        $departments = Department::query()
            ->whereIn('department_code', array_keys(self::BASELINE_STRUCTURE_MAP))
            ->where('is_deleted', false)
            ->get()
            ->keyBy('department_code');

        foreach (self::BASELINE_STRUCTURE_MAP as $departmentCode => $structureType) {
            $department = $departments->get($departmentCode);

            if (! $department) {
                continue;
            }

            $structure = FormulaStructure::default($structureType);
            $flattenedWeights = collect(FormulaStructure::flattenWeights($structure))
                ->map(fn (array $entry) => [
                    'activity_type' => $entry['activity_type'],
                    'weight' => $entry['weight'],
                ])
                ->values();

            $formula = GradesFormula::updateOrCreate(
                [
                    'department_id' => $department->id,
                    'scope_level' => 'department',
                    'is_department_fallback' => true,
                    'semester' => null,
                    'academic_period_id' => null,
                ],
                [
                    'name' => 'department_' . Str::lower($departmentCode) . '_baseline_seed',
                    'label' => trim(($department->department_description ?? 'Department') . ' Baseline Formula'),
                    'course_id' => null,
                    'subject_id' => null,
                    'base_score' => 40,
                    'scale_multiplier' => 60,
                    'passing_grade' => 75,
                    'structure_type' => $structureType,
                    'structure_config' => $structure,
                ]
            );

            $formula->weights()->delete();

            if ($flattenedWeights->isNotEmpty()) {
                $formula->weights()->createMany($flattenedWeights->all());
            }
        }
    }
}
