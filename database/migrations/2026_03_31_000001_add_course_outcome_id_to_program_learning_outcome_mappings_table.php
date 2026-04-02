<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('program_learning_outcome_mappings', 'course_outcome_id')) {
            Schema::table('program_learning_outcome_mappings', function (Blueprint $table) {
                $table->foreignId('course_outcome_id')
                    ->nullable()
                    ->after('program_learning_outcome_id');
            });
        }

        if ($this->isSqlite()) {
            // SQLite test runs keep the legacy unique constraint. Production MySQL
            // still applies the full index/foreign-key transition below.
            return;
        }

        if (!$this->indexExists('program_learning_outcome_mappings', 'plo_map_course_id_idx')) {
            Schema::table('program_learning_outcome_mappings', function (Blueprint $table) {
                // Keep a dedicated index for the course_id foreign key before dropping
                // the old unique index that currently satisfies that requirement.
                $table->index('course_id', 'plo_map_course_id_idx');
            });
        }

        if ($this->indexExists('program_learning_outcome_mappings', 'plo_mapping_course_plo_co_unique')) {
            Schema::table('program_learning_outcome_mappings', function (Blueprint $table) {
                $table->dropUnique('plo_mapping_course_plo_co_unique');
            });
        }

        if (!$this->foreignKeyExists('program_learning_outcome_mappings', 'plo_map_course_outcome_fk')) {
            Schema::table('program_learning_outcome_mappings', function (Blueprint $table) {
                $table->foreign('course_outcome_id', 'plo_map_course_outcome_fk')
                    ->references('id')
                    ->on('course_outcomes')
                    ->cascadeOnDelete();
            });
        }

        if (!$this->indexExists('program_learning_outcome_mappings', 'plo_mapping_course_plo_coid_unique')) {
            Schema::table('program_learning_outcome_mappings', function (Blueprint $table) {
                $table->unique(
                    ['course_id', 'program_learning_outcome_id', 'course_outcome_id'],
                    'plo_mapping_course_plo_coid_unique'
                );
            });
        }

        if (!$this->indexExists('program_learning_outcome_mappings', 'plo_mapping_course_coid_index')) {
            Schema::table('program_learning_outcome_mappings', function (Blueprint $table) {
                $table->index(['course_id', 'course_outcome_id'], 'plo_mapping_course_coid_index');
            });
        }
    }

    public function down(): void
    {
        if ($this->isSqlite()) {
            if (Schema::hasColumn('program_learning_outcome_mappings', 'course_outcome_id')) {
                Schema::table('program_learning_outcome_mappings', function (Blueprint $table) {
                    $table->dropColumn('course_outcome_id');
                });
            }

            return;
        }

        if ($this->foreignKeyExists('program_learning_outcome_mappings', 'plo_map_course_outcome_fk')) {
            Schema::table('program_learning_outcome_mappings', function (Blueprint $table) {
                $table->dropForeign('plo_map_course_outcome_fk');
            });
        }

        if ($this->indexExists('program_learning_outcome_mappings', 'plo_mapping_course_plo_coid_unique')) {
            Schema::table('program_learning_outcome_mappings', function (Blueprint $table) {
                $table->dropUnique('plo_mapping_course_plo_coid_unique');
            });
        }

        if ($this->indexExists('program_learning_outcome_mappings', 'plo_mapping_course_coid_index')) {
            Schema::table('program_learning_outcome_mappings', function (Blueprint $table) {
                $table->dropIndex('plo_mapping_course_coid_index');
            });
        }

        if ($this->indexExists('program_learning_outcome_mappings', 'plo_map_course_id_idx')) {
            Schema::table('program_learning_outcome_mappings', function (Blueprint $table) {
                $table->dropIndex('plo_map_course_id_idx');
            });
        }

        if (Schema::hasColumn('program_learning_outcome_mappings', 'course_outcome_id')) {
            Schema::table('program_learning_outcome_mappings', function (Blueprint $table) {
                $table->dropColumn('course_outcome_id');
            });
        }

        if (!$this->indexExists('program_learning_outcome_mappings', 'plo_mapping_course_plo_co_unique')) {
            Schema::table('program_learning_outcome_mappings', function (Blueprint $table) {
                $table->unique(
                    ['course_id', 'program_learning_outcome_id', 'co_code'],
                    'plo_mapping_course_plo_co_unique'
                );
            });
        }
    }

    private function indexExists(string $table, string $indexName): bool
    {
        if ($this->isSqlite()) {
            $rows = DB::select("PRAGMA index_list('{$table}')");
            return collect($rows)->contains(function ($row) use ($indexName) {
                $name = $row->name ?? ($row['name'] ?? null);
                return $name === $indexName;
            });
        }

        $rows = DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$indexName]);

        return !empty($rows);
    }

    private function foreignKeyExists(string $table, string $foreignKeyName): bool
    {
        if ($this->isSqlite()) {
            return false;
        }

        $rows = DB::table('information_schema.TABLE_CONSTRAINTS')
            ->where('CONSTRAINT_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', $table)
            ->where('CONSTRAINT_NAME', $foreignKeyName)
            ->where('CONSTRAINT_TYPE', 'FOREIGN KEY')
            ->count();

        return $rows > 0;
    }

    private function isSqlite(): bool
    {
        return DB::getDriverName() === 'sqlite';
    }
};
