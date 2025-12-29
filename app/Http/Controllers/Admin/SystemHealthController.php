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
            'recentErrors' => $this->getRecentErrors(),
        ]);
    }

    private function getServerMetrics()
    {
        // Get system load average
        $load = sys_getloadavg();
        $cpuLoad = round($load[0] * 100, 2); // Convert to percentage
        
        // Get memory usage
        $memoryUsed = memory_get_usage(true);
        $memoryLimit = $this->parseMemoryLimit(ini_get('memory_limit'));
        $memoryUsage = $memoryLimit > 0 ? round(($memoryUsed / $memoryLimit) * 100, 2) : 0;
        
        // Get disk space
        $diskFree = disk_free_space(base_path());
        $diskTotal = disk_total_space(base_path());
        $diskUsed = $diskTotal - $diskFree;
        $diskUsage = $diskTotal > 0 ? round(($diskUsed / $diskTotal) * 100, 2) : 0;
        
        return [
            'cpu_load' => $cpuLoad,
            'memory_usage' => $memoryUsage,
            'disk_usage' => $diskUsage,
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

            return [
                'database_size' => $this->formatBytes($dbSize->size ?? 0),
                'total_tables' => $tableCount->count ?? 0,
            ];
        } catch (\Exception $e) {
            return [
                'database_size' => 'N/A',
                'total_tables' => 0,
            ];
        }
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
                        'time' => $matches[1],
                        'message' => trim($matches[4]),
                    ];
                    $errorCount++;
                }
            }
        }

        return $errors;
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
