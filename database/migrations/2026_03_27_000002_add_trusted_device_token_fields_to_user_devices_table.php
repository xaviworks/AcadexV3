<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_devices', function (Blueprint $table) {
            $table->string('trust_token_hash', 64)->nullable()->after('device_fingerprint');
            $table->timestamp('trusted_until')->nullable()->after('last_used_at');
        });
    }

    public function down(): void
    {
        Schema::table('user_devices', function (Blueprint $table) {
            $table->dropColumn(['trust_token_hash', 'trusted_until']);
        });
    }
};
