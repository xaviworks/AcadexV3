<?php

use Illuminate\Database\Migrations\Migration;
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
            return;
        }

        // Use raw SQL to modify column type to DATETIME to allow far future values
        DB::statement('ALTER TABLE `users` MODIFY `disabled_until` DATETIME NULL;');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if ($this->isSqlite()) {
            return;
        }

        // Revert to TIMESTAMP if needed
        DB::statement('ALTER TABLE `users` MODIFY `disabled_until` TIMESTAMP NULL;');
    }
};
