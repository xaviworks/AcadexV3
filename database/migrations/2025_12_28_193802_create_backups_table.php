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
        Schema::create('backups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type')->default('full'); // full, selective, config
            $table->string('filename');
            $table->string('path');
            $table->unsignedBigInteger('size')->default(0); // bytes
            $table->json('tables')->nullable(); // which tables were backed up
            $table->string('status')->default('pending'); // pending, completed, failed
            $table->text('notes')->nullable();
            $table->text('error_message')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            
            $table->index('status');
            $table->index('type');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('backups');
    }
};
