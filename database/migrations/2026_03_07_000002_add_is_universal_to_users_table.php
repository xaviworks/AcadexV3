<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Back-fills the missing `is_universal` boolean on the users table.
 *
 * This column already exists in production (added directly to the DB as part
 * of the GE-coordinator feature) but was never recorded in a migration file.
 * The UserFactory and User model both reference it, causing SQLite test
 * failures. Using hasColumn() keeps the migration safe to re-run on any
 * environment where the column already exists.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'is_universal')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('is_universal')->default(false)->after('can_teach_ge');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'is_universal')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('is_universal');
            });
        }
    }
};
