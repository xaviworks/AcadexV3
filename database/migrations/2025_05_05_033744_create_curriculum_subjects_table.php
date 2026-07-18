<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('curriculum_subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('curriculum_id')->constrained('curriculums')->onDelete('cascade');
            $table->string('subject_code');
            $table->string('subject_description');
            $table->integer('year_level');
            $table->string('semester'); // 1st, 2nd
            $table->boolean('is_deleted')->default(false);
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('curriculum_subjects');
    }
};
