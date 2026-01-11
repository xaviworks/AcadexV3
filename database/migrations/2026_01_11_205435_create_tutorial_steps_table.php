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
        Schema::create('tutorial_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tutorial_id')->constrained('tutorials')->onDelete('cascade');
            $table->integer('step_order')->comment('Order of steps starting from 0');
            $table->string('title');
            $table->text('content');
            $table->text('target_selector')->comment('CSS selector for target element');
            $table->enum('position', ['top', 'bottom', 'left', 'right'])->default('bottom');
            $table->boolean('is_optional')->default(false)->comment('Skip step if target element not found');
            $table->boolean('requires_data')->default(false)->comment('Check if table has data before showing this step');
            $table->string('screenshot')->nullable()->comment('Optional screenshot for visual reference');
            $table->timestamps();
            
            // Indexes for performance
            $table->index('tutorial_id');
            $table->index(['tutorial_id', 'step_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tutorial_steps');
    }
};
