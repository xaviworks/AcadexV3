<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subject_attainment_levels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->decimal('level_3', 5, 2)->default(80.00);
            $table->decimal('level_2', 5, 2)->default(70.00);
            $table->decimal('level_1', 5, 2)->default(60.00);
            $table->timestamps();

            $table->unique('subject_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subject_attainment_levels');
    }
};
