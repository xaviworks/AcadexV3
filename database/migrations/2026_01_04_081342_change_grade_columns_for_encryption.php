<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Migration to change grade columns from decimal to text for encryption support.
 * 
 * This migration converts grade columns to text type to allow storing
 * encrypted values. Existing decimal values are preserved as strings.
 * 
 * IMPORTANT: This is a one-way migration for encryption support.
 * Rolling back will convert text back to decimal, which may lose
 * encrypted data if encryption was enabled.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Change scores.score from decimal to text
        Schema::table('scores', function (Blueprint $table) {
            $table->text('score')->nullable()->change();
        });

        // Change term_grades.term_grade from decimal to text
        Schema::table('term_grades', function (Blueprint $table) {
            $table->text('term_grade')->nullable()->change();
        });

        // Change final_grades.final_grade from decimal to text
        Schema::table('final_grades', function (Blueprint $table) {
            $table->text('final_grade')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     * 
     * WARNING: Rolling back will lose encrypted data!
     * Only roll back if all data is in plain decimal format.
     */
    public function down(): void
    {
        // Revert scores.score to decimal
        // First, ensure any non-numeric values are set to NULL
        DB::statement("UPDATE scores SET score = NULL WHERE score IS NOT NULL AND score NOT REGEXP '^-?[0-9]+\.?[0-9]*$'");
        Schema::table('scores', function (Blueprint $table) {
            $table->decimal('score', 5, 2)->nullable()->change();
        });

        // Revert term_grades.term_grade to decimal
        DB::statement("UPDATE term_grades SET term_grade = NULL WHERE term_grade IS NOT NULL AND term_grade NOT REGEXP '^-?[0-9]+\.?[0-9]*$'");
        Schema::table('term_grades', function (Blueprint $table) {
            $table->decimal('term_grade', 5, 2)->nullable()->change();
        });

        // Revert final_grades.final_grade to decimal
        DB::statement("UPDATE final_grades SET final_grade = NULL WHERE final_grade IS NOT NULL AND final_grade NOT REGEXP '^-?[0-9]+\.?[0-9]*$'");
        Schema::table('final_grades', function (Blueprint $table) {
            $table->decimal('final_grade', 5, 2)->nullable()->change();
        });
    }
};
