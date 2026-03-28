<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')
            ->select(['id', 'two_factor_secret'])
            ->whereNotNull('two_factor_secret')
            ->orderBy('id')
            ->chunkById(100, function ($users): void {
                foreach ($users as $user) {
                    if (! is_string($user->two_factor_secret) || $user->two_factor_secret === '') {
                        continue;
                    }

                    if ($this->looksEncrypted($user->two_factor_secret)) {
                        continue;
                    }

                    DB::table('users')
                        ->where('id', $user->id)
                        ->update([
                            'two_factor_secret' => Crypt::encryptString($user->two_factor_secret),
                        ]);
                }
            });
    }

    public function down(): void
    {
        // Existing secrets should not be decrypted back to plaintext on rollback.
    }

    private function looksEncrypted(string $value): bool
    {
        $decoded = base64_decode($value, true);

        if ($decoded === false) {
            return false;
        }

        $payload = json_decode($decoded, true);

        return is_array($payload)
            && array_key_exists('iv', $payload)
            && array_key_exists('value', $payload)
            && array_key_exists('mac', $payload);
    }
};
