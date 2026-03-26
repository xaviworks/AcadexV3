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
                'login',
                'logout',
                'failed_login',
                'session_revoked',
                'all_sessions_revoked',
                'bulk_sessions_revoked',
                '2fa_reset_by_admin',
            ]);

            return;
        }

        // Add '2fa_reset_by_admin' to the event_type enum
        DB::statement("ALTER TABLE `user_logs` MODIFY COLUMN `event_type` ENUM('login', 'logout', 'failed_login', 'session_revoked', 'all_sessions_revoked', 'bulk_sessions_revoked', '2fa_reset_by_admin') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if ($this->isSqlite()) {
            $this->rebuildSqliteTable(
                [
                    'login',
                    'logout',
                    'failed_login',
                    'session_revoked',
                    'all_sessions_revoked',
                    'bulk_sessions_revoked',
                ],
                "CASE WHEN event_type = '2fa_reset_by_admin' THEN 'session_revoked' ELSE event_type END"
            );

            return;
        }

        DB::table('user_logs')
            ->where('event_type', '2fa_reset_by_admin')
            ->update(['event_type' => 'session_revoked']);

        // Remove '2fa_reset_by_admin' from the event_type enum
        DB::statement("ALTER TABLE `user_logs` MODIFY COLUMN `event_type` ENUM('login', 'logout', 'failed_login', 'session_revoked', 'all_sessions_revoked', 'bulk_sessions_revoked') NOT NULL");
    }

    private function rebuildSqliteTable(array $eventTypes, string $eventTypeExpression = 'event_type'): void
    {
        Schema::disableForeignKeyConstraints();

        try {
            Schema::create('user_logs_tmp', function (Blueprint $table) use ($eventTypes) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->enum('event_type', $eventTypes);
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->string('browser')->nullable();
                $table->string('device')->nullable();
                $table->string('platform')->nullable();
                $table->timestamps();

                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });

            DB::statement("
                INSERT INTO user_logs_tmp (
                    id, user_id, event_type, ip_address, user_agent, browser, device, platform, created_at, updated_at
                )
                SELECT
                    id,
                    user_id,
                    {$eventTypeExpression} AS event_type,
                    ip_address,
                    user_agent,
                    browser,
                    device,
                    platform,
                    created_at,
                    updated_at
                FROM user_logs
            ");

            Schema::drop('user_logs');
            Schema::rename('user_logs_tmp', 'user_logs');
        } finally {
            Schema::enableForeignKeyConstraints();
        }
    }
};
