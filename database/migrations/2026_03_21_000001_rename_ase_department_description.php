<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('departments')
            ->where('department_code', 'ASE')
            ->where('department_description', 'Arts, Science, and Education')
            ->update([
                'department_description' => 'Arts and Science and Education',
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        DB::table('departments')
            ->where('department_code', 'ASE')
            ->where('department_description', 'Arts and Science and Education')
            ->update([
                'department_description' => 'Arts, Science, and Education',
                'updated_at' => now(),
            ]);
    }
};
