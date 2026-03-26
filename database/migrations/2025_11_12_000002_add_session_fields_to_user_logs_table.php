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
            $this->rebuildSqliteTable(
                [
                    'login',
                    'logout',
                    'failed_login',
                    'session_revoked',
                    'all_sessions_revoked',
                    'bulk_sessions_revoked',
                ],
                true
            );

            return;
        }

        Schema::table('user_logs', function (Blueprint $table) {
            $table->string('ip_address', 45)->nullable()->after('event_type');
            $table->text('user_agent')->nullable()->after('ip_address');
        });

        // Modify the event_type enum to include new session events
        DB::statement("ALTER TABLE `user_logs` MODIFY COLUMN `event_type` ENUM('login', 'logout', 'failed_login', 'session_revoked', 'all_sessions_revoked', 'bulk_sessions_revoked') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if ($this->isSqlite()) {
            $this->rebuildSqliteTable(
                ['login', 'logout', 'failed_login'],
                false,
                $this->legacyEventTypeExpression()
            );

            return;
        }

        DB::statement("UPDATE `user_logs` SET `event_type` = {$this->legacyEventTypeExpression()}");

        // Revert event_type enum to original values
        DB::statement("ALTER TABLE `user_logs` MODIFY COLUMN `event_type` ENUM('login', 'logout', 'failed_login') NOT NULL");

        Schema::table('user_logs', function (Blueprint $table) {
            $table->dropColumn(['ip_address', 'user_agent']);
        });
    }

    private function legacyEventTypeExpression(): string
    {
        return "CASE
            WHEN event_type IN ('session_revoked', 'all_sessions_revoked', 'bulk_sessions_revoked', '2fa_reset_by_admin') THEN 'logout'
            ELSE event_type
        END";
    }

    private function rebuildSqliteTable(
        array $eventTypes,
        bool $includeSessionMetadata,
        string $eventTypeExpression = 'event_type'
    ): void {
        Schema::disableForeignKeyConstraints();

        try {
            Schema::create('user_logs_tmp', function (Blueprint $table) use ($eventTypes, $includeSessionMetadata) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->enum('event_type', $eventTypes);

                if ($includeSessionMetadata) {
                    $table->string('ip_address', 45)->nullable();
                    $table->text('user_agent')->nullable();
                }

                $table->string('browser')->nullable();
                $table->string('device')->nullable();
                $table->string('platform')->nullable();
                $table->timestamps();

                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });

            if ($includeSessionMetadata) {
                DB::statement("
                    INSERT INTO user_logs_tmp (
                        id, user_id, event_type, ip_address, user_agent, browser, device, platform, created_at, updated_at
                    )
                    SELECT
                        id,
                        user_id,
                        {$eventTypeExpression} AS event_type,
                        NULL AS ip_address,
                        NULL AS user_agent,
                        browser,
                        device,
                        platform,
                        created_at,
                        updated_at
                    FROM user_logs
                ");
            } else {
                DB::statement("
                    INSERT INTO user_logs_tmp (
                        id, user_id, event_type, browser, device, platform, created_at, updated_at
                    )
                    SELECT
                        id,
                        user_id,
                        {$eventTypeExpression} AS event_type,
                        browser,
                        device,
                        platform,
                        created_at,
                        updated_at
                    FROM user_logs
                ");
            }

            Schema::drop('user_logs');
            Schema::rename('user_logs_tmp', 'user_logs');
        } finally {
            Schema::enableForeignKeyConstraints();
        }
    }
};
