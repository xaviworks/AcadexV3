<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('backups', function (Blueprint $table) {
            $table->foreignId('created_by')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('backups', function (Blueprint $table) {
            // We cannot easily revert this if there are null values, 
            // so we might need to delete them or assign a default user.
            // For now, we'll just try to make it non-nullable again.
            $table->foreignId('created_by')->nullable(false)->change();
        });
    }
};
