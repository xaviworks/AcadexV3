<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Backup;
use App\Models\User;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ZipArchive;

class BackupService
{
    /**
     * Directory where backups are stored (relative to storage/).
     */
    protected string $backupDir;

    protected string $disk;

    public function __construct()
    {
        $this->backupDir = trim(config('backup.path', 'backups'), '/');
        $this->disk = config('backup.disk', 'local');

        if (app()->environment('production') && config('backup.require_remote_in_production', true) && $this->disk === 'local') {
            throw new \LogicException('BACKUP_DISK must be configured to an off-server filesystem in production.');
        }
    }

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
        $exclude = ['migrations', 'password_reset_tokens', 'personal_access_tokens', 'failed_jobs', 'jobs', 'job_batches', 'cache', 'cache_locks', 'backups', 'audit_logs'];

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
        $name = $name ?? 'Selective Backup ('.count($tables).' tables)';

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
        $filename = Str::slug($name).'_'.$timestamp.'.zip.enc';
        $relativePath = $this->backupDir.'/'.$filename;
        $disk = Storage::disk($this->disk);
        $temporaryZip = tempnam(sys_get_temp_dir(), 'acadex-backup-');

        // Ensure backup directory exists
        // Create backup record
        $backup = Backup::create([
            'name' => $name,
            'type' => $type,
            'filename' => $filename,
            'path' => $relativePath,
            'disk' => $this->disk,
            'encrypted' => true,
            'tables' => $tables,
            'status' => Backup::STATUS_PENDING,
            'notes' => $notes,
            'created_by' => $user?->id,
        ]);

        try {
            // Create the backup
            $this->performBackup($backup, $tables, $temporaryZip);
            $plaintext = file_get_contents($temporaryZip);
            if ($plaintext === false) {
                throw new \RuntimeException('Could not read backup archive.');
            }
            $encrypted = Crypt::encryptString($plaintext);
            if (! $disk->put($relativePath, $encrypted)) {
                throw new \RuntimeException('Could not store backup on configured disk.');
            }
            $checksum = hash('sha256', $encrypted);

            // Update backup record
            $backup->update([
                'status' => Backup::STATUS_COMPLETED,
                'size' => strlen($encrypted),
                'checksum' => $checksum,
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
            if ($disk->exists($relativePath)) {
                $disk->delete($relativePath);
            }

            throw $e;
        } finally {
            if (is_string($temporaryZip) && file_exists($temporaryZip)) {
                unlink($temporaryZip);
            }
        }

        return $backup->fresh();
    }

    /**
     * Perform the actual backup.
     */
    protected function performBackup(Backup $backup, array $tables, string $zipPath): void
    {
        $zip = new ZipArchive;

        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('Could not create backup archive');
        }

        try {
            $manifest = [
                'created_at' => now()->toIso8601String(),
                'backup_id' => $backup->id,
                'type' => $backup->type,
                'format_version' => 2,
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
                $jsonData = json_encode($data, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);

                // Export table schema
                $schema = $this->getTableSchema($table);
                $schemaData = json_encode($schema, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);

                $zip->addFromString("data/{$table}.json", $jsonData);
                $zip->addFromString("schema/{$table}.json", $schemaData);

                $manifest['tables'][$table] = [
                    'row_count' => count($data),
                    'data_sha256' => hash('sha256', $jsonData),
                    'schema_sha256' => hash('sha256', $schemaData),
                    'exported_at' => now()->toIso8601String(),
                ];
            }

            // Add manifest
            $zip->addFromString('manifest.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));

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
        $disk = Storage::disk($backup->disk ?: $this->disk);
        if ($disk->exists($backup->path)) {
            $disk->delete($backup->path);
        } elseif ($backup->disk === 'local' && str_starts_with($backup->path, 'app/')) {
            $legacyPath = storage_path($backup->path);
            if (file_exists($legacyPath)) {
                unlink($legacyPath);
            }
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
        if (! $backup->fileExists()) {
            throw new \RuntimeException('Backup file not found');
        }

        $fullPath = $this->materializeZip($backup);
        $zip = new ZipArchive;

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
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }
        }

        return $preview;
    }

    /**
     * Get storage usage information.
     */
    public function getStorageInfo(): array
    {
        $disk = Storage::disk($this->disk);

        if (! $disk->directoryExists($this->backupDir)) {
            return [
                'total_size' => 0,
                'total_size_formatted' => '0 bytes',
                'backup_count' => 0,
            ];
        }

        $totalSize = 0;
        $files = $disk->files($this->backupDir);

        foreach ($files as $file) {
            $totalSize += $disk->size($file);
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
            return number_format($bytes / 1073741824, 2).' GB';
        }
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2).' MB';
        }
        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 2).' KB';
        }

        return $bytes.' bytes';
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

    /** Return the encrypted artifact after verifying its stored checksum. */
    public function getVerifiedContents(Backup $backup): string
    {
        $disk = Storage::disk($backup->disk ?: $this->disk);
        $contents = $disk->exists($backup->path)
            ? $disk->get($backup->path)
            : (str_starts_with($backup->path, 'app/') ? file_get_contents(storage_path($backup->path)) : false);
        if ($contents === false) {
            throw new \RuntimeException('Backup file not found.');
        }
        if ($backup->checksum && ! hash_equals($backup->checksum, hash('sha256', $contents))) {
            throw new \RuntimeException('Backup integrity check failed: checksum mismatch.');
        }

        return $contents;
    }

    /** Decrypt a verified backup into a temporary ZIP file. */
    public function materializeZip(Backup $backup): string
    {
        $contents = $this->getVerifiedContents($backup);
        try {
            $zipContents = $backup->encrypted ? Crypt::decryptString($contents) : $contents;
        } catch (\Throwable $e) {
            throw new \RuntimeException('Backup decryption failed. The encryption key may be incorrect.', 0, $e);
        }
        $path = tempnam(sys_get_temp_dir(), 'acadex-restore-');
        file_put_contents($path, $zipContents);

        return $path;
    }
}
