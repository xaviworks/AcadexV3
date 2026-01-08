<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Notification preferences for users to control which notifications they receive.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Notification type preferences (JSON for flexibility)
            // Stores enabled/disabled status for each notification type
            $table->json('enabled_types')->nullable();
            
            // Channel preferences
            $table->boolean('in_app_enabled')->default(true);
            $table->boolean('email_enabled')->default(false);
            $table->boolean('push_enabled')->default(true);
            
            // Quiet hours (optional)
            $table->time('quiet_start')->nullable();
            $table->time('quiet_end')->nullable();
            
            $table->timestamps();
            
            $table->unique('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_preferences');
    }
};
