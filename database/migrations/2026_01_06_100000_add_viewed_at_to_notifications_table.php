<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add viewed_at column to track when a user has viewed (seen) a notification.
 * This is separate from read_at which tracks when a user clicked/interacted with it.
 * 
 * - viewed_at: When the user opened the notification bell dropdown (saw the notification)
 * - read_at: When the user clicked on the notification (interacted with it)
 * 
 * The badge count shows notifications where viewed_at IS NULL.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->timestamp('viewed_at')->nullable()->after('read_at');
            
            // Index for efficient badge count queries
            $table->index(['notifiable_type', 'notifiable_id', 'viewed_at'], 'notifications_viewed_at_index');
        });
    }

    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex('notifications_viewed_at_index');
            $table->dropColumn('viewed_at');
        });
    }
};
