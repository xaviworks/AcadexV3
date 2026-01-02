<?php

namespace App\Services\MongoDB;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * MongoDB Query Optimizer
 * 
 * Provides query optimization utilities to improve MongoDB performance.
 * 
 * RATIONALE:
 * - Prevents common performance anti-patterns
 * - Provides query result caching with proper invalidation
 * - Monitors and logs query performance metrics
 * - Suggests index optimizations based on query patterns
 */
class MongoQueryOptimizer
{
    /**
     * Cache a query result with automatic invalidation tags
     * 
     * @param string $cacheKey Unique cache key
     * @param callable $query Query callback
     * @param array $tags Cache tags for group invalidation
     * @param int $ttlMinutes Cache TTL in minutes
     * @return mixed Query result
     */
    public function cacheQuery(string $cacheKey, callable $query, array $tags = [], int $ttlMinutes = 60)
    {
        if (empty($tags)) {
            return Cache::remember($cacheKey, now()->addMinutes($ttlMinutes), $query);
        }

        return Cache::tags($tags)->remember($cacheKey, now()->addMinutes($ttlMinutes), $query);
    }

    /**
     * Invalidate cache by tags
     * 
     * @param array $tags Tags to invalidate
     */
    public function invalidateCache(array $tags): void
    {
        Cache::tags($tags)->flush();
        Log::info('Cache invalidated', ['tags' => $tags]);
    }

    /**
     * Execute query with performance tracking
     * 
     * @param callable $query Query callback
     * @param string $queryName Query identifier for logging
     * @param int $slowThresholdMs Slow query threshold in ms
     * @return mixed Query result
     */
    public function trackQueryPerformance(callable $query, string $queryName, int $slowThresholdMs = 100)
    {
        $startTime = microtime(true);
        $startMemory = memory_get_usage();

        $result = $query();

        $executionTime = (microtime(true) - $startTime) * 1000;
        $memoryUsed = memory_get_usage() - $startMemory;

        // Log slow queries
        if ($executionTime > $slowThresholdMs) {
            Log::warning('Slow MongoDB query detected', [
                'query_name' => $queryName,
                'execution_time_ms' => round($executionTime, 2),
                'memory_used_kb' => round($memoryUsed / 1024, 2),
                'threshold_ms' => $slowThresholdMs,
            ]);
        }

        // Log in debug mode
        if (config('app.debug')) {
            Log::debug('MongoDB query executed', [
                'query_name' => $queryName,
                'execution_time_ms' => round($executionTime, 2),
            ]);
        }

        return $result;
    }

    /**
     * Batch process large collections to avoid memory issues
     * 
     * @param callable $query Query that returns collection
     * @param callable $processor Processor for each batch
     * @param int $batchSize Number of documents per batch
     */
    public function batchProcess(callable $query, callable $processor, int $batchSize = 1000): array
    {
        $offset = 0;
        $totalProcessed = 0;
        $results = [];

        do {
            $batch = $query($offset, $batchSize);
            $batchCount = $batch->count();

            if ($batchCount > 0) {
                $batchResults = $processor($batch);
                $results = array_merge($results, $batchResults);
                $totalProcessed += $batchCount;
                $offset += $batchSize;
            }

        } while ($batchCount === $batchSize);

        Log::info('Batch processing completed', [
            'total_processed' => $totalProcessed,
            'batch_size' => $batchSize,
        ]);

        return $results;
    }

    /**
     * Get optimal batch size based on document size
     * 
     * @param int $avgDocumentSizeBytes Average document size in bytes
     * @param int $targetMemoryMB Target memory usage in MB
     * @return int Optimal batch size
     */
    public function calculateOptimalBatchSize(int $avgDocumentSizeBytes, int $targetMemoryMB = 50): int
    {
        $targetBytes = $targetMemoryMB * 1024 * 1024;
        $batchSize = (int) floor($targetBytes / $avgDocumentSizeBytes);

        // Clamp between 100 and 10000
        return max(100, min(10000, $batchSize));
    }

    /**
     * Check if a field should be indexed based on query patterns
     * 
     * @param string $collection Collection name
     * @param string $field Field name
     * @param int $queryCount Number of queries using this field
     * @param int $threshold Threshold for recommendation
     * @return bool Whether index is recommended
     */
    public function shouldIndexField(string $collection, string $field, int $queryCount, int $threshold = 10): bool
    {
        // If field is queried frequently, it should be indexed
        if ($queryCount >= $threshold) {
            Log::info('Index recommended', [
                'collection' => $collection,
                'field' => $field,
                'query_count' => $queryCount,
            ]);
            return true;
        }

        return false;
    }

    /**
     * Optimize query projection to reduce data transfer
     * 
     * @param array $fields Fields needed
     * @return array MongoDB projection
     */
    public function buildProjection(array $fields): array
    {
        $projection = [];
        foreach ($fields as $field) {
            $projection[$field] = 1;
        }
        return $projection;
    }

    /**
     * Generate efficient cache key for complex queries
     * 
     * @param string $prefix Cache key prefix
     * @param array $params Query parameters
     * @return string Cache key
     */
    public function generateCacheKey(string $prefix, array $params): string
    {
        // Sort params for consistent keys
        ksort($params);
        return $prefix . ':' . md5(serialize($params));
    }

    /**
     * Warm up cache with frequently accessed data
     * 
     * @param array $queries Array of [key => query] pairs
     * @param array $tags Cache tags
     * @param int $ttlMinutes Cache TTL
     */
    public function warmCache(array $queries, array $tags = [], int $ttlMinutes = 60): void
    {
        foreach ($queries as $key => $query) {
            $this->cacheQuery($key, $query, $tags, $ttlMinutes);
        }

        Log::info('Cache warmed', [
            'query_count' => count($queries),
            'tags' => $tags,
        ]);
    }
}
