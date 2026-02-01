<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SystemHealthController extends Controller
{
    public function index(Request $request)
    {
        if ($request->query('key') !== env('SYSTEM_MONITOR_KEY')) {
            abort(403, 'Unauthorized');
        }

        // CPU Load (Compatible with Shared Hosting / Linux)
        $cpuLoad = $this->getServerLoad();

        // Memory Usage
        $memoryUsage = $this->getMemoryUsage();

        // Disk Usage
        $diskUsage = $this->getDiskUsage();

        // Database Connection & Version
        $dbStatus = $this->getDatabaseStatus();

        return response()->json([
            'status' => 'ok',
            'timestamp' => now()->toIso8601String(),
            'server' => [
                'php_version' => PHP_VERSION,
                'software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
                'laravel_version' => app()->version(),
                'ip' => $_SERVER['SERVER_ADDR'] ?? '127.0.0.1',
            ],
            'system' => [
                'cpu_load' => $cpuLoad,
                'memory' => $memoryUsage,
                'disk' => $diskUsage,
            ],
            'database' => $dbStatus,
        ]);
    }

    private function getServerLoad()
    {
        $load = 0;

        if (stristr(PHP_OS, 'win')) {
            // Windows
            $cmd = 'wmic cpu get loadpercentage /value';
            $output = [];
            @exec($cmd, $output);
            
            foreach ($output as $line) {
                if (preg_match('/^LoadPercentage=(\d+)/', $line, $matches)) {
                    $load = (int) $matches[1];
                    break;
                }
            }
        } else {
            // Linux / Unix / Shared Hosting
            $sys_load = sys_getloadavg();
            // loadavg is for 1, 5, 15 min. We take 1 min.
            // On shared hosting, this might be the load of the WHOLE server.
            // There is no perfect way to get "user-only" cpu load without shell access to top/ps.
            $load = isset($sys_load[0]) ? $sys_load[0] : 0; 
            
            // Normalize: loadavg is number of processes. To get %, we need core count.
            // Assuming 1 core if unknown (safe fallback for visualization) or just passing raw if > 100
            // For visualization purposes, let's try to map it to 0-100 range loosely 
            // or just return the raw load if it's small.
            // Many dashboards just show loadavg as is. 
            // Let's cap at 100 for percentage bar, but send raw value too.
            if ($load > 100) $load = 100; // Cap for bar
        }

        return $load;
    }

    private function getMemoryUsage()
    {
        $memoryTotal = 0;
        $memoryFree = 0;
        $memoryUsed = 0;
        $isSimulated = false;

        if (stristr(PHP_OS, 'win')) {
            // Get total physical memory
            $cmd = 'wmic ComputerSystem get TotalPhysicalMemory /value';
            @exec($cmd, $outputTotal);
            foreach ($outputTotal as $line) {
                if (preg_match('/^TotalPhysicalMemory=(\d+)/', $line, $matches)) {
                    $memoryTotal = (int) $matches[1];
                }
            }

            // Get free physical memory
            $cmd = 'wmic OS get FreePhysicalMemory /value';
            @exec($cmd, $outputFree);
            foreach ($outputFree as $line) {
                if (preg_match('/^FreePhysicalMemory=(\d+)/', $line, $matches)) {
                    $memoryFree = (int) $matches[1] * 1024; // Convert KB to Bytes
                }
            }
            $memoryUsed = $memoryTotal - $memoryFree;
        } else {
            // Linux / Shared Hosting
            // Try to read /proc/meminfo
            if (@is_readable('/proc/meminfo')) {
                $stats = @file_get_contents('/proc/meminfo');
                if ($stats) {
                    // Extract MemTotal and MemAvailable
                    preg_match('/MemTotal:\s+(\d+)\s+kB/', $stats, $matchesTotal);
                    preg_match('/MemAvailable:\s+(\d+)\s+kB/', $stats, $matchesFree);
                    
                    if (isset($matchesTotal[1])) $memoryTotal = $matchesTotal[1] * 1024;
                    if (isset($matchesFree[1])) $memoryFree = $matchesFree[1] * 1024;
                    
                    $memoryUsed = $memoryTotal - $memoryFree;
                }
            }
            
            // Fallback: Use PHP memory limit and usage if /proc/meminfo fails
            if ($memoryTotal <= 0) {
                $memoryUsed = memory_get_usage(true);
                $memoryLimit = ini_get('memory_limit');
                if (preg_match('/^(\d+)(.)$/', $memoryLimit, $matches)) {
                    if ($matches[2] == 'M') {
                        $memoryTotal = $matches[1] * 1024 * 1024; // MB to Bytes
                    } else if ($matches[2] == 'K') {
                        $memoryTotal = $matches[1] * 1024; // KB to Bytes
                    } else if ($matches[2] == 'G') {
                        $memoryTotal = $matches[1] * 1024 * 1024 * 1024; // GB to Bytes
                    }
                } else {
                     $memoryTotal = 128 * 1024 * 1024; // Default 128MB fallback
                }
                $memoryFree = $memoryTotal - $memoryUsed;
                $isSimulated = true;
            }
        }

        return [
            'used' => $this->formatBytes($memoryUsed),
            'free' => $this->formatBytes($memoryFree),
            'total' => $this->formatBytes($memoryTotal),
            'raw' => [
                'used' => $memoryUsed,
                'free' => $memoryFree,
                'total' => $memoryTotal,
                'simulated' => $isSimulated
            ]
        ];
    }

    private function getDiskUsage()
    {
        $path = base_path();
        $total = disk_total_space($path);
        $free = disk_free_space($path);
        $used = $total - $free;

        return [
            'used' => $this->formatBytes($used),
            'free' => $this->formatBytes($free),
            'total' => $this->formatBytes($total),
            'usage_percentage' => $total > 0 ? round(($used / $total) * 100, 2) . '%' : '0%',
            'raw' => [
                'used' => $used,
                'total' => $total
            ]
        ];
    }

    private function getDatabaseStatus()
    {
        try {
            $pdo = DB::connection()->getPdo();
            return [
                'status' => 'connected',
                'version' => $pdo->getAttribute(\PDO::ATTR_SERVER_VERSION),
                'driver' => DB::connection()->getDriverName()
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'disconnected', 
                'error' => $e->getMessage()
            ];
        }
    }

    private function formatBytes($bytes, $precision = 2)
    {
        if ($bytes <= 0) return '0 B';
        
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
