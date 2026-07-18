<?php

namespace App\Imports;

use App\Models\Student;
use Maatwebsite\Excel\Concerns\ToModel;

class StudentsImport implements ToModel
{
    /**
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        return new Student([
            //
        ]);
    }
}
