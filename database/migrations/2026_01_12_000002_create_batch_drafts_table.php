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
        Schema::create('batch_drafts', function (Blueprint $table) {
            $table->id();
            $table->string('batch_name'); // e.g., "Student Batch 2024 First Year BSIT"
            $table->text('description')->nullable(); // Optional batch description
            $table->foreignId('academic_period_id')->constrained('academic_periods')->onDelete('cascade');
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
            $table->integer('year_level');
            $table->foreignId('co_template_id')->constrained('course_outcome_templates')->onDelete('restrict');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_deleted')->default(false);
            $table->timestamps();
            
            // Ensure unique batch names per academic period
            $table->unique(['batch_name', 'academic_period_id'], 'unique_batch_name_per_period');
        });

        // Students imported for this batch draft
        Schema::create('batch_draft_students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_draft_id')->constrained('batch_drafts')->onDelete('cascade');
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->integer('year_level');
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
            $table->timestamps();
            
            $table->index('batch_draft_id');
        });

        // Subjects associated with batch drafts (many-to-many)
        Schema::create('batch_draft_subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_draft_id')->constrained('batch_drafts')->onDelete('cascade');
            $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');
            $table->boolean('configuration_applied')->default(false); // Has the template been applied?
            $table->timestamps();
            
            $table->unique(['batch_draft_id', 'subject_id'], 'unique_batch_subject');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('batch_draft_subjects');
        Schema::dropIfExists('batch_draft_students');
        Schema::dropIfExists('batch_drafts');
    }
};
