<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('program_learning_outcome_mappings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id');
            $table->foreignId('program_learning_outcome_id');
            $table->string('co_code', 10);
            $table->timestamps();

            $table->foreign('course_id', 'plo_map_course_fk')
                ->references('id')
                ->on('courses')
                ->cascadeOnDelete();

            $table->foreign('program_learning_outcome_id', 'plo_map_plo_fk')
                ->references('id')
                ->on('program_learning_outcomes')
                ->cascadeOnDelete();

            $table->unique(
                ['course_id', 'program_learning_outcome_id', 'co_code'],
                'plo_mapping_course_plo_co_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('program_learning_outcome_mappings');
    }
};
