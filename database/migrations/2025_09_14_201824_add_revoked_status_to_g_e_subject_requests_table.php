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

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if ($this->isSqlite()) {
            $this->rebuildSqliteTable([
                'pending',
                'approved',
                'rejected',
                'revoked',
            ]);

            return;
        }

        Schema::table('g_e_subject_requests', function (Blueprint $table) {
            // Modify the status ENUM to include 'revoked'
            $table->enum('status', ['pending', 'approved', 'rejected', 'revoked'])->default('pending')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if ($this->isSqlite()) {
            $this->rebuildSqliteTable(
                ['pending', 'approved', 'rejected'],
                "CASE WHEN status = 'revoked' THEN 'rejected' ELSE status END"
            );

            return;
        }

        DB::table('g_e_subject_requests')
            ->where('status', 'revoked')
            ->update(['status' => 'rejected']);

        Schema::table('g_e_subject_requests', function (Blueprint $table) {
            // Revert back to original ENUM values
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending')->change();
        });
    }

    private function rebuildSqliteTable(array $statuses, string $statusExpression = 'status'): void
    {
        Schema::disableForeignKeyConstraints();

        try {
            Schema::create('g_e_subject_requests_tmp', function (Blueprint $table) use ($statuses) {
                $table->id();
                $table->foreignId('instructor_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('requested_by')->constrained('users')->onDelete('cascade');
                $table->enum('status', $statuses)->default('pending');
                $table->text('notes')->nullable();
                $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamp('reviewed_at')->nullable();
                $table->timestamps();
            });

            DB::statement("
                INSERT INTO g_e_subject_requests_tmp (
                    id, instructor_id, requested_by, status, notes, reviewed_by, reviewed_at, created_at, updated_at
                )
                SELECT
                    id,
                    instructor_id,
                    requested_by,
                    {$statusExpression} AS status,
                    notes,
                    reviewed_by,
                    reviewed_at,
                    created_at,
                    updated_at
                FROM g_e_subject_requests
            ");

            Schema::drop('g_e_subject_requests');
            Schema::rename('g_e_subject_requests_tmp', 'g_e_subject_requests');
        } finally {
            Schema::enableForeignKeyConstraints();
        }
    }
};
