<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Cache;

class RequestStatsMiddleware
{
    public function handle($request, Closure $next)
    {
        // $start = microtime(true);

        // $response = $next($request);

        // $duration = microtime(true) - $start;

        // // Count requests per minute
        // $minuteKey = 'stats.requests.'.now()->format('YmdHi');
        // Cache::increment($minuteKey, 1);
        // Cache::put($minuteKey, Cache::get($minuteKey), 70);

        // // Average execution time
        // $avgKey = 'stats.exec_time';
        // $countKey = 'stats.exec_count';

        // Cache::increment($countKey, 1);
        // $total = (Cache::get($avgKey, 0) * (Cache::get($countKey) - 1)) + ($duration * 1000);
        // Cache::put($avgKey, $total / Cache::get($countKey), 70);

        // return $response;


        $start = microtime(true);

        $response = $next($request);

        $duration = microtime(true) - $start;

        // Count requests per minute
        $minuteKey = 'stats.requests.' . now()->format('YmdHi');
        if (!Cache::has($minuteKey)) {
            Cache::put($minuteKey, 0, 70);
        }
        Cache::increment($minuteKey);
        
        // Average execution time
        $avgKey   = 'stats.exec_time';
        $countKey = 'stats.exec_count';

        if (!Cache::has($countKey)) {
            Cache::put($countKey, 0, 70);
        }

        Cache::increment($countKey);
        $count = Cache::get($countKey, 0);

        if ($count > 0) {
            $prevAvg = Cache::get($avgKey, 0);
            $total   = ($prevAvg * ($count - 1)) + ($duration * 1000);
            $newAvg  = $total / $count;

            Cache::put($avgKey, $newAvg, 70);
        } else {
            Cache::put($avgKey, $duration * 1000, 70);
            Cache::put($countKey, 1, 70);
        }

        return $response;

    }
}
