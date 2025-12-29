<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add '2fa_reset_by_admin' to the event_type enum
        DB::statement("ALTER TABLE `user_logs` MODIFY COLUMN `event_type` ENUM('login', 'logout', 'failed_login', 'session_revoked', 'all_sessions_revoked', 'bulk_sessions_revoked', '2fa_reset_by_admin') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove '2fa_reset_by_admin' from the event_type enum
        DB::statement("ALTER TABLE `user_logs` MODIFY COLUMN `event_type` ENUM('login', 'logout', 'failed_login', 'session_revoked', 'all_sessions_revoked', 'bulk_sessions_revoked') NOT NULL");
    }
};
