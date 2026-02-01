<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use phpseclib3\Net\SSH2;

class SystemHealthController extends Controller
{
    public function index(Request $request)
    {
        if ($request->query('key') !== env('SYSTEM_MONITOR_KEY')) {
            abort(403, 'Unauthorized');
        }

        // Check if Remote SSH is configured
        if (config('app.env') !== 'testing' && env('SSH_HOST')) {
            return $this->getRemoteStats();
        }

        // CPU Load (Compatible with Shared Hosting / Linux)
        $cpuLoad = $this->getServerLoad();

        // Memory Usage
        $memoryUsage = $this->getMemoryUsage();

        // Disk Usage
        $diskUsage = $this->getDiskUsage();

        // Database Connection, Version & Size
        $dbStatus = $this->getDatabaseStatus();

        // Log File Size
        $logInfo = $this->getLogInfo();

        return response()->json([
            'status' => 'ok',
            'timestamp' => now()->toIso8601String(),
            'server' => [
                'php_version' => PHP_VERSION,
                'software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
                'laravel_version' => app()->version(),
                'ip' => $_SERVER['SERVER_ADDR'] ?? '127.0.0.1',
                'path' => base_path(),
            ],
            'system' => [
                'cpu_load' => $cpuLoad,
                'memory' => $memoryUsage,
                'disk' => $diskUsage,
            ],
            'database' => $dbStatus,
            'log' => $logInfo,
        ]);
    }

    private function getLogInfo()
    {
        $logPath = storage_path('logs/laravel.log');
        $size = 0;
        
        if (file_exists($logPath)) {
            $size = filesize($logPath);
        }

        return [
            'exists' => file_exists($logPath),
            'size' => $this->formatBytes($size),
            'raw_size' => $size,
            'path' => $logPath
        ];
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
            $load = isset($sys_load[0]) ? $sys_load[0] : 0; 
            
            if ($load > 100) $load = 100;
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
            if (@is_readable('/proc/meminfo')) {
                $stats = @file_get_contents('/proc/meminfo');
                if ($stats) {
                    preg_match('/MemTotal:\s+(\d+)\s+kB/', $stats, $matchesTotal);
                    preg_match('/MemAvailable:\s+(\d+)\s+kB/', $stats, $matchesFree);
                    
                    if (isset($matchesTotal[1])) $memoryTotal = $matchesTotal[1] * 1024;
                    if (isset($matchesFree[1])) $memoryFree = $matchesFree[1] * 1024;
                    
                    $memoryUsed = $memoryTotal - $memoryFree;
                }
            }
            
            if ($memoryTotal <= 0) {
                $memoryUsed = memory_get_usage(true);
                $memoryLimit = ini_get('memory_limit');
                if (preg_match('/^(\d+)(.)$/', $memoryLimit, $matches)) {
                    if ($matches[2] == 'M') {
                        $memoryTotal = $matches[1] * 1024 * 1024;
                    } else if ($matches[2] == 'K') {
                        $memoryTotal = $matches[1] * 1024;
                    } else if ($matches[2] == 'G') {
                        $memoryTotal = $matches[1] * 1024 * 1024 * 1024;
                    }
                } else {
                     $memoryTotal = 128 * 1024 * 1024; 
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
                'total' => $total,
                'path' => $path
            ]
        ];
    }

    private function getDatabaseStatus()
    {
        try {
            $pdo = DB::connection()->getPdo();
            
            // Get DB Size (MySQL specific)
            $dbName = DB::connection()->getDatabaseName();
            $size = 0;
            
            try {
                $result = DB::select("SELECT table_schema 'db_name', SUM( data_length + index_length ) / 1024 / 1024 'db_size_mb' FROM information_schema.TABLES WHERE table_schema = ? GROUP BY table_schema", [$dbName]);
                if (!empty($result)) {
                    $size = round($result[0]->db_size_mb, 2); 
                }
            } catch (\Exception $ex) {
                // Ignore if permission denied
            }

            return [
                'status' => 'connected',
                'version' => $pdo->getAttribute(\PDO::ATTR_SERVER_VERSION),
                'driver' => DB::connection()->getDriverName(),
                'size_mb' => $size
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

    private function getRemoteStats()
    {
        try {
            // Set timeout in constructor (3rd arg) to 5 seconds
            // This prevents the "infinite spin" if the server is unreachable
            $ssh = new SSH2(env('SSH_HOST'), env('SSH_PORT', 22), 5);
            
            if (!$ssh->login(env('SSH_USERNAME'), env('SSH_PASSWORD'))) {
                throw new \Exception('SSH Login Failed');
            }

            // Memory (free -m)
            $memOutput = $ssh->exec('free -m');
            $memLines = explode("\n", trim($memOutput));
            $memData = preg_split('/\s+/', $memLines[1]);
            
            $memTotal = (int)$memData[1] * 1024 * 1024; 
            $memUsed = (int)$memData[2] * 1024 * 1024;
            $memFree = (int)$memData[3] * 1024 * 1024;

            // CPU Load (uptime)
            $loadOutput = $ssh->exec('uptime');
            preg_match('/load average: ([0-9.]+)/', $loadOutput, $matches);
            $rawLoad = isset($matches[1]) ? (float)$matches[1] : 0;
            
            $cores = (int) env('SYSTEM_MONITOR_CPU_CORES', 1);
            if ($cores < 1) $cores = 1;
            $cpuLoad = min(100, round(($rawLoad / $cores) * 100, 1));

            // Disk Usage (df -h /home/user)
            $path = env('SYSTEM_MONITOR_DISK_PATH', '/');
            $diskOutput = $ssh->exec("df -h $path | tail -n 1");
            $diskData = preg_split('/\s+/', trim($diskOutput));
            
            $diskTotalStr = $diskData[1] ?? '0';
            $diskUsedStr = $diskData[2] ?? '0';
            $diskPercent = $diskData[4] ?? '0%';

            // DB Status
            $dbStatus = $this->getDatabaseStatus();

            return response()->json([
                'status' => 'ok',
                'timestamp' => now()->toIso8601String(),
                'server' => [
                    'php_version' => trim($ssh->exec('php -v | head -n 1 | cut -d " " -f 2')),
                    'software' => 'SSH Remote',
                    'laravel_version' => app()->version(),
                    'ip' => env('SSH_HOST'),
                    'path' => $path,
                ],
                'system' => [
                    'cpu_load' => $cpuLoad,
                    'memory' => [
                        'used' => $this->formatBytes($memUsed),
                        'free' => $this->formatBytes($memFree),
                        'total' => $this->formatBytes($memTotal),
                        'raw' => [
                            'used' => $memUsed,
                            'total' => $memTotal,
                            'simulated' => false,
                            'source' => 'ssh'
                        ]
                    ],
                    'disk' => [
                        'used' => $diskUsedStr,
                        'free' => 'N/A',
                        'total' => $diskTotalStr,
                        'usage_percentage' => $diskPercent,
                         'raw' => [
                            'path' => $path
                        ]
                    ],
                ],
                'database' => $dbStatus,
                'log' => $this->getLogInfo(),
            ]);

        } catch (\Exception $e) {
            // Fallback to local stats but mark as error
            $cpuLoad = $this->getServerLoad();
            $memoryUsage = $this->getMemoryUsage();
            $diskUsage = $this->getDiskUsage();
            $dbStatus = $this->getDatabaseStatus();
            $logInfo = $this->getLogInfo();

            return response()->json([
                'status' => 'ok', // Status OK so UI renders
                'error' => 'SSH Fallback: ' . $e->getMessage(), 
                'timestamp' => now()->toIso8601String(),
                'server' => [
                    'php_version' => PHP_VERSION,
                    'software' => 'Local Fallback',
                    'laravel_version' => app()->version(),
                    'ip' => '127.0.0.1',
                    'path' => base_path(),
                ],
                'system' => [
                    'cpu_load' => $cpuLoad,
                    'memory' => $memoryUsage,
                    'disk' => $diskUsage,
                ],
                'database' => $dbStatus,
                'log' => $logInfo,
            ]);
        }
    }
}
