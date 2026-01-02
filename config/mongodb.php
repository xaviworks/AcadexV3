<?php

return [

    /*
    |--------------------------------------------------------------------------
    | MongoDB Configuration
    |--------------------------------------------------------------------------
    |
    | Enable MongoDB query monitoring for performance tracking.
    | Set to false in production unless debugging performance issues.
    |
    */

    'mongodb_query_monitoring' => env('MONGODB_QUERY_MONITORING', false),

    /*
    |--------------------------------------------------------------------------
    | Enable MongoDB Hybrid Database
    |--------------------------------------------------------------------------
    |
    | Feature flag to enable/disable MongoDB hybrid database.
    | Set to false to rollback to MySQL-only mode.
    |
    */

    'enable_mongodb' => env('ENABLE_MONGODB', false),

    /*
    |--------------------------------------------------------------------------
    | Slow Query Threshold
    |--------------------------------------------------------------------------
    |
    | Threshold in milliseconds for slow query logging.
    | Queries exceeding this threshold will be logged.
    |
    */

    'slow_query_threshold_ms' => env('SLOW_QUERY_THRESHOLD_MS', 100),

    /*
    |--------------------------------------------------------------------------
    | Hybrid Query Cache Duration
    |--------------------------------------------------------------------------
    |
    | Cache duration in minutes for hybrid queries.
    | Reference data (subjects, courses) will be cached for this duration.
    |
    */

    'hybrid_query_cache_minutes' => env('HYBRID_QUERY_CACHE_MINUTES', 60),

    /*
    |--------------------------------------------------------------------------
    | Schema Migration Settings
    |--------------------------------------------------------------------------
    |
    | Settings for automatic schema migrations on MongoDB documents.
    |
    */

    'schema_migration' => [
        'auto_migrate_on_read' => env('MONGO_AUTO_MIGRATE', true),
        'batch_size' => env('MONGO_MIGRATION_BATCH_SIZE', 1000),
        'log_migrations' => env('MONGO_LOG_MIGRATIONS', true),
    ],

];
