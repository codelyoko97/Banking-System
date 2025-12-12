<?php

namespace Tests\Feature\Repositories;

use App\Repositories\SystemHealthRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SystemHealthRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private SystemHealthRepository $repo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repo = new SystemHealthRepository();
    }

    /** @test */
    public function it_returns_db_status_up()
    {
        $result = $this->repo->dbStatus();

        $this->assertEquals('up', $result['status']);
        $this->assertNotNull($result['latency_ms']);
    }

    /** @test */
    public function it_returns_cache_status_up()
    {
        $result = $this->repo->cacheStatus();

        $this->assertEquals('up', $result['status']);
    }

    /** @test */
    public function it_returns_queue_status_running()
    {
        // لا يوجد failed_jobs
        $result = $this->repo->queueStatus();

        $this->assertEquals('running', $result['status']);
        $this->assertEquals(0, $result['failed_jobs']);
    }

    /** @test */
    public function it_returns_system_usage()
    {
        $result = $this->repo->systemUsage();

        $this->assertArrayHasKey('memory_mb', $result);
        $this->assertArrayHasKey('disk_free_mb', $result);
        $this->assertArrayHasKey('disk_total_mb', $result);
        $this->assertTrue($result['memory_mb'] > 0);
    }

    /** @test */
    public function it_returns_request_stats_defaults()
    {
        Cache::flush(); // تأكد أن الكاش فاضي

        $result = $this->repo->requestStats();

        $this->assertEquals(0, $result['per_minute']);
        $this->assertEquals(0, $result['avg_exec_ms']);
    }

    /** @test */
    public function it_returns_request_stats_with_values()
    {
        $minuteKey = 'stats.requests.' . now()->format('YmdHi');
        Cache::put($minuteKey, 5, 5);
        Cache::put('stats.exec_time', 123.456, 5);

        $result = $this->repo->requestStats();

        $this->assertEquals(5, $result['per_minute']);
        $this->assertEquals(123.46, $result['avg_exec_ms']); // rounded
    }
}