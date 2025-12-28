<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Backup;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use ZipArchive;

class BackupService
{
    /**
     * Directory where backups are stored (relative to storage/).
     */
    protected string $backupDir = 'app/backups';

    /**
     * Tables that should always be backed up.
     */
    protected array $coreTables = [
        'users',
        'departments',
        'courses',
        'subjects',
        'academic_periods',
        'grades_formula',
        'grades_formula_weights',
        'structure_templates',
    ];

    /**
     * Tables that contain grade/score data.
     */
    protected array $dataTables = [
        'activities',
        'scores',
        'term_grades',
        'final_grades',
        'students',
        'enrollments',
        'course_outcomes',
        'course_outcome_attainments',
    ];

    /**
     * Get all available tables for backup.
     */
    public function getAvailableTables(): array
    {
        $tables = Schema::getTableListing();
        
        // Normalize table names: keep only the actual table name (last part)
        // This handles 3-part references and unexpected prefixes like '0.cache'
        $tables = array_map(function ($table) {
            return Str::afterLast($table, '.');
        }, $tables);

        // Filter out Laravel system tables
        $exclude = ['migrations', 'password_reset_tokens', 'personal_access_tokens', 'failed_jobs', 'jobs', 'job_batches', 'cache', 'cache_locks'];
        
        return array_values(array_diff($tables, $exclude));
    }

    /**
     * Get tables grouped by category.
     */
    public function getTableGroups(): array
    {
        return [
            'core' => [
                'label' => 'Core Configuration',
                'tables' => $this->coreTables,
                'description' => 'Essential system configuration (users, departments, formulas)',
            ],
            'data' => [
                'label' => 'Grade Data',
                'tables' => $this->dataTables,
                'description' => 'Student grades, scores, and activities',
            ],
            'all' => [
                'label' => 'Full Database',
                'tables' => $this->getAvailableTables(),
                'description' => 'Complete database backup',
            ],
        ];
    }

    /**
     * Create a full database backup.
     */
    public function createFullBackup(?User $user, ?string $notes = null): Backup
    {
        return $this->createBackup(
            $user,
            Backup::TYPE_FULL,
            $this->getAvailableTables(),
            'Full Database Backup',
            $notes
        );
    }

    /**
     * Create a selective backup with specific tables.
     */
    public function createSelectiveBackup(?User $user, array $tables, ?string $name = null, ?string $notes = null): Backup
    {
        $name = $name ?? 'Selective Backup (' . count($tables) . ' tables)';
        
        return $this->createBackup(
            $user,
            Backup::TYPE_SELECTIVE,
            $tables,
            $name,
            $notes
        );
    }

    /**
     * Create a configuration-only backup.
     */
    public function createConfigBackup(?User $user, ?string $notes = null): Backup
    {
        return $this->createBackup(
            $user,
            Backup::TYPE_CONFIG,
            $this->coreTables,
            'Configuration Backup',
            $notes
        );
    }

    /**
     * Create a backup.
     */
    protected function createBackup(?User $user, string $type, array $tables, string $name, ?string $notes): Backup
    {
        $timestamp = now()->format('Y-m-d_H-i-s');
        $filename = Str::slug($name) . '_' . $timestamp . '.zip';
        $relativePath = $this->backupDir . '/' . $filename;
        $fullPath = storage_path($relativePath);

        // Ensure backup directory exists
        $this->ensureBackupDirectory();

        // Create backup record
        $backup = Backup::create([
            'name' => $name,
            'type' => $type,
            'filename' => $filename,
            'path' => $relativePath,
            'tables' => $tables,
            'status' => Backup::STATUS_PENDING,
            'notes' => $notes,
            'created_by' => $user?->id,
        ]);

        try {
            // Create the backup
            $this->performBackup($backup, $tables, $fullPath);

            // Update backup record
            $backup->update([
                'status' => Backup::STATUS_COMPLETED,
                'size' => filesize($fullPath),
                'completed_at' => now(),
            ]);

            // Create audit log
            AuditLog::create([
                'user_id' => $user?->id,
                'auditable_type' => Backup::class,
                'auditable_id' => $backup->id,
                'event' => 'created',
                'new_values' => [
                    'name' => $backup->name,
                    'type' => $backup->type,
                    'size' => $backup->size_formatted,
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            Log::info('Backup created successfully', [
                'backup_id' => $backup->id,
                'type' => $type,
                'tables' => count($tables),
                'size' => $backup->size_formatted,
            ]);

        } catch (\Throwable $e) {
            // Update backup as failed
            $backup->update([
                'status' => Backup::STATUS_FAILED,
                'error_message' => $e->getMessage(),
            ]);

            Log::error('Backup failed', [
                'backup_id' => $backup->id,
                'error' => $e->getMessage(),
            ]);

            // Clean up partial file if exists
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }

            throw $e;
        }

        return $backup->fresh();
    }

    /**
     * Perform the actual backup.
     */
    protected function performBackup(Backup $backup, array $tables, string $zipPath): void
    {
        $zip = new ZipArchive();
        
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('Could not create backup archive');
        }

        try {
            $manifest = [
                'created_at' => now()->toIso8601String(),
                'backup_id' => $backup->id,
                'type' => $backup->type,
                'tables' => [],
                'app_version' => config('app.version', '1.0.0'),
                'laravel_version' => app()->version(),
            ];

            foreach ($tables as $table) {
                if (! Schema::hasTable($table)) {
                    continue;
                }

                // Export table data as JSON
                $data = DB::table($table)->get()->toArray();
                $jsonData = json_encode($data, JSON_PRETTY_PRINT);
                
                $zip->addFromString("data/{$table}.json", $jsonData);

                // Export table schema
                $schema = $this->getTableSchema($table);
                $zip->addFromString("schema/{$table}.json", json_encode($schema, JSON_PRETTY_PRINT));

                $manifest['tables'][$table] = [
                    'row_count' => count($data),
                    'exported_at' => now()->toIso8601String(),
                ];
            }

            // Add manifest
            $zip->addFromString('manifest.json', json_encode($manifest, JSON_PRETTY_PRINT));

        } finally {
            $zip->close();
        }
    }

    /**
     * Get table schema information.
     */
    protected function getTableSchema(string $table): array
    {
        $columns = Schema::getColumnListing($table);
        $schema = [];

        foreach ($columns as $column) {
            $schema[$column] = [
                'type' => Schema::getColumnType($table, $column),
            ];
        }

        return $schema;
    }

    /**
     * Delete a backup.
     */
    public function deleteBackup(Backup $backup): bool
    {
        $fullPath = $backup->getFullPath();

        if (file_exists($fullPath)) {
            unlink($fullPath);
        }

        $backup->delete();

        Log::info('Backup deleted', ['backup_id' => $backup->id]);

        return true;
    }

    /**
     * Get backup content for preview.
     */
    public function previewBackup(Backup $backup): array
    {
        $fullPath = $backup->getFullPath();

        if (! file_exists($fullPath)) {
            throw new \RuntimeException('Backup file not found');
        }

        $zip = new ZipArchive();
        
        if ($zip->open($fullPath) !== true) {
            throw new \RuntimeException('Could not open backup archive');
        }

        $preview = [];

        try {
            // Read manifest
            $manifestContent = $zip->getFromName('manifest.json');
            if ($manifestContent) {
                $preview['manifest'] = json_decode($manifestContent, true);
            }

            // Get sample data from each table
            $preview['tables'] = [];
            foreach ($backup->tables ?? [] as $table) {
                $dataContent = $zip->getFromName("data/{$table}.json");
                if ($dataContent) {
                    $data = json_decode($dataContent, true);
                    $preview['tables'][$table] = [
                        'row_count' => count($data),
                        'sample' => array_slice($data, 0, 3), // First 3 rows
                    ];
                }
            }

        } finally {
            $zip->close();
        }

        return $preview;
    }

    /**
     * Ensure backup directory exists.
     */
    protected function ensureBackupDirectory(): void
    {
        $path = storage_path($this->backupDir);
        
        if (! File::isDirectory($path)) {
            File::makeDirectory($path, 0755, true);
        }
    }

    /**
     * Get storage usage information.
     */
    public function getStorageInfo(): array
    {
        $path = storage_path($this->backupDir);
        
        if (! File::isDirectory($path)) {
            return [
                'total_size' => 0,
                'total_size_formatted' => '0 bytes',
                'backup_count' => 0,
            ];
        }

        $totalSize = 0;
        $files = File::files($path);
        
        foreach ($files as $file) {
            $totalSize += $file->getSize();
        }

        return [
            'total_size' => $totalSize,
            'total_size_formatted' => $this->formatBytes($totalSize),
            'backup_count' => count($files),
        ];
    }

    /**
     * Format bytes to human readable.
     */
    protected function formatBytes(int $bytes): string
    {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        }
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        }
        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }
        
        return $bytes . ' bytes';
    }

    /**
     * Clean up old backups (keep last N).
     */
    public function pruneBackups(int $keep = 10): int
    {
        $backups = Backup::completed()
            ->orderByDesc('created_at')
            ->skip($keep)
            ->get();

        $count = 0;
        foreach ($backups as $backup) {
            $this->deleteBackup($backup);
            $count++;
        }

        return $count;
    }
}
