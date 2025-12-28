<?php

namespace App\Console\Commands;

use App\Models\Backup;
use App\Services\BackupService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ScheduledBackupCommand extends Command
{
    protected $signature = 'backup:run {--type=full : Backup type (full or config)}';

    protected $description = 'Run scheduled automatic backup';

    public function handle(BackupService $backupService): int
    {
        $type = $this->option('type');
        
        $this->info("Starting scheduled {$type} backup...");

        try {
            $backup = match ($type) {
                'config' => $backupService->createConfigBackup(null, 'Scheduled automatic config backup'),
                default => $backupService->createFullBackup(null, 'Scheduled automatic full backup'),
            };

            $this->info("Backup completed: {$backup->name}");
            $this->info("Size: {$backup->size_formatted}");
            $this->info("Tables: " . count($backup->tables ?? []));

            Log::info('Scheduled backup completed', [
                'backup_id' => $backup->id,
                'type' => $type,
                'size' => $backup->size,
            ]);

            return Command::SUCCESS;

        } catch (\Throwable $e) {
            $this->error("Backup failed: {$e->getMessage()}");
            Log::error('Scheduled backup failed', ['error' => $e->getMessage()]);
            
            return Command::FAILURE;
        }
    }
}
