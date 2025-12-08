<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Artisan;

class SystemHealthRepository
{
    public function dbStatus(): array
    {
        try {
            $start = microtime(true);
            DB::select("SELECT 1");
            $latency = (microtime(true) - $start) * 1000;

            return ['status' => 'up', 'latency_ms' => round($latency, 2)];
        } catch (\Exception $e) {
            return ['status' => 'down', 'latency_ms' => null];
        }
    }

    public function cacheStatus(): array
    {
        try {
            Cache::put('health_check', 'ok', 5);
            return ['status' => 'up'];
        } catch (\Exception $e) {
            return ['status' => 'down'];
        }
    }

    public function queueStatus(): array
    {
        try {
            $failed = DB::table('failed_jobs')->count();
            return [
                'status' => 'running',
                'failed_jobs' => $failed
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'down',
                'failed_jobs' => null
            ];
        }
    }

    public function systemUsage(): array
    {
        return [
            'memory_mb' => round(memory_get_usage(true) / 1024 / 1024, 2),
            'disk_free_mb' => round(disk_free_space('/') / 1024 / 1024, 2),
            'disk_total_mb' => round(disk_total_space('/') / 1024 / 1024, 2),
        ];
    }

    public function requestStats(): array
    {
        $minuteKey = 'stats.requests.'.now()->format('YmdHi');

        return [
            'per_minute' => Cache::get($minuteKey, 0),
            'avg_exec_ms' => round(Cache::get('stats.exec_time', 0), 2)
        ];
    }
}
