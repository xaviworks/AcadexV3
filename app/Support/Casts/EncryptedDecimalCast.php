<?php

namespace App\Support\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

/**
 * Encrypts decimal/numeric values for storage and decrypts on retrieval.
 * 
 * This cast provides optional encryption for sensitive numeric data like grades.
 * It can be enabled/disabled via the ENCRYPT_GRADES environment variable.
 * 
 * Usage:
 *   protected $casts = [
 *       'score' => EncryptedDecimalCast::class,
 *   ];
 * 
 * Configuration:
 *   Set ENCRYPT_GRADES=true in .env to enable encryption.
 *   When disabled, values are stored as plain decimals for backward compatibility.
 * 
 * Migration note:
 *   If enabling encryption on existing data, you'll need to run a migration
 *   to encrypt existing plain values. See docs for migration script.
 */
class EncryptedDecimalCast implements CastsAttributes
{
    /**
     * The number of decimal places to preserve.
     */
    protected int $decimals = 2;

    /**
     * Transform the attribute from the underlying model values.
     *
     * @param  Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array<string, mixed>  $attributes
     * @return float|null
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): ?float
    {
        if ($value === null) {
            return null;
        }

        // If encryption is disabled, return as-is
        if (!$this->isEncryptionEnabled()) {
            return (float) $value;
        }

        // Try to decrypt - if it fails, the value is probably not encrypted (plain value)
        try {
            // Check if value looks encrypted (base64 format with our prefix)
            if ($this->looksEncrypted($value)) {
                $decrypted = Crypt::decryptString($value);
                return (float) $decrypted;
            }
            
            // Value is not encrypted, return as-is (backward compatibility)
            return (float) $value;
        } catch (\Exception $e) {
            // Decryption failed - value is probably plain, return as-is
            return is_numeric($value) ? (float) $value : null;
        }
    }

    /**
     * Transform the attribute to its underlying model values.
     *
     * @param  Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array<string, mixed>  $attributes
     * @return string|float|null
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): string|float|null
    {
        if ($value === null) {
            return null;
        }

        // Ensure value is numeric
        $numericValue = round((float) $value, $this->decimals);

        // If encryption is disabled, store as plain decimal
        if (!$this->isEncryptionEnabled()) {
            return $numericValue;
        }

        // Encrypt the value
        return Crypt::encryptString((string) $numericValue);
    }

    /**
     * Check if grade encryption is enabled.
     */
    protected function isEncryptionEnabled(): bool
    {
        return config('app.encrypt_grades', false);
    }

    /**
     * Check if a value looks like it's encrypted.
     * 
     * Laravel's encrypted strings are base64-encoded JSON starting with "eyJ"
     */
    protected function looksEncrypted(mixed $value): bool
    {
        if (!is_string($value)) {
            return false;
        }

        // Laravel encryption produces base64 strings that typically start with "eyJ"
        // and are much longer than a typical decimal value
        return strlen($value) > 50 && preg_match('/^[A-Za-z0-9+\/=]+$/', $value);
    }
}
