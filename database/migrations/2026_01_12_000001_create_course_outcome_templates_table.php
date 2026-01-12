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
        Schema::create('course_outcome_templates', function (Blueprint $table) {
            $table->id();
            $table->string('template_name'); // e.g., "Standard 3 COs", "Advanced 6 COs"
            $table->text('description')->nullable(); // Template description
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('course_id')->nullable()->constrained('courses')->onDelete('cascade'); // Null for universal templates
            $table->boolean('is_universal')->default(false); // For GE coordinator templates
            $table->boolean('is_active')->default(true);
            $table->boolean('is_deleted')->default(false);
            $table->timestamps();
        });

        // Template items - the actual CO configurations
        Schema::create('course_outcome_template_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->constrained('course_outcome_templates')->onDelete('cascade');
            $table->string('co_code'); // CO1, CO2, CO3, etc.
            $table->text('description'); // Default description
            $table->integer('order')->default(0); // Display order
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_outcome_template_items');
        Schema::dropIfExists('course_outcome_templates');
    }
};
