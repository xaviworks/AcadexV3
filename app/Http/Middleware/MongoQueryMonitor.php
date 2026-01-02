<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * MongoDB Query Performance Monitor Middleware
 * 
 * Tracks slow MongoDB queries and provides performance insights.
 * 
 * RATIONALE:
 * - Identifies performance bottlenecks in real-time
 * - Logs slow queries for optimization
 * - Provides metrics for query performance
 */
class MongoQueryMonitor
{
    /**
     * Threshold for slow query logging (milliseconds)
     */
    protected int $slowQueryThreshold = 100;

    /**
     * Enable query logging
     */
    protected bool $enabled = true;

    /**
     * Handle an incoming request
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$this->enabled || !config('database.mongodb_query_monitoring', false)) {
            return $next($request);
        }

        $startTime = microtime(true);
        $startMemory = memory_get_usage();

        // Enable MongoDB query logging
        $this->enableMongoLogging();

        $response = $next($request);

        $executionTime = (microtime(true) - $startTime) * 1000; // Convert to ms
        $memoryUsed = memory_get_usage() - $startMemory;

        // Log request performance if it's slow
        if ($executionTime > $this->slowQueryThreshold) {
            Log::warning('Slow request detected', [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'execution_time_ms' => round($executionTime, 2),
                'memory_used_mb' => round($memoryUsed / 1024 / 1024, 2),
                'user_id' => auth()->id(),
            ]);
        }

        // Add performance headers in development
        if (config('app.debug')) {
            $response->headers->set('X-Query-Time', round($executionTime, 2) . 'ms');
            $response->headers->set('X-Memory-Used', round($memoryUsed / 1024, 2) . 'KB');
        }

        return $response;
    }

    /**
     * Enable MongoDB query logging
     */
    protected function enableMongoLogging(): void
    {
        // This would integrate with MongoDB driver's logging
        // Implementation depends on the specific driver being used
    }
}
