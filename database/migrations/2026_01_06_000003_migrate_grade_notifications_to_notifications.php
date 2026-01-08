<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Migrate existing grade_notifications to the new notifications table.
 * This migration is safe to run multiple times.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Skip if old table doesn't exist
        if (!Schema::hasTable('grade_notifications')) {
            return;
        }

        // Skip if new table doesn't exist
        if (!Schema::hasTable('notifications')) {
            return;
        }

        // Migrate existing grade notifications
        $oldNotifications = DB::table('grade_notifications')->get();

        foreach ($oldNotifications as $old) {
            DB::table('notifications')->insert([
                'id' => Str::uuid()->toString(),
                'type' => 'App\\Notifications\\GradeSubmitted',
                'notifiable_type' => 'App\\Models\\User',
                'notifiable_id' => $old->notified_user_id,
                'data' => json_encode([
                    'type' => 'grade_submitted',
                    'category' => 'academic',
                    'priority' => 'normal',
                    'actor_id' => $old->instructor_id,
                    'subject_id' => $old->subject_id,
                    'term' => $old->term,
                    'students_graded' => $old->students_graded,
                    'message' => $old->message,
                    // For admin detailed view
                    'admin_message' => $old->message,
                    // For friendly user view
                    'user_message' => $old->message,
                    'icon' => 'bi-journal-check',
                    'color' => 'success',
                    'action_url' => null,
                    'action_text' => null,
                ]),
                'read_at' => $old->is_read ? $old->read_at : null,
                'created_at' => $old->created_at,
                'updated_at' => $old->updated_at,
            ]);
        }

        // Rename old table as backup instead of dropping
        Schema::rename('grade_notifications', 'grade_notifications_backup');
    }

    public function down(): void
    {
        // Restore backup table if it exists
        if (Schema::hasTable('grade_notifications_backup')) {
            Schema::rename('grade_notifications_backup', 'grade_notifications');
        }

        // Clear migrated notifications
        DB::table('notifications')
            ->where('type', 'App\\Notifications\\GradeSubmitted')
            ->delete();
    }
};
