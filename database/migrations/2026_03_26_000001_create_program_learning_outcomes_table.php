<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('program_learning_outcomes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->string('plo_code', 20);
            $table->string('title');
            $table->unsignedSmallInteger('display_order')->default(1);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_deleted')->default(false);
            $table->timestamps();

            $table->index(['course_id', 'is_deleted']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('program_learning_outcomes');
    }
};
