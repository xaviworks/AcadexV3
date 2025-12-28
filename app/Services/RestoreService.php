<?php

namespace App\Services;

use App\Models\Backup;
use App\Models\AuditLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use ZipArchive;

class RestoreService
{
    /**
     * Tables that should be restored in specific order (dependencies).
     */
    protected array $restoreOrder = [
        'departments',
        'courses',
        'users',
        'academic_periods',
        'subjects',
        'grades_formula',
        'grades_formula_weights',
        'structure_templates',
        'students',
        'enrollments',
        'activities',
        'scores',
        'term_grades',
        'final_grades',
        'course_outcomes',
        'course_outcome_attainments',
    ];

    /**
     * Restore from a backup.
     */
    public function restoreFromBackup(Backup $backup, array $options = []): array
    {
        $fullPath = $backup->getFullPath();

        if (! file_exists($fullPath)) {
            throw new \RuntimeException('Backup file not found');
        }

        $results = [
            'success' => true,
            'tables_restored' => [],
            'tables_skipped' => [],
            'errors' => [],
            'started_at' => now()->toIso8601String(),
        ];

        $zip = new ZipArchive();
        
        if ($zip->open($fullPath) !== true) {
            throw new \RuntimeException('Could not open backup archive');
        }

        DB::beginTransaction();

        try {
            // Read manifest
            $manifestContent = $zip->getFromName('manifest.json');
            $manifest = $manifestContent ? json_decode($manifestContent, true) : null;

            // Get tables to restore (in correct order)
            $tablesToRestore = $this->getOrderedTables($backup->tables ?? []);
            $tablesToRestore = array_filter($tablesToRestore, function ($table) use ($options) {
                // Always exclude system metadata tables from restore to preserve history
                if (in_array($table, ['backups', 'audit_logs'])) {
                    return false;
                }

                // Skip if specific tables requested and this isn't one
                if (! empty($options['tables']) && ! in_array($table, $options['tables'])) {
                    return false;
                }
                return true;
            });

            // Disable foreign key checks during restore
            DB::statement('SET FOREIGN_KEY_CHECKS=0');

            foreach ($tablesToRestore as $table) {
                try {
                    $dataContent = $zip->getFromName("data/{$table}.json");
                    
                    if (! $dataContent) {
                        $results['tables_skipped'][] = $table;
                        continue;
                    }

                    $data = json_decode($dataContent, true);

                    if (! is_array($data)) {
                        $results['errors'][] = "Invalid data format for table: {$table}";
                        continue;
                    }

                    // Clear existing data if requested
                    if ($options['clear_existing'] ?? true) {
                        // Use delete() instead of truncate() because truncate causes implicit commit in MySQL
                        // which breaks the transaction handling
                        DB::table($table)->delete();
                    }

                    // Insert data in chunks
                    $chunks = array_chunk($data, 500);
                    foreach ($chunks as $chunk) {
                        // Convert objects to arrays
                        $chunk = array_map(function ($item) {
                            return (array) $item;
                        }, $chunk);
                        
                        DB::table($table)->insert($chunk);
                    }

                    $results['tables_restored'][$table] = count($data);

                    Log::info("Restored table: {$table}", ['rows' => count($data)]);

                } catch (\Throwable $e) {
                    $results['errors'][] = "Error restoring {$table}: " . $e->getMessage();
                    Log::error("Error restoring table: {$table}", ['error' => $e->getMessage()]);
                }
            }

            // Re-enable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=1');

            DB::commit();

            $results['completed_at'] = now()->toIso8601String();

            // Log the restore action
            AuditLog::create([
                'user_id' => auth()->id(),
                'auditable_type' => Backup::class,
                'auditable_id' => $backup->id,
                'event' => 'restored',
                'old_values' => null,
                'new_values' => [
                    'tables_restored' => array_keys($results['tables_restored']),
                    'total_rows' => array_sum($results['tables_restored']),
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'metadata' => ['options' => $options],
            ]);

            Log::info('Backup restored successfully', [
                'backup_id' => $backup->id,
                'tables' => count($results['tables_restored']),
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
            
            $results['success'] = false;
            $results['errors'][] = $e->getMessage();
            
            Log::error('Restore failed', [
                'backup_id' => $backup->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        } finally {
            $zip->close();
        }

        return $results;
    }

    /**
     * Get tables in correct restore order.
     */
    protected function getOrderedTables(array $tables): array
    {
        $ordered = [];
        
        // First, add tables in defined order
        foreach ($this->restoreOrder as $table) {
            if (in_array($table, $tables)) {
                $ordered[] = $table;
            }
        }
        
        // Then add any remaining tables
        foreach ($tables as $table) {
            if (! in_array($table, $ordered)) {
                $ordered[] = $table;
            }
        }
        
        return $ordered;
    }

    /**
     * Dry run restore to see what would happen.
     */
    public function dryRunRestore(Backup $backup, array $options = []): array
    {
        $fullPath = $backup->getFullPath();

        if (! file_exists($fullPath)) {
            throw new \RuntimeException('Backup file not found');
        }

        $preview = [
            'backup' => [
                'id' => $backup->id,
                'name' => $backup->name,
                'type' => $backup->type,
                'created_at' => $backup->created_at->toIso8601String(),
            ],
            'tables' => [],
            'warnings' => [],
        ];

        $zip = new ZipArchive();
        
        if ($zip->open($fullPath) !== true) {
            throw new \RuntimeException('Could not open backup archive');
        }

        try {
            foreach ($backup->tables ?? [] as $table) {
                if (! Schema::hasTable($table)) {
                    $preview['warnings'][] = "Table '{$table}' does not exist in current database";
                    continue;
                }

                $dataContent = $zip->getFromName("data/{$table}.json");
                
                if (! $dataContent) {
                    continue;
                }

                $backupData = json_decode($dataContent, true);
                $currentCount = DB::table($table)->count();

                $preview['tables'][$table] = [
                    'backup_rows' => count($backupData),
                    'current_rows' => $currentCount,
                    'will_replace' => $options['clear_existing'] ?? true,
                ];
            }

        } finally {
            $zip->close();
        }

        return $preview;
    }

    /**
     * Rollback to a specific audit log entry.
     */
    public function rollbackToAuditLog(AuditLog $auditLog): bool
    {
        if (! $auditLog->old_values) {
            throw new \RuntimeException('No previous values to restore');
        }

        DB::beginTransaction();

        try {
            $modelClass = $auditLog->auditable_type;
            $modelId = $auditLog->auditable_id;

            if (! class_exists($modelClass)) {
                throw new \RuntimeException("Model class not found: {$modelClass}");
            }

            $model = $modelClass::find($modelId);

            if (! $model) {
                // Model was deleted, try to restore
                if ($auditLog->event === AuditLog::EVENT_DELETED) {
                    $model = new $modelClass();
                    $model->fill($auditLog->old_values);
                    $model->save();
                } else {
                    throw new \RuntimeException("Model not found: {$modelClass}#{$modelId}");
                }
            } else {
                // Update to old values
                $model->fill($auditLog->old_values);
                $model->save();
            }

            // Log the rollback
            AuditLog::log($model, AuditLog::EVENT_RESTORED, $auditLog->new_values, $auditLog->old_values, [
                'rollback_from_audit_id' => $auditLog->id,
            ]);

            DB::commit();

            Log::info('Rollback successful', [
                'audit_id' => $auditLog->id,
                'model' => $modelClass,
                'model_id' => $modelId,
            ]);

            return true;

        } catch (\Throwable $e) {
            DB::rollBack();
            
            Log::error('Rollback failed', [
                'audit_id' => $auditLog->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
