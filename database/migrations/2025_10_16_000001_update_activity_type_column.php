<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private function isSqlite(): bool
    {
        return Schema::getConnection()->getDriverName() === 'sqlite';
    }

    public function up(): void
    {
        if ($this->isSqlite()) {
            $this->rebuildSqliteTable(true);
            return;
        }

        DB::statement("ALTER TABLE activities MODIFY type VARCHAR(191) NOT NULL");
    }

    public function down(): void
    {
        if ($this->isSqlite()) {
            $this->rebuildSqliteTable(false, $this->legacyTypeExpression());
            return;
        }

        DB::statement("UPDATE activities SET type = {$this->legacyTypeExpression()}");
        DB::statement("ALTER TABLE activities MODIFY type ENUM('quiz','ocr','exam') NOT NULL");
    }

    private function legacyTypeExpression(): string
    {
        return "CASE
            WHEN lower(type) IN ('quiz', 'ocr', 'exam') THEN lower(type)
            WHEN lower(type) LIKE '%.quiz' THEN 'quiz'
            WHEN lower(type) LIKE '%.exam' THEN 'exam'
            ELSE 'ocr'
        END";
    }

    private function rebuildSqliteTable(bool $useStringType, string $typeExpression = 'type'): void
    {
        Schema::disableForeignKeyConstraints();

        try {
            Schema::create('activities_tmp', function (Blueprint $table) use ($useStringType) {
                $table->id();
                $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');
                $table->foreignId('academic_period_id')->nullable()->constrained('academic_periods')->nullOnDelete();
                $table->enum('term', ['prelim', 'midterm', 'prefinal', 'final']);

                if ($useStringType) {
                    $table->string('type', 191);
                } else {
                    $table->enum('type', ['quiz', 'ocr', 'exam']);
                }

                $table->string('title');
                $table->unsignedBigInteger('course_outcome_id')->nullable();
                $table->foreign('course_outcome_id')->references('id')->on('course_outcomes')->onDelete('set null');
                $table->integer('number_of_items');
                $table->boolean('is_deleted')->default(false);
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });

            DB::statement("
                INSERT INTO activities_tmp (
                    id,
                    subject_id,
                    academic_period_id,
                    term,
                    type,
                    title,
                    course_outcome_id,
                    number_of_items,
                    is_deleted,
                    created_by,
                    updated_by,
                    created_at,
                    updated_at
                )
                SELECT
                    id,
                    subject_id,
                    academic_period_id,
                    term,
                    {$typeExpression} AS type,
                    title,
                    course_outcome_id,
                    number_of_items,
                    is_deleted,
                    created_by,
                    updated_by,
                    created_at,
                    updated_at
                FROM activities
            ");

            Schema::drop('activities');
            Schema::rename('activities_tmp', 'activities');
        } finally {
            Schema::enableForeignKeyConstraints();
        }
    }
};
