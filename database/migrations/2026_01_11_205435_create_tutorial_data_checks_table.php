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
        Schema::create('tutorial_data_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tutorial_id')->constrained('tutorials')->onDelete('cascade');
            $table->string('selector')->nullable()->comment('CSS selector to check for data rows (e.g., tbody tr)');
            $table->json('empty_selectors')->nullable()->comment('Array of selectors that indicate empty state');
            $table->string('entity_name')->default('records')->comment('Name of the entity for user-friendly messages');
            $table->string('add_button_selector')->nullable()->comment('Selector for the add button');
            $table->boolean('no_add_button')->default(false)->comment('True if page has no add button (e.g., request-based pages)');
            $table->timestamps();
            
            // Index for performance
            $table->index('tutorial_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tutorial_data_checks');
    }
};
