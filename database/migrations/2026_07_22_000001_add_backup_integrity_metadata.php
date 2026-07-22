<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // The columns are included in the original migration for new installs;
        // this migration upgrades databases that already ran it.
        Schema::table('backups', function (Blueprint $table) {
            if (! Schema::hasColumn('backups', 'disk')) {
                $table->string('disk')->default('local')->after('path');
            }
            if (! Schema::hasColumn('backups', 'checksum')) {
                $table->string('checksum', 64)->nullable()->after('size');
            }
            if (! Schema::hasColumn('backups', 'encrypted')) {
                $table->boolean('encrypted')->default(false)->after('checksum');
            }
        });
    }

    public function down(): void
    {
        Schema::table('backups', function (Blueprint $table) {
            $table->dropColumn(['disk', 'checksum', 'encrypted']);
        });
    }
};
