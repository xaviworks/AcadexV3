<?php

// One-time utility for decrypting an Acadex .zip.enc backup.
// Usage:
//   php scripts/decrypt-backup.php
//   php scripts/decrypt-backup.php /path/to/backup.zip.enc /path/to/output.zip
//   php scripts/decrypt-backup.php --force

declare(strict_types=1);

require dirname(__DIR__).'/vendor/autoload.php';

$app = require dirname(__DIR__).'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Crypt;

$arguments = array_values(array_slice($argv, 1));
$force = in_array('--force', $arguments, true);
$arguments = array_values(array_filter($arguments, fn (string $argument): bool => $argument !== '--force'));

$source = $arguments[0] ?? null;
if ($source === null) {
    $downloads = rtrim((string) ($_SERVER['HOME'] ?? ''), '/').'/Downloads';
    $matches = glob($downloads.'/full-database-backup_*.zip.enc') ?: [];
    usort($matches, fn (string $a, string $b): int => filemtime($b) <=> filemtime($a));
    $source = $matches[0] ?? null;
}

if ($source === null && function_exists('readline')) {
    $source = trim((string) readline('Enter the full path to the encrypted backup (.zip.enc): '));
}

if (! is_string($source) || ! is_file($source)) {
    fwrite(STDERR, "Encrypted backup not found.\n");
    exit(1);
}

$destination = $arguments[1] ?? preg_replace('/\.enc$/', '', $source);
if (! is_string($destination) || $destination === $source) {
    fwrite(STDERR, "Could not determine a decrypted output path.\n");
    exit(1);
}

if (is_file($destination) && ! $force) {
    fwrite(STDERR, "Output already exists: {$destination}\nUse --force to overwrite it.\n");
    exit(1);
}

try {
    $encrypted = file_get_contents($source);
    if ($encrypted === false) {
        throw new RuntimeException('Could not read the encrypted backup.');
    }

    $zip = Crypt::decryptString($encrypted);
    if (file_put_contents($destination, $zip, LOCK_EX) === false) {
        throw new RuntimeException('Could not write the decrypted ZIP.');
    }
} catch (Throwable $exception) {
    fwrite(STDERR, "Decryption failed: {$exception->getMessage()}\n");
    exit(1);
}

fwrite(STDOUT, "Decrypted backup written to: {$destination}\n");
