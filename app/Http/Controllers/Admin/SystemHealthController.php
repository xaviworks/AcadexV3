<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use App\Models\User;
use App\Models\AuditLog;

class SystemHealthController extends Controller
{
    public function index()
    {
        return view('admin.system-health.index', [
            'serverMetrics' => $this->getServerMetrics(),
            'databaseMetrics' => $this->getDatabaseMetrics(),
            'storageMetrics' => $this->getStorageMetrics(),
            'queueMetrics' => $this->getQueueMetrics(),
            'applicationMetrics' => $this->getApplicationMetrics(),
            'recentErrors' => $this->getRecentErrors(),
        ]);
    }

    private function getServerMetrics()
    {
        // Get system load average
        $load = sys_getloadavg();
        
        // Get memory usage
        $memoryUsed = memory_get_usage(true);
        $memoryLimit = $this->parseMemoryLimit(ini_get('memory_limit'));
        $memoryPercent = $memoryLimit > 0 ? round(($memoryUsed / $memoryLimit) * 100, 2) : 0;
        
        // Get disk space
        $diskFree = disk_free_space(base_path());
        $diskTotal = disk_total_space(base_path());
        $diskUsed = $diskTotal - $diskFree;
        $diskPercent = $diskTotal > 0 ? round(($diskUsed / $diskTotal) * 100, 2) : 0;
        
        return [
            'cpu_load' => round($load[0], 2),
            'memory_used' => $this->formatBytes($memoryUsed),
            'memory_total' => $this->formatBytes($memoryLimit),
            'memory_percent' => $memoryPercent,
            'disk_used' => $this->formatBytes($diskUsed),
            'disk_total' => $this->formatBytes($diskTotal),
            'disk_free' => $this->formatBytes($diskFree),
            'disk_percent' => $diskPercent,
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
        ];
    }

    private function getDatabaseMetrics()
    {
        try {
            // Get database size
            $dbName = config('database.connections.mysql.database');
            $dbSize = DB::selectOne("
                SELECT 
                    SUM(data_length + index_length) as size
                FROM information_schema.TABLES 
                WHERE table_schema = ?
            ", [$dbName]);

            // Get table count
            $tableCount = DB::selectOne("
                SELECT COUNT(*) as count 
                FROM information_schema.TABLES 
                WHERE table_schema = ?
            ", [$dbName]);

            // Get connection count (if available)
            $connections = 0;
            try {
                $connectionInfo = DB::selectOne("SHOW STATUS LIKE 'Threads_connected'");
                $connections = $connectionInfo ? (int)$connectionInfo->Value : 0;
            } catch (\Exception $e) {
                // Skip if not accessible
            }

            // Test connection
            DB::connection()->getPdo();
            $status = 'healthy';

            return [
                'status' => $status,
                'size' => $this->formatBytes($dbSize->size ?? 0),
                'table_count' => $tableCount->count ?? 0,
                'connections' => $connections,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'error' => $e->getMessage(),
                'size' => 'N/A',
                'table_count' => 0,
                'connections' => 0,
            ];
        }
    }

    private function getStorageMetrics()
    {
        $storagePath = storage_path('app');
        $backupsPath = storage_path('app/backups');
        $logsPath = storage_path('logs');
        $publicPath = public_path('uploads');

        return [
            'total' => $this->getDirectorySize($storagePath),
            'backups' => File::exists($backupsPath) ? $this->getDirectorySize($backupsPath) : 0,
            'logs' => File::exists($logsPath) ? $this->getDirectorySize($logsPath) : 0,
            'uploads' => File::exists($publicPath) ? $this->getDirectorySize($publicPath) : 0,
        ];
    }

    private function getQueueMetrics()
    {
        try {
            // Count failed jobs
            $failedJobs = DB::table('failed_jobs')->count();
            
            // Count pending jobs (if using database queue)
            $pendingJobs = 0;
            if (Schema::hasTable('jobs')) {
                $pendingJobs = DB::table('jobs')->count();
            }

            return [
                'failed' => $failedJobs,
                'pending' => $pendingJobs,
                'status' => $failedJobs > 100 ? 'warning' : 'healthy',
            ];
        } catch (\Exception $e) {
            return [
                'failed' => 0,
                'pending' => 0,
                'status' => 'unknown',
            ];
        }
    }

    private function getApplicationMetrics()
    {
        $now = now();
        
        // Active users in last 15 minutes
        $activeUsers = DB::table('sessions')
            ->where('last_activity', '>=', $now->subMinutes(15)->timestamp)
            ->count();

        // Total users
        $totalUsers = User::count();

        // Today's activities
        $todayActivities = AuditLog::whereDate('created_at', $now->toDateString())->count();

        // Active sessions
        $activeSessions = DB::table('sessions')->count();

        return [
            'active_users' => $activeUsers,
            'total_users' => $totalUsers,
            'today_activities' => $todayActivities,
            'active_sessions' => $activeSessions,
        ];
    }

    private function getRecentErrors()
    {
        $logFile = storage_path('logs/laravel.log');
        
        if (!File::exists($logFile)) {
            return [];
        }

        $errors = [];
        $lines = file($logFile);
        $lines = array_reverse($lines);
        
        $errorCount = 0;
        foreach ($lines as $line) {
            if ($errorCount >= 5) break;
            
            if (preg_match('/\[(.*?)\] (\w+)\.(\w+): (.+)/', $line, $matches)) {
                if (in_array($matches[3], ['ERROR', 'CRITICAL', 'ALERT', 'EMERGENCY'])) {
                    $errors[] = [
                        'timestamp' => $matches[1],
                        'level' => $matches[3],
                        'message' => substr($matches[4], 0, 100),
                    ];
                    $errorCount++;
                }
            }
        }

        return $errors;
    }

    private function getDirectorySize($path)
    {
        if (!File::exists($path)) {
            return 0;
        }

        $size = 0;
        $files = File::allFiles($path);
        
        foreach ($files as $file) {
            $size += $file->getSize();
        }

        return $size;
    }

    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    private function parseMemoryLimit($limit)
    {
        if ($limit === '-1') {
            return PHP_INT_MAX;
        }

        $limit = trim($limit);
        $lastChar = strtolower($limit[strlen($limit) - 1]);
        $value = (int)$limit;

        switch ($lastChar) {
            case 'g':
                $value *= 1024 * 1024 * 1024;
                break;
            case 'm':
                $value *= 1024 * 1024;
                break;
            case 'k':
                $value *= 1024;
                break;
        }

        return $value;
    }

    public function clearCache()
    {
        try {
            \Artisan::call('cache:clear');
            \Artisan::call('config:clear');
            \Artisan::call('route:clear');
            \Artisan::call('view:clear');
            
            return redirect()->route('admin.system-health.index')
                ->with('success', 'All caches cleared successfully!');
        } catch (\Exception $e) {
            return redirect()->route('admin.system-health.index')
                ->with('error', 'Failed to clear cache: ' . $e->getMessage());
        }
    }

    public function optimizeDatabase()
    {
        try {
            $tables = DB::select('SHOW TABLES');
            $dbName = config('database.connections.mysql.database');
            
            foreach ($tables as $table) {
                $tableName = $table->{"Tables_in_$dbName"};
                DB::statement("OPTIMIZE TABLE `$tableName`");
            }
            
            return redirect()->route('admin.system-health.index')
                ->with('success', 'Database optimized successfully!');
        } catch (\Exception $e) {
            return redirect()->route('admin.system-health.index')
                ->with('error', 'Failed to optimize database: ' . $e->getMessage());
        }
    }
}
