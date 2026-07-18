<?php

namespace App\Support\Database;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class PublicUuid
{
    /**
     * @var array<string, bool>
     */
    private static array $tablesWithUuid = [];

    public static function fill(Model $model): void
    {
        if ($model->getAttribute('uuid') || ! self::tableHasUuid($model)) {
            return;
        }

        $model->setAttribute('uuid', (string) Str::uuid());
    }

    private static function tableHasUuid(Model $model): bool
    {
        $connection = $model->getConnectionName() ?? config('database.default');
        $table = $model->getTable();
        $key = "{$connection}.{$table}";

        if (! array_key_exists($key, self::$tablesWithUuid)) {
            self::$tablesWithUuid[$key] = Schema::connection($connection)->hasColumn($table, 'uuid');
        }

        return self::$tablesWithUuid[$key];
    }
}
