<?php

return [
    // Configure this to an S3-compatible disk in production. Local storage is
    // intentionally available for development and test environments only.
    'disk' => env('BACKUP_DISK', env('FILESYSTEM_DISK', 'local')),
    'path' => env('BACKUP_PATH', 'backups'),
    'retention' => (int) env('BACKUP_RETENTION', 30),
    'encryption_key' => env('BACKUP_ENCRYPTION_KEY'),
    'require_remote_in_production' => (bool) env('BACKUP_REQUIRE_REMOTE_IN_PRODUCTION', true),
];
